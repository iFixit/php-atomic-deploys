<?php

require 'src/Opcache.php';
use iFixit\Opcache\Opcache;

class OpcacheTest extends PHPUnit_Framework_TestCase {
   public function setup() {
      ini_set('opcache.validate_timestamps', 1);
      ini_set('opcache.revalidate_freq', 100);
      ini_set("opcache.file_update_protection", 0);
   }

   public function testIniSettingsArePresent() {
      $op = new Opcache();
   }

   /**
    * @expectedException Exception
    */
   public function testIniSettingValidateTimestamps() {
      ini_set('opcache.validate_timestamps', 0);
      $op = new Opcache();
   }

   /**
    * @expectedException Exception
    */
   public function testIniSettingRevalidateFreq() {
      ini_set('opcache.revalidate_freq', 0);
      $op = new Opcache();
   }

   /**
    * @expectedException Exception
    */
   public function testIniSettingEnable() {
      ini_set('opcache.enable', 0);
      $op = new Opcache();
   }

   public function testInvalidation() {
      $file = $this->temp();
      $this->makePhpFile($file, 'a');
      $this->assertSame('a', require $file);

      // Sleep long enough for the clock to tick over one second so the
      // timestamp changes.
      usleep(1200*1000);

      // Assert cache isn't cleared when the file is updated
      $this->makePhpFile($file, 'b');
      $this->assertSame('a', require $file);
   }

   protected function makePhpFile($file, $returnVal) {
      $php = "<? return '$returnVal';";
      file_put_contents($file, $php);
   }

   protected function temp() {
      return tempnam(sys_get_temp_dir(), 'php');
   }
}
