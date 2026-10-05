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
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],
    'react' => [
        'version' => '19.3.0',
    ],
    'axios' => [
        'version' => '1.20.0',
    ],
    'react-hook-form' => [
        'version' => '7.89.0',
    ],
    '@/utils/api' => [
        'path' => './assets/build/react/utils/api.js',
    ],
    '@/react/controllers/AppointmentForm' => [
        'path' => './assets/build/react/controllers/AppointmentForm.js',
    ],
    '@symfony/ux-react' => [
        'version' => '3.5.1',
    ],
    'react-dom' => [
        'version' => '19.3.0',
    ],
    'react-dom/client' => [
        'version' => '19.3.0',
    ],
    'react/jsx-runtime' => [
        'version' => '19.3.0',
    ],
    'scheduler' => [
        'version' => '0.28.0',
    ],
];
