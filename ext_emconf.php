<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Replacer',
    'description' => 'Replaces string patterns from the page. You can use it to replace URLs for Content Delivery Network (CDN).',
    'category' => 'fe',
    'author' => 'Pascal Rinker',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
