<?php

class Admin extends Promise
{    
    public static function listConnectionNav()
    {
        return self::$nav_connetion_list;
    }
    
    private static function listConnection()
    {
        return '<ul>' . self::$connection_list . '</ul>';
    }
    
    public static function route()
    {
        return self::initConnection();
    }
    
    public static function buffer()
    {
        if ($get = self::get('add', true)) {
            if ($get['add'] == 'true') {
                
                if (isset($get['c'])) {
                    return self::addForm($get['c']);
                }
                return self::addForm();
            }
        }
        elseif ($get = self::get('c', true)) {
            if (isset($get['d'])) {
                
                if (isset($get['t'])) {
                    return self::tableData($get['c'], $get['d'], $get['t']);
                }
                else {
                    return self::listTables($get['c'], $get['d']);
                }
            }
            else {
                return self::listDatabases($get['c']);
            }
        }
        else {
            return self::listConnection();
        }
    }
}