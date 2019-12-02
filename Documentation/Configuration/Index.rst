.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration
=============

The configuration of ext:replacer is that easy. The whole configuration will be made inside your TypoScript Setup.


Basic search and replace
^^^^^^^^^^^^^^^^^^^^^^^^^
Just wanna search for apple and replace it by banana? That´s easy.

**Basic example** ::

  config.tx_replacer {
    search {
      10 = apple
    }

    replace {
      10 = banana
    }
  }

Use a regex as search pattern
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

** Basic regex example** ::

  config.tx_replacer {
    enable_regex = 1
    search {
      10 = /apple|raspberry/
    }

    replace {
      10 = banana
    }

Replace links for CDN usage
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The example TypoScript replaces typo3temp,
typo3conf, uploads and fileadmin occurrences with CDN links.

**CDN example:** ::

  config.tx_replacer {
    search {
      10 = "/typo3temp/
      11 = "typo3temp/
      12 = "/typo3conf/
      13 = "typo3conf/
      14 = "/uploads/
      15 = "uploads/
      16 = "fileadmin/
      17 = "/fileadmin/
    }

    replace {
      10 = "https://cdn.tld/typo3temp/
      11 = "https://cdn.tld/typo3temp/
      12 = "https://cdn.tld/typo3conf/
      13 = "https://cdn.tld/typo3conf/
      14 = "https://cdn.tld/uploads/
      15 = "https://cdn.tld/uploads/
      16 = "https://cdn.tld/fileadmin/
      17 = "https://cdn.tld/fileadmin/
    }
  }


Original ::

  <script src="/typo3temp/assets/compressed/example.file-3b0e5471d7c4492019f42b9ea637ce4e.js.gzip?1520863480" type="text/javascript"></script>

Replaced by ::

  <script src="https://cdn.tld/typo3temp/assets/compressed/example.file-3b0e5471d7c4492019f42b9ea637ce4e.js.gzip?1520863480" type="text/javascript"></script>

Use stdWrap for search and replacement
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can use stdWrap functionality if you need a more dynamic way to search and replace content. The main step is
equal with the basic configuration like above. You can also use a regex as search pattern and a stdWrap as replacement
at the same time!

**Use page title as replacement** ::

  config.tx_replacer {
    search {
      10 = ###TITLE###
    }

    replace {
      10 =
      10.stdWrap.field = title
    }
  }

**Use page modification date as replacement** ::

  config.tx_replacer {
    search {
      10 = ###TIMESTAMP###
    }

    replace {
      10 =
      10.stdWrap {
        # format like 2017-05-31 09:08
        field = tstamp
        date = Y-m-d H:i
    }
  }

Take a look into the stdWrap documentation (https://docs.typo3.org/typo3cms/TyposcriptReference/Functions/Stdwrap/Index.html) for more information.
