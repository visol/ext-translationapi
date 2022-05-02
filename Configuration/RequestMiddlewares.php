<?php

return [
    'frontend' => [
        'visol/cms-frontend/route' => [
            'target' => \Sinso\Translationapi\Middleware\ExtbaseApiMiddleware::class,
            'before' => ['typo3/cms-frontend/base-redirect-resolver'],
        ],
        'visol/cms-frontend/translation-api' => [
            'target' => \Sinso\Translationapi\Middleware\TranslationApiMiddleware::class,
            'before' => ['visol/cms-frontend/route'],
        ],
    ],
];
