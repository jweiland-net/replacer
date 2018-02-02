.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration
=======================

The configuration of ext:replacer is that easy. The whole configuration will be made inside your TypoScript Setup.

**Example:** ::

  config.tx_replacer {
    search {
      1="/typo3temp/pics/
      2="/fileadmin/
    }
    replace {
      1="http://cd/i/
      2="http://mycdn.com/f/
    }
  }
