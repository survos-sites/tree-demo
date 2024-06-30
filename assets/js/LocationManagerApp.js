const $ = window.jQuery; // require('jquery');
import('jstree'); // add jstree to jquery

// may want to see https://www.manning.com/books/extending-jquery

const contentTypes = {
    'PATCH': 'application/merge-patch+json',
    'POST': 'application/json'
};

export class LocationManagerApp
{
    // jQuery element (a div) is first argument.
    constructor($element, options) {
        this.options = options;
        console.log(options.url);
        this.url = options.url;
        this.configure($element);
        this.addListeners($element);
        // this.logDiv = $('#jstree_event_log');

        if (0)
        $element.jstree({
            "core" : {
                "animation" : 0,
                "check_callback" : true,
                "themes" : { "stripes" : true },
                'data' : {
                    'url' :  (node) => {
                        return options.url;
                    },
                    converters:
                        {
                            "text json":  (data) => {
                                return JSON.parse(data).map( x => {
                                    return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                                });
                            }
                        },
                    'data' : (node) =>  {
                        // merge the data filter with the specific node for dynamic loading.
                        let dataToSend = { ...this.options.dataWrapper, ...{'id' : node.id }};
                        console.error(dataToSend);
                        return dataToSend;
                    }
                },
            },
            "plugins" : [
                "contextmenu", "dnd", "search",
                "state", "types", "wholerow"
            ]

            ,
        }).on('ready.jstree', (e, data) => {

            $(this).jstree('open_all');
        })


        this.$element = $element;


        return;


        this.jstree = this.configure($element);
        $element.jstree().on('ready.jstree', () => console.log('ready!'));

        // var $treeview = this.jstree;
        // $treeview.jstree().on('ready.jstree', function() {
        //     console.log('opening?');
        //     $treeview.jstree('open_all');
        // });


        this.url = $element.data('apiBase');
        if (this.url === undefined) {
            this.error('data-api-base is required, eventually pass in as options?');
        }
        return; // should be set up in configure.

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
                    // expand_selected_onload : false,
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node', 'copy_node' or 'edit'
                    check_callback: function (operation, node, node_parent, node_position, more) {
                        switch (operation) {
                            case 'delete_node':
                                return confirm("Are you sure you want to delete " + node.text);
                            case 'create_node':
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
                            return confirm_delete();
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
                "plugins": ["search", "state", "types", "wholerow"]
                // "plugins": ["contextmenu", "dnd", "search", "state", "types", "wholerow"]
            })


        console.log(this.$element, this.jstree);
        this.render();
    }

    error(msg) {
        console.error(msg);
    }


    addListeners($element) {
        $element
            .on('changed.jstree', this.onChanged) // triggered when selection changes, can be multiple, data is tree data, not node data
            .on('ready.jstree',  (e, data) => {

                console.warn('ready.jstree fired, so opening_all');
                this.$element.jstree('open_all');
            })
            .on('ready.jstree', function (e, data) {
                console.warn('ready.jstree second on call.');
            })
            .on('loaded.jstree', () => {
                console.warn('loaded.jstree');
                this.$element.jstree('open_all');
            })
            // listen for updates
            .on('changed.jstree', function (e, data) { // triggered when selection changes, can be multiple, data is tree data, not node data
                const {action, node, selected, instance} = data;
                // console.log(e.type, action, node, selected.join(','), instance);
                var i, j, r = [], ids = [];
                for (i = 0, j = selected.length; i < j; i++) {
                    let node = instance.get_node(selected[i]);
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

                let text = parentNode.text + '-' + (parentNode.children.length + 1);
                node.text = text;

                // parentId is null, not sure why!
                // console.log(parent);

                // var node = $('#dashboardTree').jstree(true).find('//something');
                this.collectionApiCall(node, 'POST', {...this.options.dataWrapper, ...{
                    code: node.id,
                    parent: this.url + '/' + parentNode.id,
                    name: text
                }}
                ,  (data) => {
                    // populate the visible node with the created id and name.
                    // node.text = data.name;
                    // node.text = text;
                    // node.id = data.id;
                    // node.data('databaseId', data.id);
                        $(e.currentTarget).jstree(true).set_id(node, data.id);
                        console.log("New node created!", data.name);
                    console.error(node, data);
                });
            })
            .on('rename_node.jstree', (e, data) => {
                const {node, text, old} = data;
                console.warn(e.type, node, text, old);
                // if there's no databaseId, then this is really a new node.  If the title blank, we shouldn't create it

                this.itemApiCall(node, 'PATCH', {name: text});
                /*
                if (node['data'] === null) {
                    itemApiCall(node, 'POST', {name: text});
                } else {
                }
                 */
            })
            .on('move_node.jstree',  (e, data)  => {
                // https://www.jstree.com/api/#/?f=move_node.jstree
                const {node, parent, position, old_parent, old_position, is_multi, old_instance, new_instance} = data;
               console.log('moving', node, parent, new_instance);
                this.itemApiCall(node, 'PATCH', {parent: this.url + '/' + parent});

            })
            .on('delete_node.jstree',  (e, data)  => {
                var i, j, r = [];
                const {node, parentId} = data;
                $('#jstree_event_log').html('DELETE! ' + node.id + '/' + node.text);
                this.itemApiCall(node, 'DELETE');
            })

        ;

    }

    onReady(e, data) {
        console.warn('jstree onReady fired.');
    }

    onChanged(e, data) {
        const {action, node, selected, instance} = data;
        console.log(e.type, action, node, selected.join(','), instance);
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
                            case 'move_node':
                            case 'edit':
                                // @todo: check that we're logged in and have permission?  Or ...?
                                return true;
                            default:
                                console.error('unhandled check_callback: ' + operation);
                                return true;
                        }
                    },
                    'force_text' : true,
                    "themes" : { "stripes" : true },
                    'data' : {
                        'url' : (node) => {
                            console.log('data.url: calling ' + this.url);
                            console.log(node);

                            // @todo: add params to node
                            return this.url; // + '.json'; // or set this in api_platform routes?
                        },
                        success: function(data) {
                            // we've received the jsTree formatted data.
                            // console.warn('!!', data);
                            console.warn('success!', data);
                        },

                        // api_platform calls return JSON in a certain format, but js-tree needs it in another.
                        converters:
                            {
                                "text json": function (dataString) {
                                    let data = JSON.parse(dataString);
                                    return data['hydra:member'].map( x => {
                                        return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                                    });
                                }
                            },
                        // dataType: 'json', // let it come back as json-ld
                        // this is the data SENT to the server
                        'data' :  (node) => {
                            return {...this.options.dataWrapper, ...{'fields': ['parentId', 'name']}};
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
                // "plugins" : [ "search", "state", "types", "wholerow" ]
                "plugins" : [ "contextmenu", "dnd", "search", "state", "types", "wholerow" ]
            })
            .on('xxready.jstree',  (e, data) => {
                console.warn($(e.currentTarget).attr('id'))
                console.warn(e, e.currentTarget, 'ready.jstree (configuration)');
                // $(e.currentTarget).jstree.open_all();
                // this.jstree('open_all');
                // this.tree.open_all();
                // $element.open_all();
                $(this).jstree("open_all");
                // $(this).open_all();
                // demo_save();
            })
        ;
        return this.tree;

    }

    render() {

        // this.$element.jstree(true).settings.core.data = ['New Data'];

        if (this.$element) {
            // this.$element.jstree(true).refresh();
            // this.$element.jstree('open_all');
        }
        return;


        let $element = this.$element;
        console.log('calling render()');
        // $('#jstree_demo').html('loading tree.');

        let apiUrlBase = $element.data('apiBase');
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

    collectionApiCall(node, method, data, callback) {
        // node is the parent node, methods are GET, POST
        $.ajax(this.url, {
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

    itemApiCall(node, method, data, callback) {
    // node is the item node, methods are GET, PATCH, DELETE
    let url = this.url + '/' + node.id;
    console.log(url, method, data);
    $.ajax(url, {
        data: JSON.stringify(data),
        // dataType: "json", // this is the RETURN data
        contentType: contentTypes[method],
        method: method
    }).done( (data) =>  {
        if (callback) {
            callback(data);
        }
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



