<?php
require_once "config.php";

$data = $database->query("SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track GROUP BY `room`) ORDER BY `track`.`count` DESC")->fetchAll();
echo "<pre>";
echo "Room - Total Count - Last Time Seen" . PHP_EOL;
foreach($data as $row){
echo $row["room"] . " - " . $row["count"] . " - " . date(DATE_RFC2822, $row["timestamp"]) . PHP_EOL;
}
echo "<pre>";
