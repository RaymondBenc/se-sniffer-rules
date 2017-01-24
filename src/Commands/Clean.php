<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

class Clean extends Command
{
  public $name = 'clean';

  public $description = 'Reset your Social Engine script to a clean state.';

  public function process()
  {
    $base = SE_CONSOLE_DIR;

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
