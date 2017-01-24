<?php

namespace SocialEngine\Console;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Composer
{
  public static function postPackageInstall(Event $event)
  {
    $path = $_SERVER['PWD'];
    $package = $event->getComposer()->getPackage();

    if ($package->getName() == 'raymondbenc/socialengine-console') {
      copy(__DIR__ . '/../bin/socialengine', $path . '/socialengine');
    }
  }
}
