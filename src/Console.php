<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Application;

/**
 * Class Console
 *
 * Initiates a new CLI
 *
 * @see bin/socialengine
 * @package SocialEngine\Console
 */
class Console
{
  const VERSION = '1.0.0';

  public function __construct()
  {
    try {
      if (!defined('SE_CONSOLE_DIR')) {
        throw new \Exception('Constant "SE_CONSOLE_DIR" must be defined.');
      }

      $app = new Application('Social Engine Console', self::VERSION);
      $dir = __DIR__ . '/Commands/';
      foreach( scandir($dir) as $command ) {
        if( $command == '.' || $command == '..' ) {
          continue;
        }

        if( substr($command, -4) == '.php' ) {
          $command = 'SocialEngine\\Console\\Commands\\' . str_replace('.php', '', $command);
          $ref = new \ReflectionClass($command);
          $object = $ref->newInstance();
          $app->add($object->__attach);
        }
      }
      $app->run();
    } catch (\Exception $e) {
      fwrite(STDOUT, $e->getMessage() . PHP_EOL);
      exit(1);
    }
  }
}
