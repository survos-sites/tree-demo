// during dev, from project_dir run
// ln -s ~/survos/bundles/tree-bundle/assets/src/controllers/sandbox_api_controller.js assets/controllers/sandbox_api_controller.js
import {Controller} from "@hotwired/stimulus";
import {default as axios} from "axios";
// const $ = window.jQuery; // require('jquery');
// require('jstree');
import jQuery from 'jquery';
import 'jstree';

// import $ from 'jquery'; // for jstree
// let $ = global.$;

const contentTypes = {
    'PATCH': 'application/merge-patch+json',
    'POST': 'application/json'
};

export default class extends Controller {
    static targets = ['ajax', 'message'];
    static values = {
        apiCall: {type: String, default: ''},
        labelField: {type: String, default: 'name'}, // not yet working, maybe not necessary
        filter: {type: String, default: '{}'}
    }

    connect() {
        super.connect(); //
        console.warn("LOCAL to jstree-demo!! connecting "  + this.identifier);

        const payload = {apiCall: this.apiCallValue, options: []};
        // if (Array.isArray(payload.options) && 0 === payload.options.length) {
        //     payload.options = {};
        // }

        this._dispatchEvent('apitree:pre-connect', { options: payload });


        this.filter = JSON.parse(this.filterValue);

        this.url = this.apiCallValue;
        this.notify('hola from ' + this.identifier + ' ' + this.url);
        this.treeElement = this.ajaxTarget;
        this.$element = $(this.treeElement); // hackish
        const connectPayload = this.$element;
        this.configure(this.$element);
        this._dispatchEvent('apitree:connect', { msg: "before adding listeners" });
        this.addListeners(this.$element);

        this.render();
    }

    notify(message) {
        console.log(message);
        this.messageTarget.innerHTML = message;
    }

    configure($element) {
        this.tree = $element
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
                            case 'move_node':
                            case 'edit':
                                // @todo: check that we're logged in and have permission?  Or ...?
                                return true;
                            default:
                                console.error('unhandled check_callback: ' + operation);
                                return true;
                        }
                    },
                    'force_text': true,
                    "themes": {"stripes": true},
                    'simple_data': [
                        'Simple root node',
                        {
                            'text': 'Root node 2',
                            'state': {
                                'opened': true,
                                'selected': true
                            },
                            'children': [
                                {'text': 'Child 1'},
                                'Child 2'
                            ]
                        },
                    ],
                    'data': {
                        url: (node) => {
                            console.log('data.url: calling ' + this.url);

                            // @todo: add params to node
                            return this.url; // + '.json'; // or set this in api_platform routes?
                        },
                        success: function (data) {
                            // we've received the jsTree formatted data.
                            // console.warn('!!', data);
                        },

                        // api_platform calls return JSON in a certain format, but js-tree needs it in another.
                        converters:
                            {
                                "text json": function (dataString) {
                                    let data = JSON.parse(dataString);
                                    let mappedData = data['hydra:member'].map(x => {
                                        // let mappedData = data.map( x => {
                                        // @todo: make 'name' configurable!
                                        return {parent: x.parentId ?? '#',
                                            my_extra: 'extra',
                                            my_extra_array: {x: 'y'},
                                            my_extra_array_json: JSON.stringify({a: 'b'}),
                                            hydra: x,
                                            data: x, id: x.id, text: x.name};
                                    });
                                    console.log(mappedData[0].data);
                                    return mappedData;

                                }
                            },
                        // dataType: 'json', // let it come back as json-ld
                        // this is the data SENT to the server
                        'data': (node) => {

                            return {...this.filter, ...{'fields': ['parentId', 'name']}};
                            // return { id : node.id }; e.g. send # if root node.  Maybe send buildingId?
                        }
                    }
                },
                "types": {
                    "#": {"max_children": 1, "max_depth": 4, "valid_children": ["root"]},
                    "root": {"icon": "/static/3.3.9/assets/images/tree_icon.png", "valid_children": ["default"]},
                    "default": {"valid_children": ["default", "file"]},
                    "file": {"icon": "glyphicon glyphicon-file", "valid_children": []}
                },
                // "plugins" : [ "search", "state", "types", "wholerow" ]
                "plugins": ["contextmenu", "dnd", "search", "state", "types", "wholerow"]
            })
            .on('xxready.jstree', (e, data) => {
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
            this.$element.jstree('open_all');
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

    // onChanged(event, data) {
    //     var i, j, r = [];
    //     let instance = data.instance;
    //     for (i = 0, j = data.selected.length; i < j; i++) {
    //         let node = instance.get_node(data.selected[i]);
    //         // r.push(instance.data('path'));
    //         console.log(node);
    //         // instance.jstree().open(); // not sure how to do this.
    //         this._dispatchEvent('apitree_changed', {
    //             hydra: node.data.original.hydra,
    //             data: node.data,
    //             msg: event.type
    //         });
    //
    //     }
    // }

            // window.dispatchEvent(new CustomEvent('apitree.changed', {
            //         detail: {
            //             hydra: node.data.original.hydra,
            //             data: node.data,
            //             msg: event.type}
            //     }
            // ));
            // let jsTreeData = JSON.parse(node.data.jstree);
            // console.warn(jsTreeData, jsTreeData.path);
    //     }
    // }

    addListeners($element) {

        $element
            // .on('changed.jstree', (e, data) => { // triggered when selection changes, can be multiple, data is tree data, not node data
            //     this.$element.jstree('open_all');
            //     console.log(e, data);
            //     // e.trigger('apitree:changed', {detail: {msg: "from trigger"}});
            //     // $( document ).trigger( 'apitree:changed', [ "bim", "baz" ] );
            //     // $( window ).trigger( 'apitree:changed', [ "bim", "baz" ] );
            //
            //     // this.$element.trigger('apitree:changed', {detail: {msg: "from trigger"}});
            //     // this._dispatchEvent('apitree:changed', {msg: "changed", e, data})
            //     // this._dispatchEvent('apitree_changed', {msg: "changed", e, data})
            //     // console.warn('changed.jstree fired');
            // })
            .on('ready.jstree', (e, data) => {
                console.log('ready.jstree', data);
                // the event.type is ready, not ready.jstree
                this._dispatchEvent(e.type + '.jstree', {msg: e.type, e, d: data})
                this.$element.jstree('open_all');
            })
            .on('loaded.jstree', (e, data) => {
                this.$element.jstree('open_all'); // ??
            })
            // listen for updates
            .on('changed.jstree',  (e, data) => { // triggered when selection changes, can be multiple, data is tree data, not node data
                const {action, node, selected, instance} = data;
                // console.log(e.type, action, node, selected.join(','), instance);
                var i, j, r = [], ids = [];
                for (i = 0, j = selected.length; i < j; i++) {
                    let node = instance.get_node(selected[i]);
                    // @todo: handle checkboxes by sending all selected nodes in a single call.
                    this._dispatchEvent('apitree_changed', {
                        msg: "change " + node.text,
                        hydra: node.original.hydra,
                        data: node.original.data, original: node.original
                    })
                    r.push(node.text);
                    ids.push(node.id);
                }
                $('#jstree_event_log').html(data.action + ': ' + r.join(', ') + ' IDS: ' + ids.join(','));
            })
            .on('create_node.jstree', (e, data) => {
                const {node, parent, position} = data;
                let parentNode = data.instance.get_node(parent);
                console.warn(e.type, node, parent, parentNode);
                console.log('new node born of parent ' + parentNode.id + '/' + parentNode.text);

                let text = parentNode.text + '-' + (parentNode.children.length + 1);
                node.text = text;

                // parentId is null, not sure why!
                // console.log(parent);

                // var node = $('#dashboardTree').jstree(true).find('//something');
                this.collectionApiCall(node, 'POST', {
                        ...this.filter, ...{
                            code: node.id,
                            parent: this.url + '/' + parentNode.id,
                            name: text
                        }
                    }
                    , (data) => {
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
            .on('move_node.jstree', (e, data) => {
                // https://www.jstree.com/api/#/?f=move_node.jstree
                const {node, parent, position, old_parent, old_position, is_multi, old_instance, new_instance} = data;
                console.log('moving', node, parent, new_instance);
                this.itemApiCall(node, 'PATCH', {parent: this.url + '/' + parent});

            })
            .on('delete_node.jstree', (e, data) => {
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

    collectionApiCall(node, method, data, callback) {
        // node is the parent node, methods are GET, POST
        console.log(data);
        $.ajax(this.url, {
            data: JSON.stringify(data),
            // dataType: "json", // this is the RETURN data
            contentType: contentTypes[method],
            method: method
        }).done((data) => {
            callback(data);

        }).fail((data) => {
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
        }).done((data) => {
            if (callback) {
                callback(data);
            }
            console.log(data);

        }).fail((data) => {
            console.error(data);
        })
    }

    // @todo: move to ts
    _dispatchEvent(name, payload) {

        // name = 'jstree';
        let ev = new CustomEvent(name, { detail: payload });
        Object.defineProperty(ev, 'target', {writable: false, value: window});
        // let ev = new Event(name, { detail: payload });
        console.log('Dispatching event ' + name + " " + payload.msg);
        // this.element.dispatchEvent(ev);
        window.dispatchEvent(ev);

        $( document ).trigger( name, [ "bim", "baz" ] );
        $( window ).trigger( name, [ "bim", "baz" ] );

        // document.dispatchEvent(ev);
    }



}
