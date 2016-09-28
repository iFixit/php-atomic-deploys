php-atomic-deploys
==================
[![Build
Status](https://travis-ci.org/iFixit/php-atomic-deploys.svg?branch=master)](https://travis-ci.org/iFixit/php-atomic-deploys)

Provides a class that allows efficient and simple atomic deploys.

The Problem
===========
You have php's Opcache (apc's successor) turned on to improve performance.
You've enabled validate_timestamps so you can deploy by updating the files in
place (git checkout or something). This isn't atomic, updating many files takes
time, deploys span multiple requests with each getting *some* of the new files
and *some* of the old files, leading to undefined behavior or errors.

Solution
========
Tell Opcache to almost never check the filesystem for newer files. Update the
files in place during deploy. Then expire the opcache for all changed files.

This Class
==========

    // Note: this needs to be run in the context of your web-server
    // because opcache on the CLI doesn't share the same memory pool
    // as under apache
    $opcache = new Opcache();
    $opcache->invalidateChangedFiles();

How it Works
============
* Use these ini settings (the class will complain if these are not set
  similarly)
  * `opcache.validate_timestamps=1` - Check the modified times of files and
    re-compile when they are out of date.
  * `opcache.revalidate_freq=1000000000` - Effectively cache the timestamp
    for forever so we never check the filesystem
  * `opcache.file_update_protection=1` - Set low so we don't force the
    cache clearing to take longer than it should. Note: this is complex
* Calling `->invalidateChangedFiles()` uses the opcache api to:
   * Get a list of all cached php scripts
   * Compare the cached modified timestamp to mtime() for each file
   * Make a list of those that have an out-of-date timestamp
   * Call `invalidate_cache()` on each file in that list so it will be
     recompiled the next time `require()` is called
