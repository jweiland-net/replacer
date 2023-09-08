..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 3.0.0 (Release Date: September 08, 2023)
================================================

*   Add support for TYPO3 v12 LTS
*   Updated Unit Tests and Functional Tests to TYPO3 Testing Framework

Version 2.1.1 (Release Date: June 02, 2022)
===========================================

*   Minor Bug Fixes

Version 2.1.0 (Release Date: November 12, 2021)
===============================================

*   Add support for TYPO3 v11 LTS

Version 2.0.0 (Release Date: April 28, 2021)
============================================

*   Add support for TYPO3 11
*   Drop support for TYPO3 7 and 8
*   Replace deprecated contentPostProc-output hook by middleware
*   Check if page contains a USER_INT plugin, otherwise replace the content before caching the page (fixes #11)
*   Add new example section to documentation

Version 1.5.1 (Release Date: September 21, 2020)
================================================

*   Fix outdated $TYPO3_CONF_VARS constant usage

Version 1.5.0 (Release Date: April 28, 2020)
============================================

*   TYPO3 10 LTS Compatibility

Version 1.4.0 (Release Date: December 02, 2019)
===============================================

*   Feature: Support regular expressions as search pattern
*   Bugfix: Run replacer hook nearly at the end

Version 1.3.0 (Release Date: November 12, 2018)
===============================================

*   Allow replacement of uncached content e.g. USER_INT
*   Fix dependency to allow TYPO3 > 9.5.0

Version 1.2.0 (Release Date: September 27, 2018)
================================================

*   Use contentPostProc-all instead of contentPostProc-output (#1)
*   Upgrade to work with TYPO3 9.x (#2)

Version 1.1.0 (Release Date: May 08, 2018)
==========================================

*   Version 1.1.0 allows usage of stdWrap for search and replace items.
*   Take a look into the documentation for some examples.

Version 1.0.0 (Release Date: February 02, 2008)
===============================================

*   Added Initial release of the Replacer Extension.
*   Basic functionality for string pattern replacement.
*   Support for global configuration of search and replace patterns.
