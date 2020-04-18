const $ = require('jquery');
require('jstree');
require('jquery-confirm');


// global.$ = $; // hack if you need a global $

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

const swal = require('sweetalert');

import {LocationManagerApp} from './LocationManagerApp';

// pass in the jQuery element
const locationManager = new LocationManagerApp(
    $('#location_manager'), {
        url: Routing.generate('api_locations_get_collection', {_format: 'json'}),
        converters:
            {
                "text json": function (data) {
                    return JSON.parse(data).map( x => {
                        return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                    });
                }
            },

    }, {
        // map: { parent: 'parentId', text: 'name' }
        changed: (data) => {}
});


locationManager.render(); // first time

/*
let $element = $('#demo');
let idx = 0;
$element.jstree({core: {data: ['Root Node 1']}})
    .on('changed.jstree', (e, data) => {
        idx++;
        console.log('changed.jstree ' + idx);
        console.log(data);
    })
    .on('ready.jstree', (e, data) => {
        idx++;
        console.log('ready.jstree ' + idx);
        console.log(data);
    });

$element.jstree(true).settings.core.data = ['New Data'];
$element.jstree(true).refresh();
*/
