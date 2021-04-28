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

**Basic regex example** ::

  config.tx_replacer {
    enable_regex = 1
    search {
      10 = /apple|raspberry/
    }

    replace {
      10 = banana
    }

.. seealso::

   * :ref:`examples`
