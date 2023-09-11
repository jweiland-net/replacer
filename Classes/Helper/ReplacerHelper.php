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
        $replacerConfig = $this->getValueByPath(
            $typoScriptFrontendController->config,
            'config/tx_replacer.'
        );

        return $this->doStdWrapProcessingAndReplace($replacerConfig, $contentToReplace);
    }

    protected function doStdWrapProcessingAndReplace(array $typoscriptConfigurations, string $contentToReplace): string
    {
        $search = $this->getValueByPath($typoscriptConfigurations, 'search.');
        $replace = [];
        foreach ($this->getValueByPath(
            $typoscriptConfigurations,
            'replace.'
        ) as $typoscriptConfigurationKey => $configurations) {
            $configurationSearchPointer = rtrim((string)$typoscriptConfigurationKey, '.');

            // if the skip is true it means that the configuration is array inside the replacer and we
            // need to go for stdWrap processing also anything in that configuration pointer should be
            // replaced with this processed value.
            if ($this->shouldDoStdWrap($configurations)) {
                $contentForProcessing = $this->getContentForProcessing(
                    $typoscriptConfigurations,
                    $configurationSearchPointer
                );
                $replace[$configurationSearchPointer] = $this->processContent(
                    $contentForProcessing,
                    $configurations
                );
            } else {
                $replace[$typoscriptConfigurationKey] = $configurations;
            }
        }

        // check search and replace configurations are same
        if (count($search) === count($replace)) {
            // check whether the configuration enabled for regular expressions
            if (array_key_exists('enable_regex', $typoscriptConfigurations)
                && (int)$typoscriptConfigurations['enable_regex'] === 1
            ) {
                // replace using a regex as search pattern
                $contentToReplace = preg_replace($search, $replace, $contentToReplace);
            } else {
                // replace using a regular strings as search pattern
                $contentToReplace = str_replace($search, $replace, $contentToReplace);
            }
        } else {
            $this->writeErrorLogEntry('Each search item must have a replace item!', $typoscriptConfigurations);
        }

        return $contentToReplace;
    }

    protected function getContentForProcessing(array $processingConfig, string $configurationSearchPointer): string
    {

        $contentForProcessing = $this->getValueByPath(
            $processingConfig,
            'search./' . $configurationSearchPointer
        );

        if (ArrayUtility::isValidPath($processingConfig, 'replace./' . $configurationSearchPointer)) {
            $replaceContentForProcessing = (string)$this->getValueByPath(
                $processingConfig,
                'replace./' . $configurationSearchPointer
            );
            // if the replace content for processing is `current` then take the value from search array key
            if ($replaceContentForProcessing === 'current') {
                return $contentForProcessing;
            }

            return $replaceContentForProcessing;
        }
        return $contentForProcessing;
    }

    /**
     * @param $configuration
     * @return bool
     */
    protected function shouldDoStdWrap($configuration): bool
    {
        return is_array($configuration);
    }

    protected function processContent(string $content, array $configKey): string
    {
        if (($configKey !== []) && $this->getTypoScriptFrontendController()->cObj instanceof ContentObjectRenderer) {
            return $this->getTypoScriptFrontendController()->cObj->stdWrap($content, $configKey);
        }

        return $content;
    }

    /**
     * @param array $array
     * @param string $path
     * @return array|string
     */
    protected function getValueByPath(array $array, string $path)
    {
        try {
            return ArrayUtility::getValueByPath($array, $path);
        } catch (MissingArrayPathException $missingArrayPathException) {
            return [];
        }
    }

    protected function writeErrorLogEntry(string $message, array $replacerConfig): void
    {
        $this->logger->log(
            LogLevel::ERROR,
            $message,
            $replacerConfig
        );
    }
}
