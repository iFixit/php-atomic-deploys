<?

namespace iFixit\Opcache;
use \Exception;

class Opcache {
   public function __construct() {
      if (!$this->ini_get_bool('opcache.validate_timestamps')) {
         throw new Exception();
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
