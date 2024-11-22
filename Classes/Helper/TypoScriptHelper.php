<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Helper;

use Psr\Http\Message\ServerRequestInterface;

class TypoScriptHelper
{
    /**
     * @param array<int, mixed> $typoScriptConfiguration
     */
    public function hasStdWrapProperties(array $typoScriptConfiguration, int|string $key): bool
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
     * @param array<int, mixed> $typoScriptConfiguration
     * @return array<int, mixed>
     */
    public function getStdWrapProperties(array $typoScriptConfiguration, int|string $key): array
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
     * @param array<int, string> $typoScriptConfiguration
     * @param int|string $key
     * @return array<int, mixed>|string
     */
    public function findValueOrConfiguration(array $typoScriptConfiguration, int|string $key): string|array
    {
        return $typoScriptConfiguration[rtrim((string)$key, '.')]
            ?? $typoScriptConfiguration[rtrim((string)$key, '.') . '.']
            ?? '';
    }

    /**
     * @param array<int, string> $typoScriptConfiguration
     */
    public function isRegExpEnabled(array $typoScriptConfiguration, int|string $key): bool
    {
        $subTypoScriptConfiguration = $typoScriptConfiguration[rtrim((string)$key, '.') . '.'] ?? [];

        return is_array($subTypoScriptConfiguration)
            && array_key_exists('enable_regex', $subTypoScriptConfiguration)
            && (int)$subTypoScriptConfiguration['enable_regex'] === 1;
    }

    /**
     * It will return true, if there is a configuration "10" given for $key "10."
     *
     * @param array<int, string> $typoScriptConfiguration
     */
    public function hasBaseEntry(array $typoScriptConfiguration, int|string $key): bool
    {
        return isset($typoScriptConfiguration[rtrim((string)$key, '.')]);
    }

    /**
     * It will return true, if there is a replacement configuration found return
     *
     * @param array<int, string> $typoScriptConfiguration
     */
    public function hasReplaceEntry(array $typoScriptConfiguration, int|string $key): bool
    {
        // depends on search keys here it will be either 10 or 10. so we need to handle here
        $keyCombinedWithTypoScriptPointer = rtrim((string)$key, '.') . '.';
        return isset($typoScriptConfiguration[rtrim((string)$key, '.')])
            || isset($typoScriptConfiguration[$keyCombinedWithTypoScriptPointer]);
    }

    /**
     * @param array<int, string> $stdWrapConfiguration
     */
    public function applyStdWrapProperties(string $content, array $stdWrapConfiguration, ServerRequestInterface $request): string
    {
        $typoscriptFrontendController = $request->getAttribute('frontend.controller');
        $contentObjectRenderer = $typoscriptFrontendController->cObj;
        if ($contentObjectRenderer === null) {
            return $content;
        }

        return $contentObjectRenderer->stdWrap($content, $stdWrapConfiguration);
    }
}
