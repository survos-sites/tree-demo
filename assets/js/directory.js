const $ = require('jquery');
require('jstree');
require('jquery-confirm');
const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

const swal = require('sweetalert');

import {FileManagerApp} from './FileManagerApp';

let $directoryDiv = $('#file_manager');
// pass in the jQuery element
let apiUrl = $directoryDiv.data('apiBase');
console.error(apiUrl);


const fileManagerApp = new FileManagerApp(
    $directoryDiv, {
        dataType: 'json', // should set accept header
        url: 'why is this passed?', // apiUrl, // Routing.generate('api_files_get_collection'),
        converters:
            {
                "text json": function (data) {
                    console.log("Got some data");
                    return JSON.parse(data).locations.map( x => {
                        const display = x.name + '(' + x.childCount + ')';
                        return { parent: x.parentId ?? '#', id: x.id, text: display };
                    });
                }
            },

    },

    {

        // map: { parent: 'parentId', text: 'name' }
        changed: (data) => {}
});


// $('.demo').off('changed.jstree').on('changed.jstree', (e, data) => { console.log(e, 'changed, my handler!!'); });


fileManagerApp.render(); // first time
