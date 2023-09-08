..  include:: /Includes.rst.txt


..  _typoscript:

==========
TypoScript
==========

`replacer` works with some basic TypoScript configuration. To do so you have to add any +ext template to either the root
page of your website or to a specific page which you need to work with the `replacer` extension.


Basic search and replace
========================

Imagine you have a website with various content, and throughout the pages, there are instances where the word "apple"
is used. You've decided that you want to replace all occurrences of "apple" with "banana".

Solution with Replacer Extension (Basic):
-----------------------------------------

Basic example:

..  code-block:: typoscript

    config.tx_replacer {
      search {
        10 = apple
      }

      replace {
        10 = banana
      }
    }

Outcome:
--------

Now, when visitors view your website, they will see "banana" instead of "apple" wherever it was originally used.

Use a regex as search pattern
=============================

Using a regular expression (regex) as a search pattern in the Replacer Extension provides a powerful way to perform
complex search and replace operations. Here's an elaboration:

Suppose you have a website with a variety of content, and you want to perform specific text replacements based on
patterns that can be defined using regular expressions.

Solution with Replacer Extension (regex example):
-------------------------------------------------

Basic regex example:

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

Outcome:
--------

Now, when visitors view your website, they will see the modified content with the replaced patterns based on the regular
expression you defined. Using regular expressions gives you the ability to perform intricate search and replace
operations, making it particularly useful for tasks that involve complex pattern matching.This feature provides a high
level of flexibility for customizing content on your website.

..  _configuration:

.. seealso::

   * :ref:`examples`

