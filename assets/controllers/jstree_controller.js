// tree-bundle/assets/src/controllers/tree_controller.js

import {Controller} from "@hotwired/stimulus";
import jQuery from 'jquery';
import 'jstree';

export default class extends Controller {

    static values = {
        msg: {type: String, default: '/bill'},
        plugins: {type: Array, default: ['checkbox', 'theme', "types", 'sort']},
        types: {type: Object, default: {}}
        // interval: { type: Number, default: 5 },
        // clicked: { type: Boolean, default: false },
    }

    static targets = ["html", "ajax"]

    connect() {

        let msg = 'Hello from controller ' + this.identifier;
        console.error(msg);
        // this.html(this.element);
        // // this.element.textContent = msg;
        // if (this.hasHtmlTarget) {
        //     this.html(this.htmlTarget);
        // }
        console.log('hello from ' + this.identifier);
        // this.element.textContent = msg;
        if (this.hasHtmlTarget) {
            this.html(this.htmlTarget);
        } else {
            console.error('Warning: no HTML target, so not rendered.');
        }

        // window.addEventListener('jstree', (ev, data) => {
        //     console.log("Event received", ev.type);
        // })
    }

    search(event) {
        // this.$element.jstree(true).search(event.currentTarget.value, {
        //     show_only_matches: true,
        //     show_only_matches_children: true
        // });
        this.$element.jstree(true).search(event.currentTarget.value, false, true, true);
    }

    addListeners() {
        console.log('adding listeners. ');
        this.$element
            .on('changed.jstree', this.onChanged) // triggered when selection changes, can be multiple, data is tree data, not node data
            .on('ready.jstree', (e, data) => {
                console.warn('ready.jstree fired, so opening_all');
                // $element.jstree('open_all');
            })
    }

    onChanged(event, data) {
        var i, j, r = [];
        let instance = data.instance;
        for(i = 0, j = data.selected.length; i < j; i++) {
            let node = instance.get_node(data.selected[i]);
            // r.push(instance.data('path'));
            console.log(node.data.path);
            window.dispatchEvent(new CustomEvent('jstree', {
                detail: {
                    data: node.data,
                    msg: event.type}
                }
                ));
            // let jsTreeData = JSON.parse(node.data.jstree);
            // console.warn(jsTreeData, jsTreeData.path);
        }
        // console.log(r);
        // console.log($(data).dataset);
    }


    html(el) {
        // jQuery.tree.reference(el );
        this.$element = $(el);
        // this.$element = jQuery.jstree.reference(el);
        console.error(this.$element);
        console.error(this.pluginsValue);
        this.$element.jstree(
            {
                "plugins": this.pluginsValue,
                "types": this.typesValue
            }
        );

        this.addListeners();

    }
}
