<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Hook;

use JWeiland\Replacer\Helper\ReplacerHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Used for Hook $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']
 */
class TypoScriptFrontendControllerHook
{
    protected ReplacerHelper $replacerHelper;

    public function __construct(ReplacerHelper $replacerHelper)
    {
        $this->replacerHelper = $replacerHelper;
    }

    /**
     * Replace text for pages without USER_INT plugins. Otherwise, Middleware\ReplaceContent will be used!
     */
    public function contentPostProcAll(array &$params, TypoScriptFrontendController $typoScriptFrontendController): void
    {
        if ($typoScriptFrontendController->isINTincScript()) {
            return;
        }

        $typoScriptFrontendController->content = $this->replacerHelper->replace(
            $typoScriptFrontendController->content,
        );
    }
}
