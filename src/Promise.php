<?php
use PDOConnection\DB as DB;

class Promise
{
	private static $template_buffer = null;
	
	protected static $nav_connetion_list = null;
	
	protected static $connection_list = null;
	
	protected static $connection_data = array();
	
	protected static $connection_log = array();
	
	
	//get config file
	protected static function getConfig($configName = 'config.json')
	{
		$json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $configName);
		if ( ! $json) {
			return false;
		}
		else {
			return $json;
			
		}
		
	}
	
	protected static function setConfig($constent, $append = true, $configName = 'config.json')
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . $configName;
		if ($append) {
			file_put_contents($file, $constent, FILE_APPEND | LOCK_EX);
		}
		else {
			file_put_contents($file, $constent);
		}
		
	}
	
	protected static function testDB($post)
	{
		$host = $post['host'];
		$password = (isset($post['pass'])) ? $post['pass'] : '';
		$username = (isset($post['user'])) ? $post['user'] : '';
		$dsn = 'mysql:host=' . $host;
		
		try {
			
			$pdo = new PDO($dsn, $username, $password, array (
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			));
			
		} catch (PDOException $e) {
			return array(
				'status'  => false,
				'message' => $e->getMessage()
			);
		}
		
		return array(
			'status' => true,
			'data'	 => $pdo
		);
	}
	
	protected static function get($name, $all = false)
	{
		if (is_array($name)) {
			foreach ($name as $gets) {
				if ( ! in_array($gets, $_GET)) {
					return false;	
				}
			}
		}
		else {
			if (isset($_GET[$name])) {
				if ($all) {
					return $_GET;	
				}
				return $_GET[$name];
			}
			return false;
		}
	}
	
	protected static function getTemplate($name)
	{
		if ( ! self::$template_buffer) {
			self::$template_buffer = file_get_contents(__DIR__ . '/Templates/engine.tpl');	
		}
		preg_match('/'. $name .': \{\{(.*?)\}\}/s', self::$template_buffer, $matches);
		return $matches[1];
		
	}
	
	protected static function templateForg($keys, $content)
	{
		foreach ($keys as $name => $val) {
			$content = str_replace('[[' . $name . ']]', $val, $content);
		}
		return $content;
	}
	
	protected static function initConnection()
	{
		$template = self::getTemplate('db_list');
		$nav 	  = self::getTemplate('nav_list');
		
		if ($tables = self::getConfig('config.json')) {
			
			$tables = json_decode($tables, true);
			foreach (array_keys($tables) as $val) {
				
				
				$connection = self::testDB($tables[$val]);
				if ( $connection['status'] == false) {
					
					self::$nav_connetion_list .= self::templateForg(array(
						'link'  => '?add=true&c=' . $val,
						'name'	=> $val
					), $nav);
					self::$connection_log[$val] = $connection;
					self::$connection_list .= self::templateForg(array(
						'link'  => '?add=true&c=' . $val,
						'image' => 'danger',
						'name'	=> $val
					), $template);
				}
				else {
					
					self::$nav_connetion_list .= self::templateForg(array(
						'link'  => '?c=' . $val,
						'name'	=> $val
					), $nav);
					self::$connection_log[$val] = $connection;
					self::$connection_list .= self::templateForg(array(
						'link'  => '?c=' . $val,
						'image' => 'success',
						'name'	=> $val
					), $template);
				}
			}
			
		}
		
		self::$connection_list .= self::templateForg(array(
			'link'  => '?add=true',
			'image' => 'default',
			'name'	=> 'Add New Connection'
		), $template);
		
	}
	
	protected static function addForm($editting = null)
	{
		$form_template  = self::getTemplate('add');
		$alert_template = self::getTemplate('alert');
		
		$label = 'Add Database';
		$alert = null;
		$host  = 'localhost';
		$user  = null;
		$pass  = null;
		$name  = null;
		$del_btn = null;
		
		if ($editting) {
			
			$config = self::getConfig();
			$json = json_decode($config, true);
			if (isset($json[$editting])) {
				
				if (isset($_POST['delete'])) {
					
					unset($json[$editting]);
					$new_json = json_encode($json);
					self::setConfig($new_json, false);
					
					$alert = array('type' => 'success', 'message' =>'(' . $editting . ') Deleted');	
					
				}
				else {
		
					$label = $editting;
					$connection = self::$connection_log[$editting];
					
					if ($connection['status'] == false) {
						$alert = array('type' => 'error', 'message' => $connection['message']);	
					}
					
					$del_btn = self::templateForg(array('name' => $editting), self::getTemplate('delete_button'));
					
					list($host, $use, $pass) = array_values($json[$editting]);
					$name = $editting;
				}
				
			}
			else {
				$alert = array('type' => 'error', 'message' => 'Connection (' . $editting . ') does not exists');	
			}
			$alert = self::templateForg($alert, $alert_template);

		}
		else {
			
			if (isset($_POST['save']) || isset($_POST['test']) || isset($_POST['delete'])) {
			
				if (trim($_POST['name']) == false) {
					
					$alert = self::templateForg(array(
						'type'	  => 'error',
						'message' => 'Set connection Name (Save As)',
					), $alert_template);
	
				}
				else {
					
					list($name, $host, $user, $pass, ) = array_values($_POST);
					
					if (isset($_POST['test'])) {
						
						array_pop($_POST);
						$connection = self::testDB($_POST);
						if ( $connection['status'] == false) {
							$alert = array('type' => 'error', 'message' => $connection['message']);
						}
						else {
							$alert = array('type' => 'success', 'message' => 'Connected');
						}
						$alert = self::templateForg($alert, $alert_template);
					}
					elseif (isset($_POST['save'])) {
	
						$config = self::getConfig();
						$json = json_decode($config, true);
						array_pop($_POST);
						array_shift($_POST);
						
						if ( $json) {
							$new_json = $json;
							
							if (array_key_exists($name, $new_json)) {
								$alert = array('type' => 'error', 'message' => 'Connection (' . $name . ') already exists');
							}
						}
						
						$new_json[$name] = $_POST;
			
						$new_json = json_encode($new_json);
						self::setConfig($new_json, false);
						
						if ( ! $alert) {
							$alert = array('type' => 'success', 'message' => 'Connection Saved!');
						}
						$alert = self::templateForg($alert, $alert_template);
						
					}
					
				}
				
			}
		}
		
		return self::templateForg(array(
			'label' => $label,
			'name'	=> $name,
			'alert' => $alert,
			'host'	=> $host,
			'user'	=> $user,
			'pass'	=> $pass,
			'delete_button' => $del_btn
		), $form_template);
	}
	
	//list all tables
	public static function makeGrid($name, $link, $label, $index = false)
	{
		$db = null;
		$template = self::getTemplate('db_list');
		foreach ($name as $val) {
			
			if ( $index) {
				$val = $val[0];
			}
			$db .= self::templateForg(array(
				'link'  => $link . $val,
				'image' => 'success',
				'name'	=> $val
			), $template);
		}
		
		$db .= self::templateForg(array(
			'link'  => '?add=' . $link,
			'image' => 'default',
			'name'	=> 'Add New ' . $label
		), $template);
		
		return '<ul>' . $db . '</ul>';
	}
	
	public static function makeTable($tables)
	{
		$columns = null;
		$tblhead = null;
		$new_tbl = null;
		$template = self::getTemplate('db_list');
		
		foreach ($tables as $val) {
			
			$columns .= '<tr><a href="">';
			
			array_map(function ($key, $values) use (&$columns, &$tblhead) {
				
				if ($tblhead !== false) {
					$tblhead .= '<th scope="col" class="' . $key .'">' . $key . '</th>';
				}
				$columns .= '<td class="' . $key .'">' . substr($values, 0, 4) . '</td>';
				
			}, array_keys($val), $val);
			
			if ($tblhead !== false) {
				$new_tbl = $tblhead;
				$tblhead = false;
			}
			$columns .= '</a></tr>';
			
		}
		return self::templateForg(array(
			'table_head'  	=> $new_tbl,
			'contents' 		=> $columns
		), self::getTemplate('table_list'));
		
	}
	
	protected static function listDatabases($DBConn)
	{
		DB::$conn = self::$connection_log[$DBConn]['data'];
		$query = new Sql\Query('SHOW DATABASES');
		$query = $query->commit('stm')->fetchAll(PDO::FETCH_NUM);
		
		$link = '?c=' . $DBConn . '&d=';
		return self::makeGrid($query, $link, 'Database', true);
	}
	
	protected static function listTables($DBConn, $database)
	{
		DB::$conn = self::$connection_log[$DBConn]['data'];
		DB::useDatabase($database);
		$query = new Sql\Query('SHOW TABLES');
		$query = $query->commit('stm')->fetchAll(PDO::FETCH_NUM);
		
		$link = '?c=' . $DBConn . '&d=' . $database . '&t=';
		return self::makeGrid($query, $link, 'Table', true);
	}
	
	protected static function tableData($DBConn, $database, $table)
	{
		DB::$conn = self::$connection_log[$DBConn]['data'];
		DB::useDatabase($database);
		
		$query = new Sql\Select();
		$query = $query->from($table)
					   ->commit();
		return self::makeTable($query);
		
	}
}