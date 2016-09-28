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
