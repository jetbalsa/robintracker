<?php
require_once "config.php";

// TODO:
//  * This query needs to ignore rooms that haven't been updated within the last 5 minutes
//  * The reap timer needs to be displayed
//  * Need to readd last update. I removed it for cleanliness
$data = $database->query("SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track GROUP BY `room`) ORDER BY `track`.`count` DESC")->fetchAll();
?>
<html>
<head>
<title>Robin Tracker</title>
<style type='text/css'>
td {
  background-color: #eeeeee;
  padding-left: 1em;
  padding-right: 1em;
}
</style>
</head>
<body>
<h1>Robin Tracker</h1>

<table>
<th><tr>
<td><b>Room</b></td>
<td><b>Total</b></td>
<td><b>Grow</b></td>
<td><b>Stay</b></td>
<td><b>Abandon</b></td>
<td><b>Abstains</b></td>
</tr></th>

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
</table>
<br /><br />
<a href='https://github.com/jrwr/robintracker'>Fork me on GitHub</a>.<br/>
<a href='https://github.com/keythkatz/Robin-Autovoter'>Robin Autovote Script</a>.<br />
</body>
</html>
