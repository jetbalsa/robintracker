<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];

$data = $database->query("SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track WHERE `count`>100 AND `time`>(UNIX_TIMESTAMP()-300) AND `time`<(UNIX_TIMESTAMP()) GROUP BY `room`) ORDER BY `track`.`count` DESC")->fetchAll();
?>
<html>
<head>
<title>RobinTracker</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<meta http-equiv="refresh" content="60;">
</head>
<body>
<h1>Robin Tracker</h1>

<table class='table table-striped'>
<thead><tr>
<td><b>Room</b></td>
<td><b>Total</b></td>
<td><b>Grow</b></td>
<td><b>Stay</b></td>
<td><b>Abandon</b></td>
<td><b>Abstains</b></td>
<td><b>Founded</b></td>
<td><b>Reaping</b></td>
<td><b>Updated</b></td>
</tr></thead>

<?php
function prettyDeltaTime($reference)
{
	$reference = intval($reference);
	$time = time();
	$dt = abs($reference - $time);

	$minutes = floor(($dt - ($hours*60))/60);
	$seconds = $dt - (($minutes + ($hours*60)) * 60);

	return $minutes . "m" . $seconds . "s " . (($reference > $time)?"from now":"ago");
}
?>

<tbody>
<?foreach($data as $row):?>
<tr>
<td><?=$row['room']?></td>
<td><?=$row['count']?></td>
<td><?=$row['grow']?></td>
<td><?=$row['stay']?></td>
<td><?=$row['abandon']?></td>
<td><?=$row['novote']?></td>
<td><?=prettyDeltaTime($row['formation']);?></td>
<td><?=prettyDeltaTime($row['reap']);?></td>
<td><?=prettyDeltaTime($row['time']);?></td>
</td>
<?endforeach;?>
</tbody>
</table>
<?php
$data = $database->query("SELECT COUNT(*) as `count` FROM `track` WHERE `time`>(UNIX_TIMESTAMP()-60)")->fetchAll();
$ppm = $data[0]['count'];
?>
<?=$ppm?> rooms updated in the last minute.
<br /><br />
<a href='https://monstrouspeace.com/robintracker/table.php'>Fork me on GitHub</a> | 
<a href='https://github.com/keythkatz/Robin-Autovoter'>Robin Autovote Script</a><br />
<br />
Shoutout to the Romanian (or person tunneling through Romania). <3<br />
<?php
$end_time = explode(' ',microtime());
$total_time = ($end_time[0] + $end_time[1]) - $start_time;
printf("Generated in %.3fs",$total_time);
?>
<?=@$footer?>
</body>
 </html>
