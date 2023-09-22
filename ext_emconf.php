<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Replacer',
    'description' => 'Replaces string patterns from the page. You can use it to replace URLs for Content Delivery Network (CDN).',
    'category' => 'fe',
    'author' => 'Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '3.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.30-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
