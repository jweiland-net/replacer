<?php

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Replacer\Middleware\ReplaceContent;

return [
    'frontend' => [
        'jweiland/replacer/replace-content' => [
            'target' => ReplaceContent::class,
            'after' => [
                'typo3/cms-frontend/maintenance-mode',
            ],
        ],
    ],
];
