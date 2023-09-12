<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Configuration;

/**
 * Contains the configuration how to replace a search value
 */
class ReplaceConfiguration
{
    protected string $searchValue = '';

    protected string $replaceValue = '';

    protected bool $useRegExp = false;

    public function getSearchValue(): string
    {
        return $this->searchValue;
    }

    public function setSearchValue(string $searchValue): void
    {
        $this->searchValue = $searchValue;
    }

    public function getReplaceValue(): string
    {
        return $this->replaceValue;
    }

    public function setReplaceValue(string $replaceValue): void
    {
        $this->replaceValue = $replaceValue;
    }

    public function isUseRegExp(): bool
    {
        return $this->useRegExp;
    }

    public function setUseRegExp(bool $useRegExp): void
    {
        $this->useRegExp = $useRegExp;
    }
}
