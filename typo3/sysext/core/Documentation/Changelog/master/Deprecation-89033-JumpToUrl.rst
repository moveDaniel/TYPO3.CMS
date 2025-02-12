.. include:: ../../Includes.txt

===============================
Deprecation: #89033 - jumpToUrl
===============================

See :issue:`89033`

Description
===========

The JavaScript function :js:`jumpToUrl()` which is widely used in TYPO3 has been marked as deprecated.


Impact
======

Calling :js:`jumpToUrl()` will cause a deprecation entry in the browser's console.


Affected Installations
======================

Extensions using the native :js:`jumpToUrl()` implementation are affected.


Migration
=========

Since :js:`jumpToUrl()` triggers a redirect only, it's safe to either use `window.location.href = 'link/to/my/module';`
or use the link in combination with old-fashioned HTML as in :html:`<a href="link/to/my/module">my link</a>`.

.. index:: Backend, JavaScript, NotScanned, ext:backend
