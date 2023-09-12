<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Helper;

use JWeiland\Replacer\Configuration\ReplaceConfiguration;
use JWeiland\Replacer\Enumeration\ConfigurationTypeEnumeration;
use JWeiland\Replacer\Traits\GetTypoScriptFrontendControllerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Helper class for content replacement using TSFE
 */
class ReplacerHelper implements LoggerAwareInterface
{
    use GetTypoScriptFrontendControllerTrait;
    use LoggerAwareTrait;

    protected TypoScriptHelper $typoScriptHelper;

    public function __construct(TypoScriptHelper $typoScriptHelper)
    {
        $this->typoScriptHelper = $typoScriptHelper;
    }

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
        $replacerTypoScriptConfiguration = $this->getValueByPath(
            $typoScriptFrontendController->config,
            'config/tx_replacer.'
        );

        foreach ($this->getReplaceConfigurationStorage($replacerTypoScriptConfiguration) as $replaceConfiguration) {
            if ($replaceConfiguration->isUseRegExp()) {
                // replace using a regex as search pattern
                $contentToReplace = preg_replace(
                    $replaceConfiguration->getSearchValue(),
                    $replaceConfiguration->getReplaceValue(),
                    $contentToReplace
                );
            } else {
                // replace using a regular strings as search pattern
                $contentToReplace = str_replace(
                    $replaceConfiguration->getSearchValue(),
                    $replaceConfiguration->getReplaceValue(),
                    $contentToReplace
                );
            }
        }

        return $contentToReplace;
    }

    /**
     * @return \SplObjectStorage|ReplaceConfiguration[]
     */
    protected function getReplaceConfigurationStorage(array $replacerTypoScriptConfiguration): \SplObjectStorage
    {
        $replacerConfigurationStorage = new \SplObjectStorage();

        $searchTypoScriptConfiguration = $this->getConfigurationFor(
            $replacerTypoScriptConfiguration,
            ConfigurationTypeEnumeration::cast('search')
        );

        $replaceTypoScriptConfiguration = $this->getConfigurationFor(
            $replacerTypoScriptConfiguration,
            ConfigurationTypeEnumeration::cast('replace')
        );

        foreach ($searchTypoScriptConfiguration as $key => $valueOrConfiguration) {
            // If configuration (10. -> array) has a base entry (10), this property was already processed. Skip.
            // If there is a search and no replace configuration skip
            if (
                (is_array($valueOrConfiguration)
                    && $this->typoScriptHelper->hasBaseEntry($searchTypoScriptConfiguration, $key))
                ||
                ($this->typoScriptHelper->hasBaseEntry($searchTypoScriptConfiguration, $key)
                    && !$this->typoScriptHelper->hasReplaceEntry($replaceTypoScriptConfiguration, $key))
            ) {
                continue;
            }

            $replaceConfiguration = $this->getFreshReplaceConfiguration();

            // Add regular expression information
            $replaceConfiguration->setUseRegExp(
                $this->typoScriptHelper->isRegExpEnabled($searchTypoScriptConfiguration, $key)
            );

            // Add search value
            $replaceConfiguration->setSearchValue(
                $this->getProcessedValue($valueOrConfiguration, $searchTypoScriptConfiguration, $key)
            );

            // Add replace value
            $replaceConfiguration->setReplaceValue(
                $this->getProcessedValue(
                    $this->typoScriptHelper->findValueOrConfiguration($replaceTypoScriptConfiguration, $key),
                    $replaceTypoScriptConfiguration,
                    $key
                )
            );

            $replacerConfigurationStorage->attach($replaceConfiguration);
        }

        return $replacerConfigurationStorage;
    }

    /**
     * Return value unprocessed, if is string and no stdWrap configuration was found
     * Return value processed, if is string and stdWrap configuration was found
     * Return a new value which was build by just stdWrap configuration
     *
     * @param string|array $valueOrConfiguration
     * @param string|int $key
     */
    protected function getProcessedValue($valueOrConfiguration, array $typoScriptConfiguration, $key): string
    {
        if (is_string($valueOrConfiguration)) {
            if ($this->typoScriptHelper->hasStdWrapProperties($typoScriptConfiguration, $key)) {
                $value = $this->typoScriptHelper->applyStdWrapProperties(
                    $valueOrConfiguration,
                    $this->typoScriptHelper->getStdWrapProperties($typoScriptConfiguration, $key)
                );
            } else {
                $value = $valueOrConfiguration;
            }
        } else {
            $value = $this->typoScriptHelper->applyStdWrapProperties(
                '',
                $valueOrConfiguration
            );
        }

        return $value;
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

    protected function getConfigurationFor(
        array $replacerConfiguration,
        ConfigurationTypeEnumeration $configurationType
    ): array {
        try {
            $typoScriptConfiguration = ArrayUtility::getValueByPath(
                $replacerConfiguration,
                (string)$configurationType
            );

            // We need correct sorting: 10, 10., 20, 30, 30.
            ksort($typoScriptConfiguration, SORT_NATURAL);

            return $typoScriptConfiguration;
        } catch (MissingArrayPathException | \RuntimeException $exception) {
            return [];
        }
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

    protected function getFreshReplaceConfiguration(): ReplaceConfiguration
    {
        return GeneralUtility::makeInstance(ReplaceConfiguration::class);
    }
}
