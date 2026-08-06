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
 *
 * @return array<string, array{    // Import name as key, description of the imported file as value
 *     path: string,               // Logical, relative or absolute path to the file
 *     type?: 'js'|'css'|'json',   // Type of the file, defaults to 'js'
 *     entrypoint?: bool,          // Whether the file is an entrypoint, for 'js' only
 * }|array{
 *     version: string,            // Version of the remote package
 *     package_specifier?: string, // Remote "package-name/path" specifier, defaults to the import name
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    '@survos/js-twig/generated/fos_routes.js' => ['path' => './var/js_twig_bundle/generated/fos_routes.js'],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
    '@tacman1123/twig-browser/testing/detailContextHeader' => ['version' => '0.2.2'],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    '@popperjs/core' => ['version' => '2.11.8'],
    'jquery' => ['version' => '4.0.0'],
    '@tacman1123/jstree-esm/dist/themes/default/style.css' => ['version' => '4.1.3', 'type' => 'css'],
    '@tacman1123/jstree-esm/jquery-plugin' => ['version' => '4.1.3'],
    '@tacman1123/jstree-esm/module' => ['version' => '4.1.3'],
    '@tacman1123/jstree-esm/dist/themes/modern/style.css' => ['version' => '4.1.3', 'type' => 'css'],
    '@tacman1123/jstree-esm' => ['version' => '4.1.3'],
    '@tabler/core' => ['path' => './vendor-patched/@tabler/core/tabler.esm.js'],
    'axios' => ['version' => '1.13.6'],
    'fos-routing' => ['version' => '0.0.6'],
    'perfect-scrollbar' => ['version' => '1.5.6'],
    'datatables.net-plugins/i18n/en-GB.mjs' => ['version' => '2.3.6'],
    'datatables.net-plugins/i18n/es-ES.mjs' => ['version' => '2.3.6'],
    'datatables.net-plugins/i18n/de-DE.mjs' => ['version' => '2.3.6'],
    'datatables.net-bs5' => ['version' => '3.0.0-beta.2'],
    'datatables.net-buttons-bs5' => ['version' => '4.0.0-beta.1'],
    'datatables.net-responsive-bs5' => ['version' => '4.0.0-beta.1'],
    'datatables.net-scroller-bs5' => ['version' => '3.0.0-beta.1'],
    'datatables.net-searchbuilder-bs5' => ['version' => '2.0.0-beta.1'],
    'datatables.net-select-bs5' => ['version' => '4.0.0-beta.1'],
    'datatables.net-columncontrol' => ['version' => '2.0.0-beta.1'],
    'datatables.net-columncontrol-bs5' => ['version' => '2.0.0-beta.1'],
    'datatables.net' => ['version' => '3.0.0-beta.2'],
    'datatables.net-buttons' => ['version' => '4.0.0-beta.1'],
    'datatables.net-responsive' => ['version' => '4.0.0-beta.1'],
    'datatables.net-scroller' => ['version' => '3.0.0-beta.1'],
    'datatables.net-searchbuilder' => ['version' => '2.0.0-beta.1'],
    'datatables.net-select' => ['version' => '4.0.0-beta.1'],
    'datatables.net-bs5/css/dataTables.bootstrap5.css' => ['version' => '3.0.0-beta.2', 'type' => 'css'],
    'datatables.net-buttons-bs5/css/buttons.bootstrap5.css' => ['version' => '4.0.0-beta.1', 'type' => 'css'],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.css' => ['version' => '4.0.0-beta.1', 'type' => 'css'],
    'datatables.net-searchbuilder-bs5/css/searchBuilder.bootstrap5.css' => ['version' => '2.0.0-beta.1', 'type' => 'css'],
    'datatables.net-select-bs5/css/select.bootstrap5.css' => ['version' => '4.0.0-beta.1', 'type' => 'css'],
    'datatables.net-columncontrol-bs5/css/columnControl.bootstrap5.css' => ['version' => '2.0.0-beta.1', 'type' => 'css'],
    '@tacman1123/twig-browser' => ['version' => '1.0.0'],
    '@tacman1123/twig-browser/adapters/symfony' => ['version' => '1.0.0'],
    'locutus/php/strings/sprintf' => ['version' => '3.0.9'],
    'locutus/php/strings/vsprintf' => ['version' => '3.0.9'],
    'locutus/php/math/round' => ['version' => '3.0.9'],
    'locutus/php/math/max' => ['version' => '3.0.9'],
    'locutus/php/math/min' => ['version' => '3.0.9'],
    'locutus/php/strings/strip_tags' => ['version' => '3.0.9'],
    'locutus/php/datetime/strtotime' => ['version' => '3.0.9'],
    'locutus/php/datetime/date' => ['version' => '3.0.9'],
    'locutus/php/var/boolval' => ['version' => '3.0.9'],
    'dexie' => ['version' => '4.3.0'],
    '@tabler/core/dist/css/tabler.min.css' => ['version' => '1.4.0', 'type' => 'css'],
    'perfect-scrollbar/css/perfect-scrollbar.min.css' => ['version' => '1.5.6', 'type' => 'css'],
    '@tacman1123/twig-browser/src/compat/compileTwigBlocks.js' => ['version' => '1.0.0'],
    'stimulus-attributes' => ['version' => '1.0.2'],
    'escape-html' => ['version' => '1.0.3'],
    '@survos/api-grid-bundle' => ['path' => '@survos/api-grid/package.json', 'type' => 'json'],
    'marked' => ['version' => '18.0.9'],
    'flag-icons/css/flag-icons.min.css' => ['version' => '7.5.0', 'type' => 'css'],
    '@floating-ui/dom' => ['version' => '1.8.0'],
    '@floating-ui/core' => ['version' => '1.8.0'],
    '@floating-ui/utils' => ['version' => '0.2.12'],
    '@floating-ui/utils/dom' => ['version' => '0.2.12'],
];
