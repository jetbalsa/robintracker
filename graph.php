<?php
require_once "config.php";

$start_time = explode(' ',microtime());
$start_time = $start_time[0] + $start_time[1];
?>
<?php
//$data = json_decode(file_get_contents("dump/pedigree.json"));
$data = unserialize(file_get_contents('dump/pedigree.bin'));

$guid = htmlspecialchars(@$_GET['guid']);
?>
<!doctype html>
<html>
<head>
    <title>RobinGrapher: <?=$data[$guid]['room']?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open Sans:300italic,400italic,600italic,700italic,400,300,600,700">
    <style>
    * { margin: 0; padding: 0; }
    body {
        font-size: 15px;
        font-family: "Open Sans", sans-serif;
    }
    
    p { margin: 5px 0; }
    
    svg {
        border: 1px solid #e3e3e3;
        margin: 20px;
    }
    
    #info {
        display: block;
        padding: 10px 20px;
    }
    .title {
        font-weight: 600;
    }
    
    #more {
        position: fixed;
        top: 10px;
        right: 20px;
        color: gray;
    }
    
    #more .morehover {
        display: none;
        position: absolute;
        top: 100%;
        right: 100%;
        font-size: 12px;
        width: 350px;
        background: white;
        padding: 10px;
        box-shadow: 0 -1px 0 #efefef,0 0 2px rgba(0,0,0,0.16),0 1.5px 4px rgba(0,0,0,0.18)
    }
    #more:hover .morehover {
        display: block;
    }
    </style>
    <script src="https://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <script src="vis_main.js" charset="utf-8"></script>
    <script>
        var data = [
<?php
$nodes = array($guid);
while(count($nodes))
{
    $n = $data[array_shift($nodes)];
    
    $parent = 'null';
    $name = $n['room'].' ('.$n['tier'].')';
    
    if(!empty($n['parent']))
    {
        $parent = $data[$n['parent']]['room'].' ('.$data[$n['parent']]['tier'].')';
    }
    echo "            //".$n['guid']."\n";
    echo "            {\n";
    echo "                name: '".$name."',\n";
    echo "                parent: '".$parent."',\n";
    echo "            },\n";

    $child0 = $n['children'][0];
    $child1 = $n['children'][1];
    if(!empty($child0))
    {
        array_push($nodes,$child0);
    }
    if(!empty($child1))
    {
        array_push($nodes,$child1);
    }
}
?>
        ];
    </script>
</head>
<body>
    <div id="info">
        <p class="title"><?=$data[$guid]['room']?> (Tier <?=$data[$guid]['tier']?>)</p>
        <p>Click nodes to toggle. Zoom with scroll. Pan with mouse drag.</div>
    </div>
    <div id="more">
        <span>(?)</span>
        <div class="morehover">
            <p>A visualization of the <?=$data->$guid->room?> family tree</p>
            <p>Visualization created by /u/kwwxis</p>
            <p>Modified by /u/GuitarShirt to use RobinTracker data</p>
<?php
$end_time = explode(' ',microtime());
$total_time = ($end_time[0] + $end_time[1]) - $start_time;
printf("            <p>Page generation took %.3fs</p>\n",$total_time);
?>
        </div>
    </div>
    <svg id='vis_svg'></svg>
    <script>
    var vis = new vis_main('#vis_svg')
        .setData(data)
        .setZoomInit(0.6)
        .start();
    </script>
<?=@$footer?>
</body>
</html>
