<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Traits;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Trait to get TSFE (now of course from GLOBALS)
 */
trait GetTypoScriptFrontendControllerTrait
{
    /**
     * Returns TSFE from GLOBALS
     * easy to manage the usage of GLOBALS TSFE
     * in future its migration friendly
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $this->getTypoScriptFrontendController()->cObj;
    }
}
