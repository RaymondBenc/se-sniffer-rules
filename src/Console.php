<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Application;

/**
 * Social Engine Console
 *
 * @see bin/socialengine
 * @package SocialEngine\Console
 */
class Console
{
    const VERSION = '1.0.5';

    public function __construct()
    {
        try {
            if (!defined('SE_CONSOLE_DIR')) {
                throw new \Exception('Constant "SE_CONSOLE_DIR" must be defined.');
            }

            $this->register();

            $app = new Application('Social Engine Console', self::VERSION);
            $dir = __DIR__ . '/Commands/';
            foreach (scandir($dir) as $command) {
                if ($command == '.' || $command == '..') {
                    continue;
                }

                if (substr($command, -4) == '.php') {
                    $command = 'SocialEngine\\Console\\Commands\\' . str_replace('.php', '', $command);
                    $ref = new \ReflectionClass($command);
                    $object = $ref->newInstanceArgs([$command, $ref]);
                    $app->add($object->__attach);
                }
            }
            $app->run();
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage() . PHP_EOL);
            exit(1);
        }
    }

    /**
     * Register path to autoload SE and Zend classes.
     */
    private function register()
    {
        spl_autoload_register(function ($class) {
            $class = str_replace('_', '/', $class);
            if (substr($class, 0, 6) == 'Engine' || substr($class, 0, 4) == 'Zend') {
                $path = SE_CONSOLE_DIR . 'application/libraries/' . $class . '.php';

                require($path);
            }
        });
    }
}
