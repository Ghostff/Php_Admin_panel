<?php

use Sql\Select as Select;
use PDOConnection\DB as DB;

require 'CRUD/src/DB.php';
require 'Promise.php';


class Admin extends Promise
{
	public static $dbs = null;
	
	public static $manage = array();
	
		//get post variables
	public static function post($name)
	{
		if (isset($_POST[$name])) {
			return $_POST[$name];	
		}
	}
	
	
	public static function get($name)
	{
		if (isset($_GET[$name])) {
			return $_GET[$name];	
		}
		return false;
	}
	
	
	//get test error messages
	public static function testStatus($connected = null)
	{
		$_POST['host'] = ( ! empty($_POST['host'])) ? $_POST['host'] : 'localhost';
		if (isset($_POST['test'])) {
			
			if ( ! $connected) {
				$connected = static::testDB($_POST);
			}
				
			if ($connected === true) {
				return '<div class="success-message"><p>Connection was successfull</p></div>';
			}
			else {
				return '<div class="error-message"><p>' . $connected . '</p></div>';
			}
		}
		elseif (isset($_POST['save'])) {
			
			if ( ! $_POST['name']) {
				return '<div class="error-message"><p>Set connection Name (Save As)</p></div>';	
			}
			
			$name = $_POST['name'];
			$config = self::getConfig();
			$json = json_decode($config, true);
			array_pop($_POST);
			array_shift($_POST);
			
			if ( $json) {
				$new_json = $json;
				
				if (array_key_exists($name, $new_json)) {
					return '<div class="error-message"><p>Connection name already exists</p></div>';
				}
			}
			
			$new_json[$name] = $_POST;

			$new_json = json_encode($new_json);

			self::setConfig($new_json, false);
		}
	}
	
	
	//list all tables
	public static function listDBTBL($name, $database)
	{
		$db = null;
		foreach ($name as $val) {
			
			$val = $val[0];
			$db .= '<li class="mix color-1 check1 radio2 option3"><a href="?db=' . $database . $val .'">
						<div class="img-hoder"><img src="assets/img/success.jpg" alt="Image 1"></div>
						<div class="name">' . $val .'</div> </a>
					</li>';
		}
		
		$db .= '<li class="mix color-1 check1 radio2 option3"><a href="?add=' . $database .'">
						<div class="img-hoder"><img src="assets/img/default.jpg" alt="Image 1"></div>
						<div class="name">Add new</div> </a>
					</li>';
		return $db;
	}
	
	//list all tables in DB
	public static function listDB()
	{
		if ($tables = self::getConfig('config.json')) {
			
			$dbs = null;
			$tables = json_decode($tables, true);
			
			foreach (array_keys($tables) as $val) {
				
				
				$dbs .= '<li class="filter"><a href="?db=' . $val .'" data-type="color-1">' . $val .'</a></li>';
				
				$conn = self::testDB($tables[$val]);
				if ( $conn['status'] === true) {
					
					self::$manage[$val] = array (
						'status'	=> true,
						'post'		=> $tables[$val],
						'data'		=> $conn['data']
					);
					self::$dbs .= '<li class="mix color-1 check1 radio2 option3"><a href="?db=' . $val .'">
						<div class="img-hoder"><img src="assets/img/success.jpg" alt="Image 1"></div>
						<div class="name">' . $val .'</div> </a>
					</li>';
				}
				else {
					
					self::$manage[$val] = array (
						'status'	=> false,
						'post'		=> $tables[$val],
						'data'		=> $conn['data']
					);
					self::$dbs .= '<li class="mix color-1 check1 radio2 option3"><a href="?db=' . $val .'">
						<div class="img-hoder"><img src="assets/img/danger.jpg" alt="Image 1"></div>
						<div class="name">' . $val .'</div> </a>
					</li>';
				}
			}
			return $dbs;
		}
		
	}
	
	//list tables
	public static function listTables()
	{
		if (self::get('add')) {
			$label = 'New Database';
			$message = null;
			require 'incs/add.php';
		}
		elseif ($get = self::get('db')) {
			
			
			
			if (self::$manage[$get]['status'] == false) {
				
				$label = $get;
				$_POST = self::$manage[$get]['post'];
				$_POST['test'] = true;
				
				$message = self::$manage[$get]['data'];
				
				require 'incs/add.php';
				return;
			}
			else {

				if ($new_get = self::get('d')) {
					
					DB::init(
						self::$manage[$get]['post']['host'],
						$new_get,
						self::$manage[$get]['post']['user'],
						self::$manage[$get]['post']['pass']
					);
					
					$query = DB::$conn->query('SHOW TABLES');
					$query = $query->fetchAll(PDO::FETCH_NUM);
					return '<ul>' . self::listDBTBL($query, $get . '&d=' . $new_get. '&t=') . '</ul>';
					
				}
				else {
					$query = self::$manage[$get]['data']->query('SHOW DATABASES');
					$query = $query->fetchAll(PDO::FETCH_NUM);
					return '<ul>' . self::listDBTBL($query, $get . '&d=') . '</ul>';
				}
				
			}
			
			
		}
		return '<ul>' . self::$dbs . '</ul>';
	}
}