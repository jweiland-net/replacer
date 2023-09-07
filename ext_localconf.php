<?php defined('TYPO3') or die();

use JWeiland\Replacer\Hooks\TypoScriptFrontendController;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(static function () {
    $typo3VersionUtility = GeneralUtility::makeInstance(
        Typo3Version::class
    );

    if (version_compare($typo3VersionUtility->getBranch(), '12.4', '>=')) {
        // Register hook for cached content
        // this has no effect in TYPO3 12 because the hook doesn't exist inside the core and we implemented
        // an event listener based on PSR-14 Events 'AfterCacheableContentIsGeneratedEvent'
        // For Version above 12.4 instead of Hook implementation PSR 14 Event configured under
        // 'Configurations/Services.yaml event we used is AfterCacheableContentIsGeneratedEvent
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['z9_replacer'] =
            TypoScriptFrontendController::class . '->contentPostProcAll';
    }
});
