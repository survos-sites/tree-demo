<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@survos/api-grid-bundle' => [
        'path' => '@survos/api-grid/package.json',
        'type' => 'json',
    ],
    '@tacman1123/twig-browser/testing/detailContextHeader' => [
        'version' => '0.2.2',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    '@tacman1123/jstree-esm/dist/themes/default/style.css' => [
        'version' => '4.1.3',
        'type' => 'css',
    ],
    '@tacman1123/jstree-esm/jquery-plugin' => [
        'version' => '4.1.3',
    ],
    '@tacman1123/jstree-esm/module' => [
        'version' => '4.1.3',
    ],
    '@tacman1123/jstree-esm/dist/themes/modern/style.css' => [
        'version' => '4.1.3',
        'type' => 'css',
    ],
    '@tacman1123/jstree-esm' => [
        'version' => '4.1.3',
    ],
    '@tabler/core' => [
        'version' => '1.4.0',
    ],
    '@tabler/core/dist/css/tabler.min.css' => [
        'version' => '1.4.0',
        'type' => 'css',
    ],
    'axios' => [
        'version' => '1.13.6',
    ],
    'fos-routing' => [
        'version' => '0.0.6',
    ],
    'perfect-scrollbar' => [
        'version' => '1.5.6',
    ],
    'datatables.net-plugins/i18n/en-GB.mjs' => [
        'version' => '2.3.6',
    ],
    'datatables.net-plugins/i18n/es-ES.mjs' => [
        'version' => '2.3.6',
    ],
    'datatables.net-plugins/i18n/de-DE.mjs' => [
        'version' => '2.3.6',
    ],
    'datatables.net-bs5' => [
        'version' => '2.1.6',
    ],
    'datatables.net-buttons-bs5' => [
        'version' => '3.2.6',
    ],
    'datatables.net-responsive-bs5' => [
        'version' => '3.0.8',
    ],
    'datatables.net-scroller-bs5' => [
        'version' => '2.4.3',
    ],
    'datatables.net-searchpanes-bs5' => [
        'version' => '2.3.5',
    ],
    'datatables.net-searchbuilder-bs5' => [
        'version' => '1.8.4',
    ],
    'datatables.net-select-bs5' => [
        'version' => '2.1.0',
    ],
    'datatables.net-columncontrol' => [
        'version' => '1.2.1',
    ],
    'datatables.net-columncontrol-bs5' => [
        'version' => '1.2.1',
    ],
    'datatables.net' => [
        'version' => '2.1.6',
    ],
    'datatables.net-buttons' => [
        'version' => '3.2.6',
    ],
    'datatables.net-responsive' => [
        'version' => '3.0.8',
    ],
    'datatables.net-scroller' => [
        'version' => '2.4.3',
    ],
    'datatables.net-searchpanes' => [
        'version' => '2.3.5',
    ],
    'datatables.net-searchbuilder' => [
        'version' => '1.8.4',
    ],
    'datatables.net-select' => [
        'version' => '2.1.0',
    ],
    'perfect-scrollbar/css/perfect-scrollbar.min.css' => [
        'version' => '1.5.6',
        'type' => 'css',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '2.1.6',
        'type' => 'css',
    ],
    'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css' => [
        'version' => '3.2.6',
        'type' => 'css',
    ],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css' => [
        'version' => '3.0.8',
        'type' => 'css',
    ],
    'datatables.net-scroller-bs5/css/scroller.bootstrap5.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'datatables.net-searchpanes-bs5/css/searchPanes.bootstrap5.min.css' => [
        'version' => '2.3.5',
        'type' => 'css',
    ],
    'datatables.net-searchbuilder-bs5/css/searchBuilder.bootstrap5.min.css' => [
        'version' => '1.8.4',
        'type' => 'css',
    ],
    'datatables.net-select-bs5/css/select.bootstrap5.min.css' => [
        'version' => '2.1.0',
        'type' => 'css',
    ],
    'datatables.net-columncontrol-bs5/css/columnControl.bootstrap5.min.css' => [
        'version' => '1.2.1',
        'type' => 'css',
    ],
    '@tacman1123/twig-browser' => [
        'version' => '0.4.12',
    ],
    '@tacman1123/twig-browser/adapters/symfony' => [
        'version' => '0.4.12',
    ],
    'locutus/php/strings/sprintf' => [
        'version' => '3.0.9',
    ],
    'locutus/php/strings/vsprintf' => [
        'version' => '3.0.9',
    ],
    'locutus/php/math/round' => [
        'version' => '3.0.9',
    ],
    'locutus/php/math/max' => [
        'version' => '3.0.9',
    ],
    'locutus/php/math/min' => [
        'version' => '3.0.9',
    ],
    'locutus/php/strings/strip_tags' => [
        'version' => '3.0.9',
    ],
    'locutus/php/datetime/strtotime' => [
        'version' => '3.0.9',
    ],
    'locutus/php/datetime/date' => [
        'version' => '3.0.9',
    ],
    'locutus/php/var/boolval' => [
        'version' => '3.0.9',
    ],
    'dexie' => [
        'version' => '4.3.0',
    ],
    '@tacman1123/twig-browser/src/compat/compileTwigBlocks.js' => [
        'version' => '0.4.12',
    ],
];
