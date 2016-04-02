<?php
require_once "config.php";

$data = $database->query("SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track WHERE `count`>100 AND `time`>(UNIX_TIMESTAMP()-300) GROUP BY `room`) ORDER BY `track`.`count` DESC")->fetchAll();
?>
<html>
<head>
<title>RobinTracker</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
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
<br /><br />
<a href='https://monstrouspeace.com/robintracker/table.php'>Fork me on GitHub</a>.<br/>
<a href='https://github.com/keythkatz/Robin-Autovoter'>Robin Autovote Script</a>.<br />
<br />
Shoutout to the Romanian (or person tunneling through Romania). <3
</body>
</html>
