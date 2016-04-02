<?php
header('Access-Control-Allow-Origin: *');  
echo "OK";
require_once "config.php";

if(empty(@$_GET['id'])){ die(); }

// If the user is banned, don't let them submit data
if(in_array(@$_SERVER['REMOTE_ADDR'],$banlist)){ die(); }

// Basic data validation
if (intval(@$_GET['count']) != (intval(@$_GET['gr'])+intval(@$_GET['st'])+intval(@$_GET['ab'])+intval(@$_GET['nv']))) { die(); }

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
