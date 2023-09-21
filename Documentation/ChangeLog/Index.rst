..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 3.0.2
=============

*   [TASK] Update .editorconfig
*   [TASK] Rename LICENSE.txt to just LICENSE
*   [TASK] Add directories to exclude to .gitattributes
*   [TASK] Move TYPO3 const check into new line of ext_localconf.php
*   [TASK] Remove unneeded properties from ext_emconf.php

Version 3.0.1
=============

*   [BUGFIX] Middleware called in the wrong position where TSFE not initialized

Version 3.0.0
=============

*   Add support for TYPO3 v12 LTS
*   Better Search and Replace handling
*   Documentation updated with several examples and upgrade notes.
*   Updated Unit Tests and Functional Tests to TYPO3 Testing Framework

Version 2.1.1
=============

*   Minor Bug Fixes

Version 2.1.0
=============

*   Add support for TYPO3 v11 LTS

Version 2.0.0
=============

*   Add support for TYPO3 11
*   Drop support for TYPO3 7 and 8
*   Replace deprecated contentPostProc-output hook by middleware
*   Check if page contains a USER_INT plugin, otherwise replace the content before caching the page (fixes #11)
*   Add new example section to documentation

Version 1.5.1
=============

*   Fix outdated $TYPO3_CONF_VARS constant usage

Version 1.5.0
=============

*   TYPO3 10 LTS Compatibility

Version 1.4.0
=============

*   Feature: Support regular expressions as search pattern
*   Bugfix: Run replacer hook nearly at the end

Version 1.3.0
=============

*   Allow replacement of uncached content e.g. USER_INT
*   Fix dependency to allow TYPO3 > 9.5.0

Version 1.2.0
=============

*   Use contentPostProc-all instead of contentPostProc-output (#1)
*   Upgrade to work with TYPO3 9.x (#2)

Version 1.1.0
=============

*   Version 1.1.0 allows usage of stdWrap for search and replace items.
*   Take a look into the documentation for some examples.

Version 1.0.0
=============

*   Added Initial release of the Replacer Extension.
*   Basic functionality for string pattern replacement.
*   Support for global configuration of search and replace patterns.
