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
        'version' => '4.0.0',
    ],
    '@tacman1123/jstree-esm/dist/themes/default/style.css' => [
        'version' => '4.1.1',
        'type' => 'css',
    ],
    '@tacman1123/jstree-esm/jquery-plugin' => [
        'version' => '4.1.1',
    ],
    '@tacman1123/jstree-esm/module' => [
        'version' => '4.1.1',
    ],
    '@tacman1123/jstree-esm/dist/themes/modern/style.css' => [
        'version' => '4.1.1',
        'type' => 'css',
    ],
    '@tacman1123/jstree-esm' => [
        'version' => '4.1.1',
    ],
];
