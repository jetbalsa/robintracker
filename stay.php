<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];

$data = $database->query("SELECT `A`.`users` as 'users', `track_storage`.* FROM (SELECT MAX(`id`) AS 'id', COUNT(DISTINCT `ip`) AS 'users' FROM `track_storage` WHERE `stay`>50 AND `guid`!='' GROUP BY `guid`) AS A, `track_storage` WHERE `track_storage`.id = A.`id` AND `track_storage`.`time`<(UNIX_TIMESTAMP()-300) AND `A`.users > 3 ORDER BY `track_storage`.`stay` DESC")->fetchAll();
?>
<html>
<head>
<title>RobinTracker</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<meta http-equiv="refresh" content="60;">
</head>
<body>
<h1>Robin Tracker</h1>
These are rooms that ended with over 50 people voting stay, that we haven't received an update for within the last 5 minutes, and that we were getting updates from at least 3 sources. This is based on data that only started getting collected late Saturday PDT.
<table class='table table-striped'>
<thead><tr>
<td><b>Room</b></td>
<td><b>Total</b></td>
<td><b>Grow</b></td>
<td><b>Stay</b></td>
<td><b>Abandon</b></td>
<td><b>Abstains</b></td>
<td><b>Founded</b></td>
<td><b>Reaped</b></td>
<td><b>Last Seen</b></td>
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
<!--<?=$row['guid']?>-->
<td><?=$row['room']?></td>
<td><?=$row['count']?></td>
<td><?=$row['grow']?></td>
<td><?=$row['stay']?></td>
<td><?=$row['abandon']?></td>
<td><?=$row['novote']?></td>
<td><?=date("m-d-Y H:i T",$row['formation']);?></td>
<td><?=date("m-d-Y H:i T",$row['reap']);?></td>
<td><?=date("m-d-Y H:i T",$row['time']);?></td>
</td>
<?endforeach;?>
</tbody>
</table>
<a href='https://github.com/jhon/robintracker'>Fork me on GitHub</a> | 
<a href='https://github.com/keythkatz/Robin-Autovoter'>Robin Autovote Script</a><br />
<?php
$end_time = explode(' ',microtime());
$total_time = ($end_time[0] + $end_time[1]) - $start_time;
printf("Generated in %.3fs",$total_time);
?>
<?=@$footer?>
</body>
 </html>
