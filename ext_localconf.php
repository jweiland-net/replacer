<?php
defined('TYPO3_MODE') or die();

// Register hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
    \JWeiland\Replacer\Hooks\TypoScriptFrontendController::class . '->contentPostProcAll';
