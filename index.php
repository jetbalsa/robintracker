<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];

$data = $database->query("SELECT *, COUNT(*) AS 'beacons', MAX(`time`) as 'time' FROM (SELECT * FROM `track` WHERE `count`>50 AND `time`>(UNIX_TIMESTAMP()-120) AND `guid`!='' ORDER BY `id` DESC) as T GROUP BY `guid` ORDER BY `count` DESC LIMIT 10")->fetchAll();
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
<?php
$totalUsers = 0;
$totalGrow = 0;
$totalAbandon = 0;
$totalAbstains = 0;
?>

<?foreach($data as $row):?>
<?php
// If the last update for this was actually >60s, bail
$time = time();
$dt = abs($time-$row['time']);

$totalUsers += $row['count'];
$totalGrow += $row['grow'];
$totalStay += $row['stay'];
$totalAbandon += $row['abandon'];
$totalAbstain += $row['novote'];

$class = [];
// Only report rooms with over 100 people if we have 5+ beacons
if($row['count'] >= 100 && $row['beacons']<5)
{
	continue;
}

// Spruce up 
if($row['count'] >= 100 && $dt > 30)
{
	array_push($class,"warning");
}

if($dt>60)
{
	array_push($class,"danger");
}
?>
<tr class="<?=implode(' ',$class)?>">
<td><?=$row['room']?></td>
<td><?=$row['count']?></td>
<td><?=$row['grow']?></td>
<td><?=$row['stay']?></td>
<td><?=$row['abandon']?></td>
<td><?=$row['novote']?></td>
<?if(@$_GET['ft']=='absolute'):?>
<?date_default_timezone_set("America/New_York")?>
<td><?=date("m-d-Y H:i T",$row['formation']);?></td>
<?else:?>
<td><?=prettyDeltaTime($row['formation']);?></td>
<?endif;?>
<td><?=prettyDeltaTime($row['reap']);?></td>
<td><?=prettyDeltaTime($row['time']);?></td>
</tr>
<?endforeach;?>
<tr>
<td><b>Table Sum</b></td>
<td><?=$totalUsers?></td>
<td><?=$totalGrow?></td>
<td><?=$totalStay?></td>
<td><?=$totalAbandon?></td>
<td><?=$totalAbstain?></td>
<td></td>
<td></td>
<td></td>
</tr>
</tbody>
</table>
Contribute data using the <a href='https://raw.githubusercontent.com/jhon/robintracker/master/robintracker.user.js'>Standalone Userscript</a> or by enabling cotribution in a compatible script like <a href='https://github.com/vartan/robin-grow'>Robin-Grow</a> or <a href='https://github.com/keythkatz/Robin-Autovoter'>Robin-Autovoter</a>.<br />
Found an issue or want to contribute code? <a href='https://github.com/jhon/robintracker'>Visit the GitHub</a>.<br />
Want more Robin data? Checkout the <a href='https://www.reddit.com/r/robintracking/comments/4czzo2/robin_chatter_leader_board_official/'>Official Leader Board</a> and <a href='http://robintree-apr3.s3-website-us-east-1.amazonaws.com/'>RobinTree</a>.<br />
<br />
<?php
$data = $database->query("SELECT COUNT(`id`) as `count`, COUNT(DISTINCT `guid`) as `rooms` FROM `track` WHERE `time`>(UNIX_TIMESTAMP()-60)")->fetchAll();
$ppm = $data[0]['count'];
$rooms = $data[0]['rooms'];
?>
<?=$ppm?> updates for <?=$rooms?> rooms in the last minute |
<?php
$end_time = explode(' ',microtime());
$total_time = ($end_time[0] + $end_time[1]) - $start_time;
printf("Page generation took %.3fs",$total_time);
?>

<!-- Shoutout to the Romanian (or person tunneling through Romania). <3 Without you this service wouldn't be anywhere near as good as it is today you wonderful pain in the ass -->

<?=@$footer?>
</body>
 </html>
