<?php

namespace SocialEngine\Console;

use Composer\Installer\PackageEvent;

class Composer
{
  public static function postPackageInstall(PackageEvent $event)
  {
    $path = $_SERVER['PWD'];
    copy(__DIR__ . '/../bin/socialengine', $path . '/socialengine');
  }
}
