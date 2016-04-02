<?php
header('Access-Control-Allow-Origin: *');  
echo "OK";
require_once "config.php"
if(empty($_GET['id'])){ die(); }

// If the user is banned, don't let them submit data
if(in_array($_SERVER['REMOTE_ADDR'],$banned)){ die(); }

$last_user_id = $database->insert("track", [
	"room" => $_GET['id'],
	"abandon" => @$_GET['ab'],
	"stay" => @$_GET['st'],
	"grow" => @$_GET['gr'],
	"novote" => @$_GET['nv'],
	"count" => @$_GET['count'],
	"reap" => @$_GET['rt'],
	"formation" => @$_GET['ft'],
	"time" => time(),
	"ip" => @$_SERVER['REMOTE_ADDR']
]);
