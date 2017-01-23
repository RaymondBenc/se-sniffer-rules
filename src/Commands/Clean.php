<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

class Clean extends Command
{
  public $name = 'clean';

  public $description = 'Reset your Social Engine script to a clean state.';

  public function process()
  {
    $base = rtrim($this->getConfigValue('path'), '/') . '/';
    if (!is_dir($base)) {
      throw new \Exception('Not a valid directory to work with: ' . $base);
    }

    if (!file_exists($base . 'application/libraries/Engine/Api.php')) {
      throw new \Exception('Does not seem like SE resides here.');
    }

    $remove = [
      'application/settings/database.php'
    ];

    foreach ($remove as $file) {
      $this->exec('rm -f ' . $base . $file);
    }

    $this->write('Done!');
  }
}
