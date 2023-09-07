<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Helper;

use function count;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Helper class for content replacement using TSFE
 */
class ReplacerHelper
{
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
     *
     * @param string $contentToReplace
     * @param TypoScriptFrontendController $typoScriptFrontendController
     * @return string
     */
    public function replace(string $contentToReplace, TypoScriptFrontendController $typoScriptFrontendController): string
    {
        if (
            !empty($typoScriptFrontendController->config['config']['tx_replacer.']['search.'])
            && !empty($typoScriptFrontendController->config['config']['tx_replacer.']['replace.'])
        ) {
            $search = [];
            $replace = [];
            $loops = [
                'search' => &$typoScriptFrontendController->config['config']['tx_replacer.']['search.'],
                'replace' => &$typoScriptFrontendController->config['config']['tx_replacer.']['replace.'],
            ];
            foreach ($loops as $name => &$config) {
                foreach ($config as $key => &$content) {
                    if (is_string($key) && $key[-1] === '.') {
                        continue;
                    }
                    if (!empty($typoScriptFrontendController->config['config']['tx_replacer.'][$name . '.'][$key . '.'])) {
                        if ($typoScriptFrontendController->cObj instanceof ContentObjectRenderer) {
                            ${$name}[] = $typoScriptFrontendController->cObj->stdWrap(
                                $content,
                                $typoScriptFrontendController->config['config']['tx_replacer.'][$name . '.'][$key . '.']
                            );
                        }
                    } else {
                        ${$name}[] = $content;
                    }
                }
            }
            // Only replace if search and replace count are equal
            if (count($search) === count($replace)) {
                if (
                    array_key_exists('enable_regex', $typoScriptFrontendController->config['config']['tx_replacer.'])
                    && $typoScriptFrontendController->config['config']['tx_replacer.']['enable_regex']
                ) {
                    // replace using a regex as search pattern
                    $contentToReplace = preg_replace(
                        $search,
                        $replace,
                        $contentToReplace
                    );
                } else {
                    // replace using a regular string as search pattern
                    $contentToReplace = str_replace(
                        $search,
                        $replace,
                        $contentToReplace
                    );
                }
            } else {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->log(
                    LogLevel::ERROR,
                    'Each search item must have a replace item!',
                    $typoScriptFrontendController->config['config']['tx_replacer.']
                );
            }
        }
        return $contentToReplace;
    }
}
