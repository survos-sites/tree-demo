// const $ = require('jquery');
require('jstree'); // add jstree to jquery

// may want to see https://www.manning.com/books/extending-jquery

const contentTypes = {
    'PATCH': 'application/merge-patch+json',
    'POST': 'application/json'
};

export class FileManagerApp
{
    // pass options, which override what's in .data?
    constructor($element) {
        // console.log(data);
        this.$element = $element;
        this.jstree = this.configure($element);
        this.addListeners();
        this.url = $element.data('apiBase');
        if (this.url === undefined) {
            this.error('data-api-base is required, eventually pass in as options?');
        }

        // this.jstree = this.$element.jstree(
        //     {
        //         core: {data: data },
        //         plugins: ["contextmenu", "dnd", "search", "state", "types", "wholerow"]
        //
        //     });

        // this sets up the core, listeners are already attached.  check_callback should be in the listeners if possible.
       this.jstree = $element
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
                            this.warning('data url ', url, this.url);
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


        this.render();
    }

    error(msg) {
        console.error(msg);
    }


    addListeners() {
        this.$element
            .on('changed.jstree', this.onChanged) // triggered when selection changes, can be multiple, data is tree data, not node data
            .on('ready.jstree', function (e, data) {
                console.warn('ready.jstree fired.');
            })
            .on('ready.jstree', function (e, data) {
                console.warn('ready.jstree second on call.');
            })
        ;

        this.$element
            // listen for events
            .on('changed.jstree', function (e, data) { // triggered when selection changes, can be multiple, data is tree data, not node data
                const {action, node, selected, instance} = data;
                // console.log(e.type, action, node, selected.join(','), instance);
                var i, j, r = [], ids = [];
                for (i = 0, j = selected.length; i < j; i++) {
                    let node = instance.get_node(selected[i]);
                    console.log(i, node, node.data);
                    r.push(node.text);
                    ids.push(node.id );
                }
                $('#jstree_event_log').html(data.action + ': ' + r.join(', ') + ' IDS: ' + ids.join(','));
            })
            .on('create_node.jstree', (e, data) => {
                const {node, parent, position} = data;
                let parentNode = data.instance.get_node(parent);
                console.warn(e.type, node, parent, parentNode);
                console.log(parentNode.id, parentNode.text);
                let text = parentNode.text + ' child node';
                // parentId is null, not sure why!
                console.log('e', e, e.currentTarget);
                // let thisTree = locationTree.jstree(true);
                // let thisTree = e.currentTarget.jstree(true);

                // let parent = thisTree.find('//' + parentId);
                console.log(parent);

                let buildingId = 1;
                // var node = $('#dashboardTree').jstree(true).find('//something');
                this.itemApiCall(node, 'POST', {
                    code: node.id,
                    building: "/api/buildings/" + buildingId,
                    parent: '/api/locations/' + parentNode.id,
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
                this.itemApiCall(node, 'PATCH', {name: text});
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

    onReady(e, data) {
        console.warn('jstree onReady fired.');
    }

    onChanged(e, data) {
        const {action, node, selected, instance} = data;
        // console.log(e.type, action, node, selected.join(','), instance);
        var i, j, r = [], ids = [];
        for (i = 0, j = selected.length; i < j; i++) {
            let node = instance.get_node(selected[i]);
            // console.log(i, node, node.data);
            r.push(node.text);
            // console.log(r);
            // ids.push(node.data.databaseId);
        }
    }

    configure($element)
    {

        this.tree = $element
            .jstree({
                "core" : {
                    animation : 0,
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node', 'copy_node' or 'edit'
                    check_callback : function (operation, node, node_parent, node_position, more) {
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
                                        ok: function() {

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
                    'force_text' : true,
                    "themes" : { "stripes" : true },
                    'data' : {
                        url : (node) => {
                            // console.log('data.url: calling ' + this.url);

                            // @todo: add params to node
                            return this.url; // or set this in api_platform routes?
                        },
                        success: function(data) {
                            // we've received the jsTree formatted data.
                            // console.warn('!!', data);
                        },

                        // api_platform calls return JSON in a certain format, but js-tree needs it in another.
                        converters:
                            {
                                "text json": function (data) {
                                    // console.error(data);
                                    return JSON.parse(data).map( x => {
                                        return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                                    });
                                }
                            },
                        // this is the data SENT to the server
                        'data' : function (node) {
                            return {'fields' : ['parentId', 'name'] };
                            // return { id : node.id }; e.g. send # if root node.  Maybe send buildingId?
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
            .on('ready.jstree', function (e, data) {
                // $(this).jstree("open_all");
                console.warn('ready.jstree');
                // demo_save();
            })
            .on("loaded.jstree", function (event, data) {
                console.warn('loaded.');
                $(this).jstree("open_all");
            });
        ;


    }

    render() {

        // this.$element.jstree(true).settings.core.data = ['New Data'];

        this.$element.jstree(true).refresh();
        return;
        let $element = this.$element;
        console.log('calling render()');
        // $('#jstree_demo').html('loading tree.');

        let apiUrlBase = $element.data('api-base');
        this.$element = $element;
        this.url = apiUrlBase;
        console.log('api base: ' + this.url);
        /* @
        this.references = [];
        this.render();
         */

    }

    addClickHandlers() {
        this.$element.on('click', '.js-reference-delete', (event) => {
            this.handleReferenceDelete(event);
        });

        this.$element.on('blur', '.js-edit-filename', (event) => {
            this.handleReferenceEditFilename(event);
        });

    }

    addReference(reference) {
        this.references.push(reference);
        this.render();
    }

    itemApiCall(node, method, data, callback) {

    // let url = $('#config').data('apiUrl');
    let url = this.url;
    if (node['data'] !== null) {
        url += '/' + node.id;
    } else {
        if (method === 'PATCH') {
            method = 'POST';
        }
    }

    url += '.json'; // otherwise, the data is returned in json+ld!
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



    handleReferenceDelete(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        $li.addClass('disabled');

        $.ajax({
            url: '/admin/article/references/'+id,
            method: 'DELETE'
        }).then(() => {
            this.references = this.references.filter(reference => {
                return reference.id !== id;
            });
            this.render();
        });
    }

    handleReferenceEditFilename(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');
        const reference = this.references.find(reference => {
            return reference.id === id;
        });
        reference.originalFilename = $(event.currentTarget).val();

        $.ajax({
            url: '/admin/article/references/'+id,
            method: 'PUT',
            data: JSON.stringify(reference)
        });
    }

    renderExample() {


        const itemsHtml = this.references.map(reference => {
            return `
<li class="list-group-item d-flex justify-content-between align-items-center" data-id="${reference.id}">
    <span class="drag-handle fas fa-bars"></span>
    
    <input type="text" value="${reference.originalFilename}" class="form-control js-edit-filename" style="width: auto;">
    <a target="_blank" href="/uploads/article_reference/${reference.filename}">
    <span class="fas fa-eye"></span>
${reference.filename}
 <img width="75" src="/uploads/article_reference/${reference.filename}" />
</a>

    <span>
        <a href="/admin/article/references/${reference.id}/download" class="btn btn-link btn-sm"><span class="fas fa-download" style="vertical-align: middle"></span></a>
        <button class="js-reference-delete btn btn-link btn-sm"><span class="fas text-danger fa-trash"></span></button>
        
    </span>
</li>
`
        });

        this.$element.html(itemsHtml.join(''));
    }
}



