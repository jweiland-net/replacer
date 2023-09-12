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

    public function hasStdWrapProperties(array $typoScriptConfiguration, int $key): bool
    {
        $possibleStdWrapConfiguration = $typoScriptConfiguration[$key . '.'] ?? [];
        if (is_array($possibleStdWrapConfiguration)) {
            // "enable_regex" is not part of stdWrap properties
            unset($possibleStdWrapConfiguration['enable_regex']);

            return $possibleStdWrapConfiguration !== [];
        }

        return false;
    }

    public function getStdWrapProperties(array $typoScriptConfiguration, int $key): array
    {
        if ($this->hasStdWrapProperties($typoScriptConfiguration, $key)) {
            $stdWrapConfiguration = $typoScriptConfiguration[$key . '.'] ?? [];

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
        return $typoScriptConfiguration[$key] ?? $typoScriptConfiguration[$key . '.'] ?? '';
    }

    public function isRegExpEnabled(array $typoScriptConfiguration, int $key): bool
    {
        $subTypoScriptConfiguration = $typoScriptConfiguration[$key . '.'] ?? [];

        return is_array($subTypoScriptConfiguration)
            && array_key_exists('enable_regex', $subTypoScriptConfiguration)
            && (int)$subTypoScriptConfiguration['enable_regex'] === 1;
    }

    /**
     * It will return true, if there is a configuration "10" given for $key "10."
     */
    public function hasBaseEntry(array $typoScriptConfiguration, string $key): bool
    {
        return isset($typoScriptConfiguration[rtrim($key, '.')]);
    }

    public function applyStdWrapProperties(string $content, array $stdWrapConfiguration): string
    {
        return $this->getContentObjectRenderer()->stdWrap($content, $stdWrapConfiguration);
    }
}
