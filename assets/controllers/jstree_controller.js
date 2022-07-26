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
        console.log('hello from ' + this.identifier);
        // this.element.textContent = msg;
        if (this.hasHtmlTarget) {
            this.html(this.htmlTarget);
        } else {
            console.error('Warning: no HTML target, so not rendered.');
        }
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
