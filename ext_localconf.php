<?php

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

// To check version
$typo3VersionUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Information\Typo3Version::class
);

if (version_compare($typo3VersionUtility->getBranch(), '12.4', '>=')) {
    // Register hook for cached content
    // this has no effect in TYPO3 12 because the hook does'nt exists inside the core and we implemented a event listener
    // based on PSR-14 E
    //vents 'AfterCacheableContentIsGeneratedEvent'
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['z9_replacer'] =
        \JWeiland\Replacer\Hooks\TypoScriptFrontendController::class . '->contentPostProcAll';
}
// For Version above 12.4 instead of Hook implementation PSR 14 Event configured under 'Configurations/Services.yaml
// event we used is AfterCacheableContentIsGeneratedEvent
