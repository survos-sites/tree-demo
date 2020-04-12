require('jstree');
const $ = require('jquery');

function demo_create() {
        var ref = $('#jstree_demo').jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        sel = sel[0];
        sel = ref.create_node(sel, {"type":"file"});
        if(sel) {
            ref.edit(sel);
        }
    }

function demo_rename() {
    var ref = $('#jstree_demo').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    sel = sel[0];
    ref.edit(sel);
}

function demo_save() {

    let json = $('#jstree_demo').jstree().get_json(null, {flat: true});
    console.log(json);

    let simplifedJson = json.map( (node) => {
        return (({ id, text, parent }) => ({ id, text, parent }))(node);
    });

    let saveUrl = $('#config').data('saveUrl');
    console.log(saveUrl, simplifedJson);
    $.getJSON(saveUrl, {'json': simplifedJson}, function(data) {
        console.log(data);
    });

    let jsonString = JSON.stringify(simplifedJson, null, 4);
    console.log(jsonString);
    $('#jstree_event_log').html(jsonString);

}

function demo_delete() {
    var ref = $('#jstree_demo').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    ref.delete_node(sel);
}

    var to = false;
    $('#demo_q').keyup(function () {
        if(to) { clearTimeout(to); }
        to = setTimeout(function () {
            var v = $('#demo_q').val();
            $('#jstree_demo').jstree(true).search(v);
        }, 250);
    });

    let url = $('#config').data('url');
    console.log(url);


    $('#js_save_button').click(function() {
        demo_save();

    });

    $('#jstree_demo')
        .jstree({
            "core" : {
                "animation" : 0,
                "check_callback" : true,
                'force_text' : true,
                "themes" : { "stripes" : true },
                'data' : {
                    'url' : function (node) {
                        return url;
                        // node.id === '#' ? 'https://www.jstree.com/static/3.3.9/assets/ajax_roots.json' : 'https://www.jstree.com/static/3.3.9/assets/ajax_children.json';
                        // return node.id === '#' ? '/static/3.3.9/assets/ajax_demo_roots.json' : '/static/3.3.9/assets/ajax_demo_children.json';
                    },
                    'data' : function (node) {
                        return { 'id' : node.id };
                    }
                }
            },
            "types" : {
                "#" : { "max_children" : 1, "max_depth" : 4, "valid_children" : ["root"] },
                "root" : { "icon" : "/static/3.3.9/assets/images/tree_icon.png", "valid_children" : ["default"] },
                "default" : { "valid_children" : ["default","file"] },
                "file" : { "icon" : "glyphicon glyphicon-file", "valid_children" : [] }
            },
            "plugins" : [ "contextmenu", "dnd", "search", "state", "types", "wholerow" ]
        })
        .on('ready.jstree', function(e, data) {
            // demo_save();
        })
    ;

