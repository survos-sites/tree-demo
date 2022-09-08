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
            if (data.type == 'dir') {
                return;
            }

            this.titleTarget.innerHTML = data.path;
            let url = this.sourcePathValue + '?path=' + data.path;

            console.log('Received in file_browser ', ev);
            console.warn(ev.type, data.path, data.jstree);


            fetch(url)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not OK');
                    }
                    return response.text();
                })
                .then((response) => {
                    console.log(response);

                    this.contentTarget.innerHTML = 'content here. from ' + this.sourcePathValue + data.path;
                    this.contentTarget.innerHTML = response;
                })
                .catch((error) => {
                    console.error('There has been a problem with your fetch operation:', error);
                });


        });

    }
}
