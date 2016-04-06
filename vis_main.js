
var vis_global = {
    base_style:' \
        .link { \
            fill: none; \
            stroke: #ccc; \
            stroke-width: 2px; \
        } \
        .node circle { \
            cursor: pointer; \
            fill: #fff; \
            stroke: steelblue; \
            stroke-width: 1.5px; \
        } \
        .node text { \
            font-size: 11px; \
        } \
        path.link { \
            fill: none; \
            stroke: #ccc; \
            stroke-width: 1.5px; \
        }',
    isFirstCall: true,
    firstCall: function() {
        if (!this.isFirstCall)
            return;
        this.isFirstCall = false;
        
        var head = document.head || document.getElementsByTagName('head')[0],
            style = document.createElement('style');

        style.type = 'text/css';
        if (style.styleSheet){
            style.styleSheet.cssText = this.base_style;
        } else {
            style.appendChild(document.createTextNode(this.base_style));
        }

        head.appendChild(style);
    },
    list: []
};

function vis_main(vis_sel) {
    var instance = this,
        isStarted = false,
        selector = vis_sel,
        root,
        tree,
        svg,
        svg_g,
        diagonal,
        zm,
        simple_data = [],
        data = [],
    
        // viewport dimensions
        vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
        vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0),
        // svg dimensions - can be changed to any desired numbers
        w = vw < 800 ? vw : vw - 120,
        h = vh - 40,
        // node next id
        i = 0,
        // zoom min, max, initial
        zm_min = 0.2,
        zm_max = 10,
        zm_init = 1,
        zm_current = zm_init,
        // pan initial
        px_init = 120,
        py_init = w / 3,
        pn_current = [px_init, py_init];
        
    vis_global.list.push(this);
    
    this.getSelector = function() {
        return selector;
    };
    
    // *************** Get/Set data functions ***************
    // Any changes to the data after start() must be made active
    // by calling reset()
    
    this.getSimpleData = function() { return simple_data };
    this.setSimpleData = function(new_simple_data) {
        simple_data = new_simple_data;
        return this;
    };
    this.getData = function() { return data; };
    this.setData = function(new_data) {
        data = new_data;
        return this;
    };
    
    // *************** Dimension functions ***************
    // After start(), reset() must be called for any dimensions
    // changes to be active
    
    this.getDimensions = function() {
        return [w, h];
    };
    this.setDimensions = function(dim) {
        w = dim[0];
        h = dim[1];
        return this;
    };
    
    this.getWidth = function() {
        return w;
    };
    this.setWidth = function(new_w) {
        w = new_w;
        return this;
    };
    
    this.getHeight = function() {
        return h;
    };
    this.setHeight = function(new_h) {
        h = new_h;
        return this;
    };
    
    // *************** Zoom/Pan functions ***************
    // The set functions here only work before start()
    // reset() is not necessary to use pan,translate,zoom,scale,panzoom
    
    this.setZoomScale = function(min, max) {
        zm_min = min;
        zm_max = max;
        return this;
    };
    this.setZoomInit = function(zoom_init) {
        zm_init = zoom_init;
        if (!isStarted)
            zm_current = zm_init;
        return this;
    };
    this.setPanInit = function(pan_x, pan_y) {
        px_init = pan_x,
        py_init = pan_y;
        if (!isStarted)
            pn_current = [px_init, py_init];
        return this;
    };
    
    this.pan = function(new_pan) {
        this.panzoom(zm_current, new_pan);
    };
    this.translate = function(new_pan) {
        this.pan(new_pan);
    };
    
    this.zoom = function(new_zoom) {
        this.panzoom(new_zoom, pn_current);
    };
    this.scale = function(new_zoom) {
        this.zoom(new_zoom);
    };
    
    this.panzoom = function(new_zoom, new_pan) {
        zm.translate(new_pan).scale(new_zoom);
        svg_g.attr("transform",
            "translate(" + new_pan  + ")"
             + " scale(" + new_zoom + ")");
    };
    
    // *************** Get internal data functions ***************
    // these functions return data structures used internally, be
    // careful when using them. When changing data, it is recommended
    // you use setSimpleData or setData instead of directly modifying
    // the root
    
    this.getSVG = function() { return svg; };
    this.getG = function() { return svg_g; };
    this.getTree = function() { return tree; };
    this.getRoot = function() { return root; };
    this.getZoom = function() { return zm; };
    
    // *************** Main functions ***************
    
    // For any changes to the data or dimensions after start(), this should be called
    this.reset = function() {
        this.pack();
        this.update(root);
    };
    
    // Can only be used when called by a d3 event, do not call directly
    this._redraw = function() {
        zm_current = d3.event.translate;
        pn_current = d3.event.scale;
        svg_g.attr("transform",
            "translate(" + d3.event.translate + ")"
             + " scale(" + d3.event.scale + ")");
    };
    
    // Updates root based on what's in simple_data and/or data
    this.pack = function() {
        var treeData = [];
        
        if (simple_data.length != 0) {
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
        }
        
        if (data.length == 0) {
            return;
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
        root.x0 = h / 2;
        root.y0 = 0;
        
        if (tree && svg) {
            tree.size([h, w]);
            svg.attr('width',  w)
               .attr('height', h);
        }
    };
    
    // toggle a node d, calling update() is necessary to see changes
    this.toggle = function(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else {
            d.children = d._children;
            d._children = null;
        }
    };
    
    this.start = function() {
        vis_global.firstCall();
        
        if (isStarted) {
            return false;
        }
        isStarted = true;
        
        this.pack();
        
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
        
        svg = d3.select(selector);
        svg_g = d3.select(selector)
            .attr('width',  w)
            .attr('height', h)
            .call(zm = d3.behavior.zoom().scaleExtent([zm_min,zm_max]).on("zoom", this._redraw))
            .append("svg:g")
                .attr("transform", "translate(" + px_init + "," + py_init + ")scale("+zm_init+")");
        
        zm.translate([px_init, py_init]).scale(zm_init);
        
        if (root == null) {
            return null;
        }
        
        this.update(root);
        return this;
    };
    this.update = function(source) {
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

        // Update the nodesc
        var node = svg_g.selectAll("g.node")
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

        // Update the linksc
        var link = svg_g.selectAll("path.link")
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
    };
    
    return this;
};