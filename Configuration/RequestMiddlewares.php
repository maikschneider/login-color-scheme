<?php

declare(strict_types=1);

use MaikSchneider\LoginColorScheme\Middleware\LoginColorSchemeStyles;

return [
    'backend' => [
        'maikschneider/login-color-scheme/styles' => [
            'target' => LoginColorSchemeStyles::class,
            'after' => [
                'typo3/cms-backend/backend-routing',
            ],
            'before' => [
                'typo3/cms-backend/authentication',
            ],
        ],
    ],
];
