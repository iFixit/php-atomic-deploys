<?php

require './src/Opcache.php';
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
      $op = new Opcache();
      $file = $this->temp();
      $this->makePhpFile($file, 'a');
      $this->assertSame('a', require $file);

      // Sleep long enough for the clock to tick over one second so
      // time() changes.
      usleep(1200*1000);

      // Assert cache shouln't be cleared cause the file hasn't changed
      $files = $op->invalidateChangedFiles();
      $this->assertNotContains($file, $files);

      // Assert cache isn't cleared when the file is updated
      $this->makePhpFile($file, 'b');
      $this->assertSame('a', require $file);

      // Clear the changed files
      $files = $op->invalidateChangedFiles();

      // Assert the file is recompiled on next access
      $this->assertSame('b', require $file);
      // Assert it was the only one that was expired
      $this->assertSame([$file], $files);
   }

   public function testMissingFile() {
      $op = new Opcache();
      $file = $this->temp();
      $this->makePhpFile($file, 'a');
      $this->assertSame('a', require $file);
      unlink($file);

      // Sleep long enough for the clock to tick over one second so
      // time() changes.
      usleep(1200*1000);

      // This shouldn't fail even though the cached file doesn't exist anymore.
      $files = $op->invalidateChangedFiles();
   }

   protected function makePhpFile($file, $returnVal) {
      $php = "<?php return '$returnVal';";
      file_put_contents($file, $php);
   }

   protected function temp() {
      return tempnam(sys_get_temp_dir(), 'php');
   }
}
