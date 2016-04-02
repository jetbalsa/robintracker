<!DOCTYPE html>
<html>
<head>
    <title>RobinTracker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Room</th>
                <th>Total Count</th>
                <th>Last Time Seen</th>
            </tr>
        </thead>
        <tbody>
<?php

// SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track GROUP BY `room`) ORDER BY `track`.`count` DESC
require("medoo.php");
$data = $database->query("SELECT * FROM track WHERE `id` IN (SELECT MAX(`id`) FROM track GROUP BY `room`) ORDER BY `track`.`count` DESC")->fetchAll();
foreach($data as $row){
    echo "<tr>";
    echo "<td>" . $row["count"] . "</td>";
    echo "<td>" . $row["room"] . "</td>";
    echo "<td>" . date(DATE_RFC2822, $row["timestamp"]) . "</td>";
    echo "</tr>";
}
?>

        </tbody>
    </table>
</body>
</html>
