import { Controller } from 'stimulus';
import 'jstree';

export default class extends Controller {

    static values = {
        url: { type: String, default: '/bill' },
        interval: { type: Number, default: 5 },
        clicked: Boolean
    }

    static targets = [ "html", "ajax" ]

    connect() {
        this.element.textContent = 'Hello from ' + this.identifier;
        if (this.hasHtmlTarget) {
            this.html(htmlTarget);
        }
    }

    html(el) {
        $(el).jstree(
            {
                "plugins": ['checkbox', 'theme', "html_data", "types"]
            }
        );
    }
}
