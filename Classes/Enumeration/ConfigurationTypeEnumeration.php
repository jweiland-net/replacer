<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Enumeration;

/**
 * Contains the TypoScript configuration type like "search" or "replace"
 */
enum ConfigurationTypeEnumeration: string
{
    case SEARCH = 'search';
    case REPLACE = 'replace';

    public function getValueAsFormatted(): string
    {
        return $this->value . '.';
    }
}
