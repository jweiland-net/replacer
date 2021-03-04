<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Hooks;

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class TypoScriptFrontendController
 * Used for Hook $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']
 *
 * @package JWeiland\Replacer\Hooks
 */
class TypoScriptFrontendController
{
    /**
     * Search and replace text from TypoScriptFrontendController->content
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
     * @param array $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $ref
     * @return void
     */
    public function contentPostProcAll(
        array &$params,
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $ref
    ) {
        if (
            !empty($ref->config['config']['tx_replacer.']['search.'])
            && !empty($ref->config['config']['tx_replacer.']['replace.'])
        ) {
            $search = [];
            $replace = [];
            $loops = [
                'search' => &$ref->config['config']['tx_replacer.']['search.'],
                'replace' => &$ref->config['config']['tx_replacer.']['replace.']
            ];
            foreach ($loops as $name => &$config) {
                foreach ($config as $key => &$content) {
                    if ($key[\strlen($key) - 1] === '.') {
                        continue;
                    }
                    if (!empty($ref->config['config']['tx_replacer.'][$name . '.'][$key . '.'])) {
                        if ($ref->cObj instanceof ContentObjectRenderer) {
                            ${$name}[] = $ref->cObj->stdWrap(
                                $content,
                                $ref->config['config']['tx_replacer.'][$name . '.'][$key . '.']);
                        }
                    } else {
                        ${$name}[] = $content;
                    }
                }
            }
            // Only replace if search and replace count are equal
            if (\count($search) === \count($replace)) {
                if (
                    array_key_exists('enable_regex', $ref->config['config']['tx_replacer.'])
                    && $ref->config['config']['tx_replacer.']['enable_regex']
                ) {
                    // replace using a regex as search pattern
                    $ref->content = preg_replace(
                        $search,
                        $replace,
                        $ref->content
                    );
                } else {
                    // replace using a regular string as search pattern
                    $ref->content = str_replace(
                        $search,
                        $replace,
                        $ref->content
                    );
                }
            } else {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->log(
                    LogLevel::ERROR,
                    'Each search item must have a replace item!',
                    $ref->config['config']['tx_replacer.']
                );
            }
        }
    }
}
