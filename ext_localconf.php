<?php

defined('TYPO3') or die();

use JWeiland\Replacer\Hook\TypoScriptFrontendControllerHook;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(static function () {
    $typo3VersionUtility = GeneralUtility::makeInstance(Typo3Version::class);

    if (version_compare($typo3VersionUtility->getBranch(), '12.0', '<')) {
        // Register hook for cached content.
        // This has no effect since TYPO3 12, because the hook doesn't exist inside the core anymore, and we
        // implemented an event listener based on PSR-14 Event 'AfterCacheableContentIsGeneratedEvent' which was
        // available since TYPO3 12.0.
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['z9_replacer'] =
            TypoScriptFrontendControllerHook::class . '->contentPostProcAll';
    }
});
