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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class for content replacement using TSFE
 */
class ReplacerHelper
{
    protected TypoScriptHelper $typoScriptHelper;

    protected ServerRequestInterface $request;

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
    public function replace(string $contentToReplace, ServerRequestInterface $request): string
    {
        $typoScriptFrontendController = $request->getAttribute('frontend.controller');
        $replacerTypoScriptConfiguration = $this->getValueByPath(
            $typoScriptFrontendController->config,
            'config/tx_replacer.',
        );

        $replacerStorageConfigurations = $this->getReplaceConfigurationStorage(
            $replacerTypoScriptConfiguration,
            $request
        );

        foreach ($replacerStorageConfigurations as $replaceConfiguration) {
            if ($replaceConfiguration->isUseRegExp()) {
                // replace using a regex as search pattern
                $contentToReplace = (string)preg_replace(
                    $replaceConfiguration->getSearchValue(),
                    $replaceConfiguration->getReplaceValue(),
                    $contentToReplace,
                );
            } else {
                // replace using a regular strings as search pattern
                $contentToReplace = (string)str_replace(
                    $replaceConfiguration->getSearchValue(),
                    $replaceConfiguration->getReplaceValue(),
                    $contentToReplace,
                );
            }
        }

        return $contentToReplace;
    }

    /**
     * @param array<int, mixed> $replacerTypoScriptConfiguration
     * @return \SplObjectStorage<object, mixed>
     */
    protected function getReplaceConfigurationStorage(array $replacerTypoScriptConfiguration, ServerRequestInterface $request): \SplObjectStorage
    {
        $replacerConfigurationStorage = new \SplObjectStorage();

        $searchTypoScriptConfiguration = $this->getConfigurationFor(
            $replacerTypoScriptConfiguration,
            ConfigurationTypeEnumeration::cast('search'),
        );

        $replaceTypoScriptConfiguration = $this->getConfigurationFor(
            $replacerTypoScriptConfiguration,
            ConfigurationTypeEnumeration::cast('replace'),
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
                $this->typoScriptHelper->isRegExpEnabled($searchTypoScriptConfiguration, $key),
            );

            // Add search value
            $replaceConfiguration->setSearchValue(
                $this->getProcessedValue(
                    $valueOrConfiguration,
                    $searchTypoScriptConfiguration,
                    $key,
                    $request,
                ),
            );

            // Add replace value
            $replaceConfiguration->setReplaceValue(
                $this->getProcessedValue(
                    $this->typoScriptHelper->findValueOrConfiguration($replaceTypoScriptConfiguration, $key),
                    $replaceTypoScriptConfiguration,
                    $key,
                    $request,
                ),
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
     * @param array<int, mixed>|string $valueOrConfiguration
     * @param array<int, mixed> $typoScriptConfiguration
     */
    protected function getProcessedValue(
        array|string $valueOrConfiguration,
        array $typoScriptConfiguration,
        int|string $key,
        ServerRequestInterface $request
    ): string {
        if (is_string($valueOrConfiguration)) {
            if ($this->typoScriptHelper->hasStdWrapProperties($typoScriptConfiguration, $key)) {
                $value = $this->typoScriptHelper->applyStdWrapProperties(
                    $valueOrConfiguration,
                    $this->typoScriptHelper->getStdWrapProperties(
                        $typoScriptConfiguration,
                        $key,
                    ),
                    $request,
                );
            } else {
                $value = $valueOrConfiguration;
            }
        } else {
            $value = $this->typoScriptHelper->applyStdWrapProperties(
                '',
                $valueOrConfiguration,
                $request
            );
        }

        return $value;
    }

    /**
     * @param array<int, string> $processingConfig
     */
    protected function getContentForProcessing(array $processingConfig, string $configurationSearchPointer): string
    {

        $contentForProcessing = $this->getValueByPath(
            $processingConfig,
            'search./' . $configurationSearchPointer,
        );

        if (ArrayUtility::isValidPath($processingConfig, 'replace./' . $configurationSearchPointer)) {
            $replaceContentForProcessing = (string)$this->getValueByPath(
                $processingConfig,
                'replace./' . $configurationSearchPointer,
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
     * @param array<int, mixed>|null $configuration
     * @return bool
     */
    protected function shouldDoStdWrap(array|null $configuration): bool
    {
        return is_array($configuration);
    }

    /**
     * @param array<int, mixed> $replacerConfiguration
     * @return array<int,mixed>
     */
    protected function getConfigurationFor(
        array $replacerConfiguration,
        ConfigurationTypeEnumeration $configurationType
    ): array {
        try {
            $typoScriptConfiguration = ArrayUtility::getValueByPath(
                $replacerConfiguration,
                (string)$configurationType,
            );

            // We need correct sorting: 10, 10., 20, 30, 30.
            ksort($typoScriptConfiguration, SORT_NATURAL);

            return $typoScriptConfiguration;
        } catch (MissingArrayPathException | \RuntimeException $exception) {
            return [];
        }
    }

    /**
     * @param array<int, mixed> $array
     * @return array<int, mixed>|string|null
     */
    protected function getValueByPath(array $array, string $path): array|string|null
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
