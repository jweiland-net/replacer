<?php
defined('TYPO3_MODE') or die();

// Register hook for cached content
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
    \JWeiland\Replacer\Hooks\TypoScriptFrontendController::class . '->contentPostProcAll';

// Register hook for uncached content (USER_INT, uncached extbase actions, etc...)
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
    \JWeiland\Replacer\Hooks\TypoScriptFrontendController::class . '->contentPostProcAll';
