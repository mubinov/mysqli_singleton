<?php
error_reporting(E_ALL);

define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'test');

include_once 'DB.php';

$db = DB::getInstance();
$db->loadEngine();

/*
	Действия которые можно выполнять в любом участке кода
*/
	
$db = DB::getInstance();
$db->query('SELECT version();');
var_dump($db->num_rows());
var_dump($db->fetch_row());

$db->query('SELECT version();');
var_dump($db->fetch_array());


var_dump($db->escape_string("Robert';DROP TABLE `STUDENTS`;"));

var_dump($db::getCountQueries());
