<?php

namespace iFixit\Opcache;
use \Exception;

class Opcache {
   public function __construct() {
      if (!$this->ini_get_bool('opcache.validate_timestamps')) {
         throw new Exception(
          "ini setting: opcache.validate_timestamps must be enabled");
      }
      if (!$this->ini_get_bool('opcache.enable')) {
         throw new Exception(
          "ini setting: opcache.enable must be true");
      }
      if (intval(ini_get('opcache.revalidate_freq')) < 1) {
         throw new Exception(
          "ini setting: opcache.revalidate_freq must be set very high");
      }
   }

   /**
    * Invalidates the cache for all files that have been modified since they've
    * been cached.
    */
   public function invalidateChangedFiles() {
      $this->waitForFileUpdateProtection();
      $allFiles = $this->getCachedFiles();
      $expired = $this->getExpiredFiles($allFiles);
      $this->invalidateFiles($expired);
      return $expired;
   }

   /**
    * file_update_protection prevents caching a file if it's younger than X
    * seconds old. This can lead to invalidating the cache, then having the
    * file continuously recompile till it's at least X seconds old.
    *
    * This means files can be compiled many times in a short time frame and may
    * include modifications made *after* the deploy process.
    * Since php uses `request_time` (not time()) for its check, cli scripts
    * launched immediately after deploy may never opcache some scripts.
    *
    * The only solution is to stall the deploy process long enough
    * so that no file is that young. The test here simply ensures that the deploy
    * process stalls long enough.
    */
   protected function waitForFileUpdateProtection() {
      $protection = ini_get("opcache.file_update_protection");
      if ($protection) {
         usleep($protection * 1000000 + 50000);
      }
   }

   /**
    * Returns array of all cached php files, each entry has several bits of
    * info about the cache.
    */
   protected function getCachedFiles() {
      $data = opcache_get_status();
      return $data['scripts'];
   }

   /**
    * Returns an array of paths to files who's cached values need to be
    * cleared.
    */
   protected function getExpiredFiles(array $cachedFiles) {
      // filemtime() result is cached per request
      clearstatcache(true);
      $files = [];
      // Iterate over cached scripts
      foreach ($cachedFiles as $cachedFile) {
         $file = $cachedFile['full_path'];
         // If file still exists and the cached timestamp is behind the real one,
         // mark it for invalidation
         try {
            if (filemtime($file) > $cachedFile['timestamp']) {
               $files[] = $file;
            }
         // we can't use file_exists() cause that's based on opcache
         // so we just call filemtime() on potentially missing files
         // and add them to the list if they fail.
         } catch (Exception $e) {
            $files[] = $file;
         }
      }

      return $files;
   }

   protected function invalidateFiles(array $files) {
      foreach($files as $file) {
         opcache_invalidate($file, true);
      }
   }

   /**
    * Returns true if the passed boolean based ini setting is enabled
    *
    * Sad that we have to do this, but... it's php.
    */
   protected function ini_get_bool($name) {
      $val = strtolower(ini_get($name));
      return $val === '1' || $val === 'true' || $val === 'on';
   }
}
