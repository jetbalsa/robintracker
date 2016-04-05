<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];

$data = $database->query("SELECT *, COUNT(*) AS 'beacons', MAX(`time`) as 'time' FROM (SELECT * FROM `track` WHERE `count`>50 AND `time`>(UNIX_TIMESTAMP()-120) AND `guid`!='' ORDER BY `id` DESC) as T GROUP BY `guid` ORDER BY `count` DESC LIMIT 10")->fetchAll();

// Someone with better SQL knowlege might be able to do all this in one query, but since
//   we're relying on a derived table above right now we'll just make this two queries
function getGuid($r) { return $r['guid']; }
$guids = array_map(getGuid, $data);
$rooms = $database->select('rooms',['guid','room','tier','parent','child0','child1'],['OR'=>['guid'=>$guids,'parent'=>$guids]]);
$guids = array_map(getGuid, $rooms);
$rooms = array_combine($guids,$rooms);
?>
<html>
<head>
<title>RobinTracker</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<?php
$r = intval(@$_GET['r']);
if($r==0)
{
	$r = 60;
}
?>
<meta http-equiv="refresh" content="<?=$r?>">
</head>
<body style="margin:16px;">
<h1>Robin Tracker</h1>
<span class='text-danger'>Tier data updates every 2 minutes and is probably wrong. You've been warned!</span>
<table class='table table-striped'>
<thead><tr>
<td><b>Room</b></td>
<td><b>Tier</b></td>
<td><b>Constituent Rooms</b></td>
<td><b>Total</b></td>
<td><b>Grow</b></td>
<td><b>Stay</b></td>
<td><b>Abandon</b></td>
<td><b>Abstain</b></td>
<td>
<b>Founded</b>
<?php
$queryString = "?" . (($r==60)?"":"r=".$r."&") . "ft=";
$label = "";
if(@$_GET['ft']=='absolute')
{
	$label = 'relative';
}
else
{
	$label = 'absolute';
}
$queryString .= $label;
?>
( <a href='<?=$queryString?>'><?=$label?></a> )
</td>
<td><b>Reaping</b></td>
<td><b>Updated</b></td>
</tr></thead>

<?php
function prettyDeltaTime($reference)
{
	$reference = intval($reference);
	$time = time();
	$dt = abs($reference - $time);

	$minutes = floor($dt/60);
	$seconds = $dt - ($minutes * 60);

	$hours = floor($minutes/60);
	$minutes = $minutes - ($hours*60);

	return (empty($hours)?"":($hours . "h")) . $minutes . "m" . $seconds . "s " . (($reference > $time)?"from now":"ago");
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

$class = [];
// Only report rooms with over 100 people if we have 5+ beacons
if($row['count'] >= 100 && $row['beacons']<5)
{
	continue;
}

$totalUsers += $row['count'];
$totalGrow += $row['grow'];
$totalStay += $row['stay'];
$totalAbandon += $row['abandon'];
$totalAbstain += $row['novote'];

// Retrieve Tier and Room information
$tier = '?';
$child0 = '??';
$child1 = '??';
if(!empty(@$rooms[$row['guid']]))
{
	$room = $rooms[$row['guid']];
	$tier = $room['tier'];
	$child0 = $rooms[$room['child0']];
	$child1 = $rooms[$room['child1']];
	$child0 = empty($child0)?"??":$child0['room'];
	$child1 = empty($child1)?"??":$child1['room'];
}

// Spruce up 
// For 100+ rooms, we get enough beacons that >30 seconds may have merged
if($time > $row['reap'] && $row['count'] >= 100 && $dt > 30)
{
	array_push($class,"warning");
}
// If we haven't gotten a beacon in 60 seconds it almost certainly merged
if($time > $row['reap'] && $dt>60)
{
	array_push($class,"danger");
}
// Channels fromed in the last 2 minutes
if(abs($time-$row['formation'])<120)
{
	array_push($class,"success");
}
?>
<tr class="<?=implode(' ',$class)?>">
<!--<?=htmlspecialchars($row['guid'])?>-->
<td><b><?=htmlspecialchars($row['room'])?></b></td>
<td><?=$tier?></td>
<td><?=htmlspecialchars($child0)?>, <?=htmlspecialchars($child1)?></td>
<td><?=$row['count']?></td>
<td><?=$row['grow']?></td>
<td><?=$row['stay']?></td>
<td><?=$row['abandon']?></td>
<td><?=$row['novote']?></td>
<?if(@$_GET['ft']=='absolute'):?>
<?date_default_timezone_set("America/New_York")?>
<td><?=date("Y-m-d H:i T",$row['formation']);?></td>
<?else:?>
<td><?=prettyDeltaTime($row['formation']);?></td>
<?endif;?>
<td><?=prettyDeltaTime($row['reap']);?></td>
<td><?=prettyDeltaTime($row['time']);?></td>
</tr>
<?endforeach;?>
<tr>
<td></td>
<td></td>
<td style="text-align: right"><b>Table Sum</b></td>
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
Contribute data using the <a href='https://raw.githubusercontent.com/jhon/robintracker/master/robintracker.user.js'>Standalone Userscript</a> or by enabling contribution in a compatible script like <a href='https://github.com/vartan/robin-grow'>Robin-Grow</a> or <a href='https://github.com/keythkatz/Robin-Autovoter'>Robin-Autovoter</a>.<br />
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
