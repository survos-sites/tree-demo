const $ = require('jquery');

require('jstree');

// global.$ = $; // hack if you need a global $

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

const swal = require('sweetalert');

import {LocationManagerApp} from './LocationManagerApp';

let $allLocations = $('#all_locations');
let $buildingLocations = $('#building_locations');
// pass in the jQuery element



// handle one specific building.  Needs filter or specific route.
const buildingManager = new LocationManagerApp(
    $buildingLocations, {
        dataType: 'json', // should set accept header
        url: Routing.generate('api_buildings_get_collection') + '/' + $buildingLocations.data('buildingId'), // seems hackish
        converters:
            {
                "text json": function (data) {
                    return JSON.parse(data).locations.map( x => {
                        return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                    });
                }
            },

    },

    {

        // map: { parent: 'parentId', text: 'name' }
        changed: (data) => {}
});

const allManager = new LocationManagerApp(
    $allLocations, {
        dataType: 'json', // should set accept header
        url: Routing.generate('api_locations_get_collection'),
        converters:
            {
                "text json": function (data) {
                    return JSON.parse(data).map( x => {
                        return { parent: x.parentId ?? '#', id: x.id, text: x.name };
                    });
                }
            },

    },

    {

        // map: { parent: 'parentId', text: 'name' }
        changed: (data) => {}
    });

$('.demo').off('changed.jstree').on('changed.jstree', (e, data) => { console.log(e, 'changed, my handler!!'); });


buildingManager.render(); // first time
