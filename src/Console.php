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
    /**
     * Current console version
     *
     * @var string
     */
    private $version;

    /**
     * @var Application
     */
    private $app;

    /**
     * Configuration values
     *
     * @var array
     */
    private $config;

    public function __construct($config = [])
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'));

        $this->version = $composer->version;
        $this->config = $config;

        if (!isset($this->config['path'])) {
            $path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/';
            $this->config['path'] = $path;
        }

        $this->register();

        $this->app = new Application('Social Engine Console', $this->version);
        $dir = __DIR__ . '/Commands/';
        foreach (scandir($dir) as $command) {
            if ($command == '.' || $command == '..') {
                continue;
            }

            if (substr($command, -4) == '.php') {
                $command = 'SocialEngine\\Console\\Commands\\' . str_replace('.php', '', $command);
                $ref = new \ReflectionClass($command);
                $object = $ref->newInstanceArgs([$command, $ref, $this->config]);
                $this->app->add($object->__attach);
            }
        }
    }

    /**
     * Run Symfony command
     *
     * @see \Symfony\Component\Console\Application::all()
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        return $this->app->all();
    }

    /**
     * Register path to autoload SE and Zend classes.
     */
    private function register()
    {
        spl_autoload_register(function ($class) {
            $class = str_replace('_', '/', $class);
            if (substr($class, 0, 6) == 'Engine' || substr($class, 0, 4) == 'Zend') {
                $path = $this->config['path'] . 'application/libraries/' . $class . '.php';

                require($path);
            }
        });
    }
}
