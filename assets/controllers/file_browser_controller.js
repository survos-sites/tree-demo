import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['content', 'title']
    static values = {
        sourcePath: {type: String, default: ''},
    }

    connect() {
        super.connect();
        console.log(this.sourcePathValue);

        window.addEventListener('jstree', ev => {

            let data = ev.detail.data;
            this.titleTarget.innerHTML = data.path;

            if (data.type == 'dir') {
                this.contentTarget.innerHTML = data.path + ' is a directory';
                return;
            }

            let url = this.sourcePathValue + '?path=' + data.path;

            console.log('Received ' + ev.type + ' in file_browser_controller ', ev);
            console.warn(ev.type, data.path, data.jstree);


            fetch(url)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not OK');
                    }
                    return response.text();
                })
                .then((response) => {
                    this.contentTarget.innerHTML = 'content here. from ' + this.sourcePathValue + data.path;
                    this.contentTarget.innerHTML = response;
                })
                .catch((error) => {
                    console.error('There has been a problem with your fetch operation:', error);
                });


        });

    }
}
