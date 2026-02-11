import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['results', 'title'];

    connect() {
        this.onTreeChanged = this.onTreeChanged.bind(this);
        this.titleTarget.textContent = 'Waiting for tree selection';
        this.resultsTarget.textContent = 'Select a node in a tree to inspect payload data.';
        window.addEventListener('apitree_changed', this.onTreeChanged);
    }

    disconnect() {
        window.removeEventListener('apitree_changed', this.onTreeChanged);
    }

    onTreeChanged(event) {
        const payload = event.detail || {};
        const nodeData = payload.data || payload.hydra || payload.node?.data || null;

        if (!nodeData) {
            this.titleTarget.textContent = 'Node selected';
            this.resultsTarget.textContent = JSON.stringify(payload, null, 2);
            return;
        }

        this.titleTarget.textContent = nodeData.name || nodeData.title || nodeData.path || 'Node selected';
        this.resultsTarget.textContent = JSON.stringify(nodeData, null, 2);
    }
}
