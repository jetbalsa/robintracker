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
    <!--[if lt IE 9]>
    <script>
        document.createElement('header');
        document.createElement('nav');
        document.createElement('section');
        document.createElement('article');
        document.createElement('aside');
        document.createElement('footer');
    </script>
    <![endif]-->
    <script src="https://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open Sans:300italic,400italic,600italic,700italic,400,300,600,700">
    <style>
    * {
        margin: 0;
        padding: 0;
    }
    body {
        font-size: 15px;
        font-family: "Open Sans", sans-serif;
    }
    
    p {
        margin: 5px 0;
    }
    
    svg {
        border: 1px solid #e3e3e3;
        margin: 20px;
    }
    
	.link {
        fill: none;
        stroke: #ccc;
        stroke-width: 2px;
	}
    
    .node circle {
        cursor: pointer;
        fill: #fff;
        stroke: steelblue;
        stroke-width: 1.5px;
    }

    .node text {
        font-size: 11px;
        font-family: "Open Sans", sans-serif;
    }

    path.link {
        fill: none;
        stroke: #ccc;
        stroke-width: 1.5px;
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
    <script>
    function vis_redraw() {
        vis.getSVG().attr("transform",
            "translate(" + d3.event.translate + ")"
             + " scale(" + d3.event.scale + ")");
    }
    function vis_main() {
        var simple_data = [
<?php
$nodes = array($guid);
while(count($nodes))
{
    $n = $data[array_shift($nodes)];
    
    // Initial Node
    if(empty($n['parent']))
    {
        echo "            \"".$n['room']." (".$n['tier'].") + null = null\",\n";
    }
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
    $child0 = empty($child0)?'null':$data[$child0]['room'].' ('.$data[$child0]['tier'].')';
    $child1 = empty($child1)?'null':$data[$child1]['room'].' ('.$data[$child1]['tier'].')';
    
    echo "            \"$child0 + $child1 = ".$n['room']." (".$n['tier'].")\",\n";
}
?>
        ];
        
        var root,
            tree,
            svg,
            diagonal,
            zm;
            
        this.getSVG = function() { return svg; }
        this.getTree = function() { return tree; }
        this.getRoot = function() { return root; }
        this.setRoot = function(new_root) { root = new_root; }
        
        var // viewport dimensions
            vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
            vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0),
            // svg dimension margins
            m = [20, 60, 20, 60],
            // svg dimensions - can be changed to any desired numbers
            w = vw < 800 ? vw : vw - m[1] - m[3],
            h = vh - m[0] - m[2],
            // node next id
            i = 0,
            // zoom min, max, initial
            zm_min = 0.2,
            zm_max = 10,
            zm_init = 0.6,
            // pan initial
            px_init = 120,
            py_init = w / 3;
        
        this.init = function() {
            var data = [],
                treeData = [];
            
            for (var i = 0; i < simple_data.length; i++) {
                var item_data0 = simple_data[i].split("+");
                var item_data1 = item_data0[1].split("=");
                var child0 = item_data0[0].trim();
                var child1 = item_data1[0].trim();
                var parent = item_data1[1].trim();
                
                var append0 = {
                    "name": child0,
                    "parent": parent
                };
                
                var append1 = {
                    "name": child1,
                    "parent": parent
                };
                
                if (child0 != "null") {
                    data.push(append0);
                }
                if (child1 != "null") {
                    data.push(append1);
                }
            }

            // Convert flat data to tree
            
            var dataMap = data.reduce(function(map, node) {
                map[node.name] = node;
                return map;
            }, {});
            
            // create the tree array
            data.forEach(function(node) {
                var parent = dataMap[node.parent];
                if (parent) {
                    (parent.children || (parent.children = [])).push(node);
                } else {
                    treeData.push(node);
                }
            });
            root = treeData[0];
        },
        // toggle a node d, calling update() is necessary to see changes
        this.toggle = function(d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
            } else {
                d.children = d._children;
                d._children = null;
            }
        },
        this.start = function() {
            var instance = this;
            this.init();
            
            tree = d3.layout.tree()
                .size([h, w])
                .nodeSize([30, 150])
                .separation(function(a, b) {
                    return (a.parent == b.parent ? 1 : 1.5);
                });
            
            tree.size = function(x) {
                if (!arguments.length)
                    return nodeSize ? null : size;
                nodeSize = (size = x) == null;
                return tree;
            };

            tree.nodeSize = function(x) {
                if (!arguments.length) return nodeSize ? size : null;
                nodeSize = (size = x) != null;
                return tree;
            };
            
            diagonal = d3.svg.diagonal()
                .projection(function(d) { return [d.y, d.x]; });

            svg = d3.select("body").append("svg:svg")
                .attr("id", "vis_svg")
                .attr('width',  w)
                .attr('height', h)
                .call(zm = d3.behavior.zoom().scaleExtent([zm_min,zm_max]).on("zoom", vis_redraw))
                .append("svg:g")
                    .attr("transform", "translate(" + px_init + "," + py_init + ")scale("+zm_init+")");
            
            zm.translate([px_init, py_init]).scale(zm_init);
            
            root.x0 = h / 2;
            root.y0 = 0;
            this.update(root);
            return this;
        },
        this.update = function(source) {
            var instance = this;
            var duration = d3.event && d3.event.altKey ? 5000 : 500;
            
            // compute the new height
            var levelWidth = [1];
            var childCount = function(level, n) {
                if(n.children && n.children.length > 0) {
                    if(levelWidth.length <= level + 1)
                        levelWidth.push(0);
                    
                    levelWidth[level+1] += n.children.length;
                    n.children.forEach(function(d) {
                        childCount(level + 1, d);
                    });
                }
            };
            childCount(0, root);  
            var newHeight = d3.max(levelWidth) * 20; // 20 pixels per line  
            tree = tree.size([newHeight, w]);
            
            // Compute the new tree layout.
            var nodes = tree.nodes(root).reverse();

            // Normalize for fixed-depth.
            nodes.forEach(function(d) { d.y = d.depth * 180; });

            // Update the nodes…
            var node = svg.selectAll("g.node")
                .data(nodes, function(d) { return d.id || (d.id = ++i); });

            // Enter any new nodes at the parent's previous position.
            var nodeEnter = node.enter().append("svg:g")
                .attr("class", "node")
                .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
                .on("click", function(d) { instance.toggle(d); instance.update(d); });

            nodeEnter.append("svg:circle")
                .attr("r", 1e-6)
                .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

            nodeEnter.append("svg:text")
                .attr("x", function(d) { return d.children || d._children ? -10 : 10; })
                .attr("dy", ".35em")
                .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
                .text(function(d) { return d.name; })
                .style("fill-opacity", 1e-6);

            // Transition nodes to their new position.
            var nodeUpdate = node.transition()
                .duration(duration)
                .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

            nodeUpdate.select("circle")
                .attr("r", 4.5)
                .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

            nodeUpdate.select("text")
                .style("fill-opacity", 1);

            // Transition exiting nodes to the parent's new position.
            var nodeExit = node.exit().transition()
                .duration(duration)
                .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
                .remove();

            nodeExit.select("circle")
                .attr("r", 1e-6);

            nodeExit.select("text")
                .style("fill-opacity", 1e-6);

            // Update the links…
            var link = svg.selectAll("path.link")
                .data(tree.links(nodes), function(d) { return d.target.id; });

            // Enter any new links at the parent's previous position.
            link.enter().insert("svg:path", "g")
                .attr("class", "link")
                .attr("d", function(d) {
                    var o = {x: source.x0, y: source.y0};
                    return diagonal({source: o, target: o});
                })
                .transition()
                .duration(duration)
                    .attr("d", diagonal);

            // Transition links to their new position.
            link.transition()
                .duration(duration)
                .attr("d", diagonal);

            // Transition exiting nodes to the parent's new position.
            link.exit().transition()
                .duration(duration)
                .attr("d", function(d) {
                    var o = {x: source.x, y: source.y};
                    return diagonal({source: o, target: o});
                })
                .remove();

            // Stash the old positions for transition.
            nodes.forEach(function(d) {
                d.x0 = d.x;
                d.y0 = d.y;
            });
            
            return this;
        }
    };
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
    <script>
    var vis = new vis_main().start();
    </script>
<?=@$footer?>
</body>
</html>
