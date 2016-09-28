<?php

require 'src/Opcache.php';
use iFixit\Opcache\Opcache;

class OpcacheTest extends PHPUnit_Framework_TestCase {
   public function setup() {
      ini_set('opcache.revalidate_freq', 100);
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
}
