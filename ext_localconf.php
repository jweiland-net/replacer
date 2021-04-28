<?php
defined('TYPO3_MODE') or die();

// Register hook for cached content
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['z9_replacer'] =
    \JWeiland\Replacer\Hooks\TypoScriptFrontendController::class . '->contentPostProcAll';
