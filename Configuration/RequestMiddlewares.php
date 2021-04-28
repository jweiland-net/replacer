<?php
return [
    'frontend' => [
        'jweiland/replacer/replace-content' => [
            'target' => \JWeiland\Replacer\Middleware\ReplaceContent::class,
            'after' => [
                'typo3/cms-frontend/maintenance-mode'
            ]
        ]
    ]
];
