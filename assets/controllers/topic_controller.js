import {Controller} from '@hotwired/stimulus';
import { prettyPrintJson } from 'pretty-print-json';
/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulus  Fetch: 'lazy' */
export default class extends Controller {
    static targets = ['results', 'title']
    // ...
    static values = {
        description: {type: String, default: ''},
    }

    connect() {
        super.connect();
        this.titleTarget.innerHTML = "Connecting " + this.identifier;
        this.resultsTarget.innerHTML = "startup: results will go here. " + this.identifier;

        window.addEventListener('apitree:connect', ev => {
            console.error(ev.type);
        });

        // window.addEventListener('jstree', this.receivedEvent);
        this.resultsTarget.innerHTML = "adding listener";
        this.that = this;
        // window.addEventListener('apitree_changed', this.receivedEvent);
        window.addEventListener('apitree_changed', ev => {
            this.resultsTarget.innerHTML = 'helllo?';
            let data = ev.detail;
            console.warn(data.hydra);
            this.resultsTarget.innerHTML = data.hydra.description;
            this.titleTarget.innerHTML = data.hydra.name;

        });
    }

    receivedEvent(ev) {
        this.that.resultsTarget.innerHTML = "adding receivedEvent";
        let data = ev.detail;
        console.log(data.hydra);

        // this.that.resultsTarget.innerHTML = "NEW results will go here. " + this.identifier;
        // this.topicResultsTarget.innerHTML = JSON.stringify(data.hydra);
        // console.log(Object.keys(data));
        // console.log(data.original, data.original.hydra);
    }
}
