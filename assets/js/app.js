require('@popperjs/core');
require('bootstrap');
require('Hinclude/hinclude');
require('../css/app.scss');

const $ = require('jquery');
global.jQuery = global.$ = $;

require('../../vendor/survos/base-bundle/src/Resources/assets/js/adminlte');

//
// console.log('app.js loading...');
//
// require('admin-lte'); // from yarn add admin-lte, 57k, does not include bootstrap
// require('bootstrap');
// require('../css/app.scss');
//
// // @todo: add to crud generator
// $('button:contains(Save)').addClass('btn-primary');

