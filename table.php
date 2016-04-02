<?php
require_once "config.php";

// TODO:
//  * The reap timer needs to be displayed
//  * Need to readd last update. I removed it for cleanliness
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
</tr></thead>

<tbody>
<?foreach($data as $row):?>
<tr>
<td><?=$row['room']?></td>
<td><?=$row['count']?></td>
<td><?=$row['grow']?></td>
<td><?=$row['stay']?></td>
<td><?=$row['abandon']?></td>
<td><?=$row['novote']?></td>
</td>
<?endforeach;?>
</tbody>
</table>
<br /><br />
<a href='https://github.com/jrwr/robintracker'>Fork me on GitHub</a>.<br/>
<a href='https://github.com/keythkatz/Robin-Autovoter'>Robin Autovote Script</a>.<br />
</body>
</html>
