import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['content', 'title'];
    static values = {
        sourcePath: { type: String, default: '' },
    };

    connect() {
        this.onTreeChanged = this.onTreeChanged.bind(this);
        window.addEventListener('apitree_changed', this.onTreeChanged);
    }

    disconnect() {
        window.removeEventListener('apitree_changed', this.onTreeChanged);
    }

    async onTreeChanged(event) {
        const payload = event.detail || {};
        const node = payload.data || payload.hydra || payload.node?.data || null;
        if (!node) {
            return;
        }

        const path = node.path || payload.node?.data?.path;
        if (!path) {
            return;
        }

        this.titleTarget.textContent = path;

        const nodeType = node.type || payload.node?.type;
        const isDirectory = node.isDir === true || nodeType === 'dir';
        if (isDirectory) {
            this.contentTarget.textContent = `${path} is a directory.`;
            return;
        }

        if (!this.sourcePathValue) {
            this.contentTarget.textContent = 'No sourcePath configured.';
            return;
        }

        const url = `${this.sourcePathValue}?path=${encodeURIComponent(path)}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`${response.status} ${response.statusText}`);
            }
            this.contentTarget.textContent = await response.text();
        } catch (error) {
            this.contentTarget.textContent = `Failed to load ${path}: ${error}`;
        }
    }
}
