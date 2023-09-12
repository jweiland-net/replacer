<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Contains the TypoScript configuration type like "search" or "replace"
 */
final class ConfigurationTypeEnumeration extends Enumeration
{
    public const __default = self::TYPE_SEARCH;
    public const TYPE_SEARCH = 'search';
    public const TYPE_REPLACE = 'replace';

    /**
     * We need to add a trailing dot to find the expected configuration in TypoScript
     */
    public function __toString()
    {
        return $this->value . '.';
    }
}
