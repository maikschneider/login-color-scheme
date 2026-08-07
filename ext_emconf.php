<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Login Color Scheme',
    'description' => 'Color-scheme aware TYPO3 backend login: adds a dark mode variant for the login background image.',
    'category' => 'be',
    'author' => 'Maik Schneider',
    'author_email' => 'schneider.maik@me.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
            'backend' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
