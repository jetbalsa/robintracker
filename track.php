<?php
header('Access-Control-Allow-Origin: *');  
echo "OK";
require("medoo.php");
if(empty($_GET['id'])){ die(); }
$last_user_id = $database->insert("track", [
	"room" => $_GET['id'],
	"abandon" => @$_GET['ab'],
	"stay" => @$_GET['st'],
	"grow" => @$_GET['gr'],
	"novote" => @$_GET['nv'],
	"count" => @$_GET['count'],
	"rt" => @$_GET['rt'],
	"ft" => @$_GET['ft'],
	"timestamp" => time(),
	"ip" => @$_SERVER['REMOTE_ADDR']
]);
