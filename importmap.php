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
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'jquery' => [
        'version' => '4.0.0',
    ],
    '@tacman1123/jstree-esm' => [
        'path' => '@tacman1123/jstree-esm-local/jstree.esm.mjs',
    ],
    '@tacman1123/jstree-esm/module' => [
        'path' => '@tacman1123/jstree-esm-local/jstree.module.mjs',
    ],
    '@tacman1123/jstree-esm/jquery-plugin' => [
        'path' => '@tacman1123/jstree-esm-local/dist/jstree.js',
    ],
    '@tacman1123/jstree-esm/dist/themes/default/style.css' => [
        'path' => '@tacman1123/jstree-esm-local/dist/themes/default/style.css',
        'type' => 'css',
    ],
];
