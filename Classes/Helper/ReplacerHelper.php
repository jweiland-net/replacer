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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Helper class for content replacement using TSFE
 */
class ReplacerHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    use GetTypoScriptFrontendControllerTrait;

    /**
     * Search and replace text from $contentToReplace
     * You must set the Search and Replace patterns via TypoScript.
     * usage from TypoScript:
     *   config.tx_replacer {
     *     search {
     *       1="/typo3temp/pics/
     *       2="/fileadmin/
     *     }
     *     replace {
     *       1="http://mycdn.com/i/
     *       2="http://mycdn.com/f/
     *     }
     *   }
     */
    public function replace(string $contentToReplace): string
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        $replacerConfig = $this->getArrayValueByPath(
            $typoScriptFrontendController->config,
            'config/tx_replacer.'
        );

        return $this->doProcessingForReplacerConfig($contentToReplace, $replacerConfig);
    }

    protected function doProcessingForReplacerConfig(string $contentToReplace, array $replacerConfig): string
    {
        $typoscriptConfigurations = $this->getSearchAndReplaceConfigurations($replacerConfig);

        if (is_array($typoscriptConfigurations['search']) && is_array($typoscriptConfigurations['replace'])) {
            // this will do if the typoscript configuration contains stdWrap
            $searchAndReplaceConfigurations = $this->doStandardWrapProcessing(
                $typoscriptConfigurations,
                $replacerConfig
            );
            $search = $this->getArrayValueByPath($searchAndReplaceConfigurations, 'search');
            $replace = $this->getArrayValueByPath($searchAndReplaceConfigurations, 'replace');

            // Only replace if search and replace count are equal
            if (count($search) === count($replace)) {
                if (array_key_exists('enable_regex', $replacerConfig)
                    && (int)$replacerConfig['enable_regex'] === 1
                ) {
                    // replace using a regex as search pattern
                    $contentToReplace = preg_replace($search, $replace, $contentToReplace);
                } else {
                    // replace using a regular strings as search pattern
                    $contentToReplace = str_replace($search, $replace, $contentToReplace);
                }
            } else {
                $this->logger->log(
                    LogLevel::ERROR,
                    'Each search item must have a replace item!',
                    $replacerConfig
                );
            }
        }

        return $contentToReplace;
    }

    protected function doStandardWrapProcessing(array $typoscriptConfigurations, array $replacerConfig): array
    {
        $processedConfigurations = [];
        foreach ($typoscriptConfigurations as $configurationPointer => $config) {
            foreach ($config as $key => $content) {
                if ($this->shouldSkipKey($key)) {
                    continue;
                }

                if (ArrayUtility::isValidPath($replacerConfig, $configurationPointer . './' . $key . '.')) {
                    $configKey = $replacerConfig[$configurationPointer . '.'][$key . '.'];
                    $processedConfigurations[$configurationPointer][] = $this->processContent(
                        $content,
                        $configKey,
                        $this->getTypoScriptFrontendController()
                    );
                } else {
                    $processedConfigurations[$configurationPointer][] = $content;
                }
            }
        }

        return $processedConfigurations;
    }

    protected function shouldSkipKey($key): bool
    {
        return is_string($key) && substr($key, -1) === '.';
    }

    protected function processContent(string $content, array $configKey): string
    {
        if ($configKey && $this->getTypoScriptFrontendController()->cObj instanceof ContentObjectRenderer) {
            return $this->getTypoScriptFrontendController()->cObj->stdWrap($content, $configKey);
        }

        return $content;
    }

    protected function getSearchAndReplaceConfigurations(array $replacerConfig): array
    {
        return [
            'search' => $this->getArrayValueByPath($replacerConfig, 'search.'),
            'replace' => $this->getArrayValueByPath($replacerConfig, 'replace.'),
        ];
    }

    protected function getArrayValueByPath(array $array, $path): array
    {
        try {
            return ArrayUtility::getValueByPath($array, $path);
        } catch (MissingArrayPathException $missingArrayPathException) {
            return [];
        }
    }
}
