import {Controller} from "@hotwired/stimulus"
import jQuery from 'jquery';
import 'jstree';

export default class extends Controller {

    static values = {
        // url: { type: String, default: '/bill' },
        // interval: { type: Number, default: 5 },
        // clicked: { type: Boolean, default: false },
    }

    static targets = [ "html" ]

    connect() {

        let msg = 'Hello from controller ' + this.identifier;
        console.error(msg);
        // this.html(this.element);
        // // this.element.textContent = msg;
        // if (this.hasHtmlTarget) {
        //     this.html(this.htmlTarget);
        // }
    }

    html(el) {
        // jQuery.jstree(el,
            console.assert(jQuery().jstree, 'jstree is not loaded.');
            // jQuery.tree.reference(el );
        $(el).jstree(
            {
                "plugins": ['checkbox', 'theme', "html_data", "types"]
            }
        );
    }
}
