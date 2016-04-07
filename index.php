<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];

// Cache this page for 4s
$ts = gmdate('D, d M Y H:i:s ',(time()&0xfffffffc)) . 'GMT';
$etag = '"'.md5($ts).'"';

$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
if($if_none_match && $if_none_match == $etag)
{
	header('HTTP/1.1 304 Not Modified');
	exit();
}

header('Last-Modified: ' . $ts);
header("ETag: $etag");

$data = $database->query("SELECT *, COUNT(*) AS 'beacons', MAX(`time`) as 'time' FROM `track` WHERE `time`>(UNIX_TIMESTAMP()-120) AND `guid`!='' GROUP BY `guid` ORDER BY `count` DESC;")->fetchAll();

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
<p>Tier data updates every 2 minutes.</p>
<p class='text-danger'>Want to see why I don't trust the tier data? Click the room to see the computed history!</p>
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
(&nbsp;<a href='<?=$queryString?>'><?=$label?></a>&nbsp;)
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
$totalBeacons = 0;
$roomCount = 0;
$tierCounts = array();
?>

<?foreach($data as $row):?>
<?php
// Calculate time deltas
$time = time();
$dt = abs($time-$row['time']);

if($dt>120)
{
	continue;
}

// Count the number of updates
$totalBeacons += $row['beacons'];
$roomCount++;

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

// Add tier stat info:
if($time < $row['reap'] || ($dt < 60 && $row['count'] < 100) || $dt < 30)
{
	if(empty(@$tierCounts[$tier]))
	{
		$tierCounts[$tier] = 0;
	}
	$tierCounts[$tier]++;
}

if($roomCount>10 || $row['count']<50)
{
	continue;
}

?>
<tr class="<?=implode(' ',$class)?>">
<td><b><a href='graph.php?guid=<?=htmlspecialchars($row['guid'])?>'><?=htmlspecialchars($row['room'])?></a></b></td>
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
<?
$newRooms = 0.5;
for($i=17;$i>0;$i--)
{
	$newRooms *= 2;
	$roomsAtTier = 0;
	if(!empty(@$tierCounts[$i]))
	{
		$roomsAtTier = $tierCounts[$i];
	}
	$newRooms -= $roomsAtTier;
	if($i==8) $newRoomsT8 = $newRooms;
}
$newRoomsT1 = $newRooms
?>
<tr>
<td></td>
<td></td>
<td style="text-align: right"><b>New T8s Needed For T17</b></td>
<td><?=($newRoomsT8>0)?$newRoomsT8:"<b>None!</b>"?></td>
<td colspan='7' class='text-danger'>This may be <b>inaccurate</b> during merges.</td>
</tr>
</tbody>
</table>
Contribute data using the <a href='https://raw.githubusercontent.com/jhon/robintracker/master/robintracker.user.js'>Standalone Userscript</a> or by enabling contribution in a compatible script like <a href='https://github.com/5a1t/parrot'>Parrot</a>, <a href='https://github.com/vartan/robin-grow'>Robin-Grow</a> or <a href='https://github.com/keythkatz/Robin-Autovoter'>Robin-Autovoter</a>.<br />
Get the most out of Robin: <a href='https://www.reddit.com/r/joinrobin/comments/4d8dlp/guide_20_list_of_most_known_scripts_and_how_to_be/'>List of Most Known Scripts</a><br />
Want more Robin data? Checkout the <a href='https://www.reddit.com/r/robintracking/comments/4czzo2/robin_chatter_leader_board_official/'>Official Leader Board</a>, <a href='http://robintree-apr3.s3-website-us-east-1.amazonaws.com/'>RobinTree</a>, and <a href='http://justinhart.net/robintable/'>Robin Table</a>.<br />
Found an issue? Want to contribute Code? Made your own Robin Mashup? Let me know on <a href='https://github.com/jhon/robintracker'>GitHub</a>!<br />
<br />
<?=intval($totalBeacons/2)?> updates for <?=$roomCount?> rooms in the last minute |
<?php
$end_time = explode(' ',microtime());
$total_time = ($end_time[0] + $end_time[1]) - $start_time;
printf("Page generation took %.3fs",$total_time);
?>

<!-- Shoutout to the Romanian (or person tunneling through Romania). <3 Without you this service wouldn't be anywhere near as good as it is today you wonderful pain in the ass -->

<?=@$footer?>
</body>
 </html>
