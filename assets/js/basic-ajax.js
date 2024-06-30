const $ = import('jquery');
import('jstree');
import('jquery-confirm');

import FileManagerApp from './FileManagerApp.js'
// global.$ = $; // hack if you need a global $

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

const swal = require('sweetalert');
import LocationManagerApp from './LocationManagerApp.js';

const locationManager = new LocationManagerApp($('.js-demo'));

locationManager.render();

// require('./LocationManagerApp');
// import ExclamFunction from './tutorial'; <-- use import when possible!
// console.log('hello' + ExclamFunction(3))
// let manager = new LocationManager($('#location_manager', $, Routing));

function demo_create() {
        var ref = $('#jstree_demo').jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        sel = sel[0];
        sel = ref.create_node(sel, {"type":"file"});
        if(sel) {
            ref.edit(sel);
        }
    }

function demo_rename() {
    var ref = $('#jstree_demo').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    sel = sel[0];
    ref.edit(sel);
}

function demo_save() {

    let json = $('#jstree_demo').jstree().get_json(null, {flat: true});
    console.log(json);

    let simplifedJson = json.map( (node) => {
        return (({ id, text, parent }) => ({ id, text, parent }))(node);
    });

    let saveUrl = $('#config').data('saveUrl');
    console.log(saveUrl, simplifedJson);
    $.getJSON(saveUrl, {'json': simplifedJson}, function(data) {
        console.log(data);
    });

    let jsonString = JSON.stringify(simplifedJson, null, 4);
    console.log(jsonString);
    $('#jstree_event_log').html(jsonString);

}

function confirm_delete()
{
    return $.confirm({
        title: 'Confirm!',
        content: 'Simple confirm!',
        buttons: {
            confirm: function () {
                console.log('delete it!');
                return true;
                $.alert('Confirmed!');
            },
            cancel: function () {
                console.log('Leave it be.');
                return false;
                $.alert('Canceled!');
            },
            somethingElse: {
                text: 'Something else',
                btnClass: 'btn-blue',
                keys: ['enter', 'shift'],
                action: function(){
                    $.alert('Something else?');
                }
            }
        }
    });
}
function demo_delete() {
    var ref = $('#jstree_demo').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    ref.delete_node(sel);
}

// start of code

function main() {
    var to = false;
    $('#demo_q').keyup(function () {
        if (to) {
            clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $('#demo_q').val();
            $('#jstree_demo').jstree(true).search(v);
        }, 250);
    });

    let url = $('#config').data('url');
    let apiUrl = $('#config').data('apiUrl');
    console.log(url, apiUrl);


    $('#js_save_button').click(function () {
        demo_save();
    });
    $('.js-create').click(demo_create);
    $('.js-delete').click(demo_delete);
    $('.js-rename').click(demo_rename);


}
