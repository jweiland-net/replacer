<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Helper;

use JWeiland\Replacer\Traits\GetTypoScriptFrontendControllerTrait;

class TypoScriptHelper
{
    use GetTypoScriptFrontendControllerTrait;

    /**
     * @param string|int $key
     */
    public function hasStdWrapProperties(array $typoScriptConfiguration, $key): bool
    {
        $possibleStdWrapConfiguration = $typoScriptConfiguration[rtrim((string)$key, '.') . '.'] ?? [];
        if (is_array($possibleStdWrapConfiguration)) {
            // "enable_regex" is not part of stdWrap properties
            unset($possibleStdWrapConfiguration['enable_regex']);

            return $possibleStdWrapConfiguration !== [];
        }

        return false;
    }

    /**
     * @param string|int $key
     */
    public function getStdWrapProperties(array $typoScriptConfiguration, $key): array
    {
        if ($this->hasStdWrapProperties($typoScriptConfiguration, $key)) {
            $stdWrapConfiguration = $typoScriptConfiguration[rtrim((string)$key, '.') . '.'] ?? [];

            // "enable_regex" is not part of stdWrap properties
            unset($stdWrapConfiguration['enable_regex']);

            return $stdWrapConfiguration;
        }

        return [];
    }

    /**
     * Tries to find an equivalent for "search" in TypoScript "replace" configuration.
     * It can be either "10", but also "10." where "10" has priority.
     * In case of just "10" we will add stdWrap properties automatically
     *
     * @param string|int $key
     * @return string|array
     */
    public function findValueOrConfiguration(array $typoScriptConfiguration, $key)
    {
        return $typoScriptConfiguration[rtrim((string)$key, '.')]
            ?? $typoScriptConfiguration[rtrim((string)$key, '.') . '.']
            ?? '';
    }

    /**
     * @param string|int $key
     */
    public function isRegExpEnabled(array $typoScriptConfiguration, $key): bool
    {
        $subTypoScriptConfiguration = $typoScriptConfiguration[rtrim((string)$key, '.') . '.'] ?? [];

        return is_array($subTypoScriptConfiguration)
            && array_key_exists('enable_regex', $subTypoScriptConfiguration)
            && (int)$subTypoScriptConfiguration['enable_regex'] === 1;
    }

    /**
     * It will return true, if there is a configuration "10" given for $key "10."
     *
     * @param string|int $key
     */
    public function hasBaseEntry(array $typoScriptConfiguration, $key): bool
    {
        return isset($typoScriptConfiguration[rtrim((string)$key, '.')]);
    }

    /**
     * It will return true, if there is a replacement configuration found return
     *
     * @param string|int $key
     */
    public function hasReplaceEntry(array $typoScriptConfiguration, $key): bool
    {
        // depends on search keys here it will be either 10 or 10. so we need to handle here
        $keyCombinedWithTypoScriptPointer = rtrim((string)$key, '.') . '.';
        return isset($typoScriptConfiguration[rtrim((string)$key, '.')])
            || isset($typoScriptConfiguration[$keyCombinedWithTypoScriptPointer]);
    }

    public function applyStdWrapProperties(string $content, array $stdWrapConfiguration): string
    {
        $contentObjectRenderer = $this->getContentObjectRenderer();
        if ($contentObjectRenderer === null) {
            return $content;
        }

        return $contentObjectRenderer->stdWrap($content, $stdWrapConfiguration);
    }
}
