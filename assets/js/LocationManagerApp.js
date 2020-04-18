const $ = require('jquery');
require('jstree'); // add jstree to jquery

// may want to see https://www.manning.com/books/extending-jquery



export class LocationManagerApp
{
    constructor($element, data) {
        console.log(data);
        this.$element = $element;
        this.addListeners();
        this.jstree = this.$element.jstree(
            {
                core: {data: data },
                plugins: ["contextmenu", "dnd", "search", "state", "types", "wholerow"]

            });

        this.jstree = $element.jstree(true);
        console.log(this.$element, this.jstree);
        this.render();
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
    }

    onReady(e, data) {
        console.warn('ready.jstree fired.');
    }

    onChanged(e, data) {
        const {action, node, selected, instance} = data;
        console.log(e.type, action, node, selected.join(','), instance);
        var i, j, r = [], ids = [];
        for (i = 0, j = selected.length; i < j; i++) {
            let node = instance.get_node(selected[i]);
            console.log(i, node, node.data);
            r.push(node.text);
            console.log(r);
            // ids.push(node.data.databaseId);
        }
    }

    render() {

        // this.$element.jstree(true).settings.core.data = ['New Data'];

        this.$element.jstree(true).refresh();
        return;

        console.log('calling render()');
        this.$element.jstree({ 'core' : {
                'data' : [
                    'Simple root node',
                    {
                        'text' : 'Root node 2',
                        'state' : {
                            'opened' : true,
                            'selected' : true
                        },
                        'children' : [
                            { 'text' : 'Child 1' },
                            'Child 2'
                        ]
                    }
                ]
            } });
        return;
        // $('#jstree_demo').html('loading tree.');

        let apiUrlBase = $element.data('api-base');
        this.$element = $element;
        this.url = apiUrlBase;
        console.log('api base: ' + this.url);


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
                        'url' : (node) => {
                            console.log('data.url: calling ' + this.url);
                            console.log(node);

                            // @todo: add params to node
                            return this.url;
                        },
                        success: function(data) {
                            // we've received the jsTree formatted data.
                            // console.warn('!!', data);
                        },

                        converters:
                            {
                                "text json": function (data) {
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
                console.warn('ready.jstree');
                // demo_save();
            })
        ;

        return;

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



