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
    * Returns true if the passed boolean based ini setting is enabled
    *
    * Sad that we have to do this, but... it's php.
    */
   protected function ini_get_bool($name) {
      $val = strtolower(ini_get($name));
      return $val === '1' || $val === 'true' || $val === 'on';
   }
}
