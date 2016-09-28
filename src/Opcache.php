<?

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
      $allFiles = $this->getCachedFiles();
      $expired = $this->getExpiredFiles($allFiles);
      $this->invalidateFiles($expired);
      return $expired;
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
      $files = [];
      // Iterate over cached scripts
      foreach ($cachedFiles as $cachedFile) {
         $file = $cachedFile['full_path'];
         // If file still exists and the cached timestamp is behind the real one,
         // mark it for invalidation
         if (file_exists($file) && filemtime($file) > $cachedFile['timestamp']) {
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
