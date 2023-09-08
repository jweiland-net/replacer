..  include:: /Includes.rst.txt

..  _examples:

Example configurations
======================

In this section you will find some examples how ext:replacer can be used.

Replace pattern only within tags using a regex
==============================================

It is even possible to replace content just within a specific tag like a parameter.

**Replace content within a paragraph**

Code:

..  code-block:: typoscript

    config.tx_replacer {
      enable_regex = 1
      search {
        10 = #(<p[^>]*>.*?)SEARCH(.*?<\/p>)#
      }

      replace {
        10 = $1REPLACE$2
      }
    }

Original:

..  code-block:: html

    <p class="example">SEARCH</p>

Replaced by:

..  code-block:: html

    <p class="example">REPLACE</p>

Replace links for CDN usage
===========================

The example TypoScript replaces typo3temp,
typo3conf, uploads and fileadmin occurrences with CDN links.

**CDN example:**

Code:

..  code-block:: typoscript

    config.tx_replacer {
      search {
        10 = /"\/?(fileadmin|typo3temp|uploads)/
      }

      replace {
        10 = "https://cdn.tld/$1
      }
    }


Original:

..  code-block:: html

    <script src="/typo3temp/assets/compressed/example-3b0e5471d7c4492019f42b9ea637ce4e.js"></script>

Replaced by:

..  code-block:: html

    <script src="https://cdn.tld/typo3temp/assets/compressed/example-3b0e5471d7c4492019f42b9ea637ce4e.js"></script>


Use stdWrap for search and replacement
======================================

You can use stdWrap functionality if you need a more dynamic way to search and replace content. The main step is
equal with the basic configuration like above. You can also use a regex as search pattern and a stdWrap as replacement
at the same time!

**Use page title as replacement**

Code:

..  code-block:: typoscript

    config.tx_replacer {
      search {
        10 = ###TITLE###
      }

      replace {
        10 =
        10.stdWrap.field = title
      }
    }

**Use page modification date as replacement**

Code:

..  code-block:: typoscript

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

Take a look into the :ref:`stdWrap <t3tsref:stdwrap>` documentation for more information.
