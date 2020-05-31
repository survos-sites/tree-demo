const $ = require('jquery');
require('jstree');
require('jquery-confirm');

// global.$ = $; // hack if you need a global $

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

const swal = require('sweetalert');


import {LocationManagerApp} from './LocationManagerApp';

const locationManager = new LocationManagerApp($('#jstree_demo'));

// locationManager.render();

// require('./LocationManagerApp');
// import ExclamFunction from './tutorial'; <-- use import when possible!
// console.log('hello' + ExclamFunction(3))
// let manager = new LocationManager($('#location_manager', $, Routing));

let contentTypes = {
    'PATCH': 'application/merge-patch+json',
    'POST': 'application/json'
};

function itemApiCall(node, method, data, callback) {
    let url = $('#config').data('apiUrl');
    if (node['data'] !== null) {
        url += '/' + node.data.databaseId;
    } else {
        if (method === 'PATCH') {
            method = 'POST';
        }
    }
    console.log(url, method, data);
    $.ajax(url, {
        data: JSON.stringify(data),
        // dataType: "json", // this is the RETURN data
        contentType: contentTypes[method],
        method: method
    }).done( (data) =>  {
        callback(data);
        console.log(data);

    }).fail( (data) => {
        console.error(data);
    })
}

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

function confirm_delete()
{
    return $.confirm({
        title: 'Confirm!',
        content: 'Simple confirm!',
        buttons: {
            confirm: function () {
                console.log('delete it!');
                return true;
                $.alert('Confirmed!');
            },
            cancel: function () {
                console.log('Leave it be.');
                return false;
                $.alert('Canceled!');
            },
            somethingElse: {
                text: 'Something else',
                btnClass: 'btn-blue',
                keys: ['enter', 'shift'],
                action: function(){
                    $.alert('Something else?');
                }
            }
        }
    });
}
function demo_delete() {
    var ref = $('#jstree_demo').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    ref.delete_node(sel);
}

// start of code

function main() {
    var to = false;
    $('#demo_q').keyup(function () {
        if (to) {
            clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $('#demo_q').val();
            $('#jstree_demo').jstree(true).search(v);
        }, 250);
    });

    let url = $('#config').data('url');
    let apiUrl = $('#config').data('apiUrl');
    console.log(url, apiUrl);


    $('#js_save_button').click(function () {
        demo_save();
    });
    $('.js-create').click(demo_create);
    $('.js-delete').click(demo_delete);
    $('.js-rename').click(demo_rename);

    let locationTree = $('#jstree_demo')
        .jstree({
            "core": {
                animation: 0,
                // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node', 'copy_node' or 'edit'
                check_callback: function (operation, node, node_parent, node_position, more) {
                    switch (operation) {
                        case 'delete_node':
                            return confirm("Are you sure you want to delete " + node.text);
                        case 'create_nodex':
                            console.log(node_parent);
                            $.confirm({
                                title: 'Create a new location',
                                content: '' +
                                    '<form action="" class="formName">' +
                                    '<div class="form-group">' +
                                    '<label>New Location Name (in PARENT)</label>' +
                                    '<input type="text" placeholder="Location Name" class="name form-control" required />' +
                                    '</div>' +
                                    '</form>',
                                buttons: {
                                    ok: function () {

                                        location.href = this.$target.attr('href');
                                    },
                                    cancel: function () {
                                        //close
                                    },
                                }
                            });
                            console.warn('returning false in check_callback for ' + operation);
                            return false; // manually create a node with our name.
                        case 'create_node':
                        case 'rename_node':
                        case 'edit':
                            // @todo: check that we're logged in and have permission?  Or ...?
                            return true;
                        default:
                            console.error('unhandled check_callback: ' + operation);
                    }
                },
                "xxcheck_callback": function (operation, node, node_parent, node_position, more) {
                    console.log(operation, node, node.data, node_position, more);
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node', 'copy_node' or 'edit'
                    // in case of 'rename_node' node_position is filled with the new node name
                    if (operation === 'delete_node') {

                        if (!confirm_delete()) {
                            return false;
                        }
                        return true;
                    } else {
                        return true;
                    }
                },
                'force_text': true,
                "themes": {"stripes": true},
                'data': {
                    'url': function (node) {
                        return url;
                        // node.id === '#' ? 'https://www.jstree.com/static/3.3.9/assets/ajax_roots.json' : 'https://www.jstree.com/static/3.3.9/assets/ajax_children.json';
                        // return node.id === '#' ? '/static/3.3.9/assets/ajax_demo_roots.json' : '/static/3.3.9/assets/ajax_demo_children.json';
                    },
                    'data': function (node) {
                        console.warn(node);
                        return {'id': node.id};
                    }
                }
            },
            "types": {
                "#": {"max_children": 1, "max_depth": 4, "valid_children": ["root"]},
                "root": {"icon": "/static/3.3.9/assets/images/tree_icon.png", "valid_children": ["default"]},
                "default": {"val_children": ["default", "file"]},
                "file": {"icon": "glyphicon glyphicon-file", "valid_children": []}
            },
            "plugins": ["contextmenu", "dnd", "search", "state", "types", "wholerow"]
        })

        // listen for events
        .on('changed.jstree', function (e, data) { // triggered when selection changes, can be multiple, data is tree data, not node data
            const {action, node, selected, instance} = data;
            console.log(e.type, action, node, selected.join(','), instance);
            var i, j, r = [], ids = [];
            for (i = 0, j = selected.length; i < j; i++) {
                let node = instance.get_node(selected[i]);
                console.log(i, node, node.data);
                r.push(node.text);
                ids.push(node.data.databaseId);
            }
            $('#jstree_event_log').html(data.action + ': ' + r.join(', ') + ' IDS: ' + ids.join(','));
        })
        .on('create_node.jstree', (e, data) => {
            const {node, parent, position} = data;
            let parentNode = data.instance.get_node(parent);
            console.warn(e.type, node, parent, parentNode);
            console.log(parentNode.data.databaseId, parentNode.text);
            let text = parentNode.text + ' child node';
            // parentId is null, not sure why!
            console.log('e', e, e.currentTarget);
            let thisTree = locationTree.jstree(true);
            // let thisTree = e.currentTarget.jstree(true);

            // let parent = thisTree.find('//' + parentId);
            console.log(parent);

            let buildingId = 1;
            // var node = $('#dashboardTree').jstree(true).find('//something');
            itemApiCall(node, 'POST', {
                code: node.id,
                building: "/api/buildings/" + buildingId,
                parent: '/api/locations/' + parentNode.data.databaseId,
                name: text
            }, function (data) {
                console.error(data);
            });
        })
        .on('rename_node.jstree', (e, data) => {
            const {node, text, old} = data;
            console.warn(node, node.parent, text, old);
            console.log(node);
            // if there's no databaseId, then this is really a new node.  If the title blank, we shouldn't create it
            itemApiCall(node, 'PATCH', {name: text});
            /*
            if (node['data'] === null) {
                itemApiCall(node, 'POST', {name: text});
            } else {
            }
             */
        })
        .on('delete_node.jstree', function (e, data) {
            var i, j, r = [];
            console.log(e, data, data.action, data.node.data.databaseId, data.node, data.node.data);
            $('#jstree_event_log').html('DELETE! ' + data.node.data.databaseId);
            $.ajax(apiUrl + "/" + data.node.data.databaseId, {method: 'DELETE'}
            ).done((data) => {
                console.log('Success!', data)
            })
            ;

            let nodeData = data.node.data;
            console.log(e, data, data.action, data.node, nodeData.databaseId);
            console.warn('Deleting ' + nodeData.databaseId);
        })

        .on('ready.jstree', function (e, data) {
            // demo_save();
        })
    ;

}