<?php

namespace JWeiland\Replacer\Hooks;

/*
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/

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
                $ref->content = str_replace(
                    $search,
                    $replace,
                    $ref->content
                );
            } else {
                GeneralUtility::sysLog(
                    'Each search item must have a replace item!',
                    'replacer',
                    GeneralUtility::SYSLOG_SEVERITY_ERROR
                );
            }
        }
    }
}
