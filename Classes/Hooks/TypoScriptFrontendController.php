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
    public function contentPostProcOutput(
        array &$params,
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $ref
    ) {
        if (
            !empty($ref->config['config']['tx_replacer.']['search.'])
            && !empty($ref->config['config']['tx_replacer.']['replace.'])
        ) {
            $ref->content = str_replace(
                $ref->config['config']['tx_replacer.']['search.'],
                $ref->config['config']['tx_replacer.']['replace.'],
                $ref->content
            );
        }
    }
}
