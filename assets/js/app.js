const $ = require('jquery');
require('popper.js');
global.jQuery = global.$ = $;

console.log('app.js loading...');

require('admin-lte'); // from yarn add admin-lte, 57k, does not include bootstrap
require('bootstrap');
require('../css/app.scss');

// @todo: add to crud generator
$('button:contains(Save)').addClass('btn-primary');

