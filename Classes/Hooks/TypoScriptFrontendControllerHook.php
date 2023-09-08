<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Hooks;

use JWeiland\Replacer\Helper\ReplacerHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TypoScriptFrontendController
 * Used for Hook $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']
 */
class TypoScriptFrontendController
{
    /**
     * Replace text for pages without USER_INT plugins. Otherwise Middleware\ReplaceContent will be used!
     *
     * @param array $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $ref
     */
    public function contentPostProcAll(
        array &$params,
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $ref
    ) {
        if ($ref->isINTincScript()) {
            return;
        }
        $ref->content = GeneralUtility::makeInstance(ReplacerHelper::class)
            ->replace($ref->content, $ref);
    }
}
