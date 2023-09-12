..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

If you upgrade/update EXT:replacer to a newer version, please read this section carefully!


Update to Version 3.0.0
=======================

This version is NOT compatible with PHP versions lower than 7.4! and also the minimum TYPO3 version Compatibility is
11.5.30.

We rewrited the code base and there are so many possibilities and flexibility in configuration possible.

New Configuration Upgrade Needed for Regular expression
-------------------------------------------------------

In older versions regular expressions are configured like a global configuration and if the configuration enabled then all
search configurations should be written as regular expression only.

..  code-block:: typoscript

    config.tx_replacer {
      # if this is enabled make sure all search keys should be in regex
      enable_regex = 1
      search {
        10 = /apple|raspberry/
      }

      replace {
         10 = banana
      }
   }

This should be replaced from version 3. We made the individual processing of search replacement key. So you can configure
the extensions like below. The main advantage is you can write different configurations for individual keys.

..  code-block:: typoscript

    config.tx_replacer {
      search {
        10 = /apple|raspberry/
        10.enable_regex = 1
        20 = keyword
      }

      replace {
         10 = banana
         20 = replacer
      }
   }

Here 20 is not a regular expression key.

Replacer works now even if the search and replace values not same
-----------------------------------------------------------------

In older version if the search configurations and replace configurations should be equal. Otherwise replacer will skip the
processing. But in new version replacer will work if there is a valid replacer configuration exists in TypoScript.

For Example:

..  code-block:: typoscript

    config.tx_replacer {
      enable_regex = 1
      search {
        10 = /apple|raspberry/
        20 = /keyword/
      }

      replace {
         10 = banana
         #20 = replacer
      }
   }

In older version the above code snippet will not work at all because it skips the replace process due to difference in
search and replace configurations.

..  code-block:: typoscript

    config.tx_replacer {
      search {
        10 = /apple|raspberry/
        10.enable_regex = 1
        20 = keyword
      }

      replace {
         10 = banana
         #20 = replacer
      }
   }

Look at this one it will process the replacement for 10 and skip only for 20 (keep keyword in page content as it is.)
because it's replace configuration is commented and not available.
