const $ = require('jquery');
require('jstree');
require('jquery-confirm');

// global.$ = $; // hack if you need a global $

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
const swal = require('sweetalert');

import {LocationManagerApp} from './LocationManagerApp';

const locationManager = new LocationManagerApp($('#location_manager'));

