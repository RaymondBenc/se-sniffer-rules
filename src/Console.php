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

    /**
     * @var SymfonyCommand
     */
    private $symfony;

    /**
     * @var Command[]
     */
    private $commands = [];

    /**
     * Console constructor.
     * @param array $config
     */
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
                $this->add($command);
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
     * Array of all commands
     *
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Register path to autoload SE and Zend classes.
     */
    private function register()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('PS') || define('PS', PATH_SEPARATOR);
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', rtrim($this->config['path'], '/') . '/');
        defined('_ENGINE') || define('_ENGINE', true);

        spl_autoload_register(function ($class) {
            $class = str_replace('_', '/', $class);
            if (substr($class, 0, 6) == 'Engine' ||
                substr($class, 0, 4) == 'Zend' ||
                substr($class, 0, 4) == 'Core'
            ) {
                // $this->config['path'] = rtrim($this->config['path'], '/') . '/';
                $path = $this->config['path'] . 'application/libraries/' . $class . '.php';

                require($path);
            }
        });

        // \Engine_Loader::getInstance()->register('Core', APPLICATION_PATH . '/application/modules/Core');
    }

    /**
     * Add a command to Symfony Console
     *
     * @param string $command
     * @throws \Exception If doc comments are missing.
     */
    private function add($command)
    {
        $command = 'SocialEngine\\Console\\Commands\\' . str_replace('.php', '', $command);
        $reflection = new \ReflectionClass($command);

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->class == $command && $method->getName() != '__construct') {
                $docComments = $method->getDocComment();

                if (empty($docComments)) {
                    throw new \Exception('Missing doc comments for this method: ' .
                        $method->class . '::' . $method->getName());
                }
                $comments = explode("\n", $docComments);
                foreach ($comments as $comment) {
                    $comment = trim(str_replace('*', '', $comment));
                    if (substr($comment, 0, 5) == '@cli-') {
                        $parts = explode(' ', trim(explode('@cli-', $comment)[1]));
                        $name = trim($parts[0]);
                        unset($parts[0]);
                        $data = implode(' ', $parts);

                        if ($name != 'command' && is_null($this->symfony)) {
                            throw new \Exception('Command is not setup correctly.');
                        }

                        switch ($name) {
                            case 'argument':
                                $this->symfony->addArgument($data);
                                break;
                            case 'command':
                                $methodName = $method->getName();
                                $key = $data . '->' . $methodName;
                                $this->symfony = new SymfonyCommand($data, $methodName);
                                $this->symfony->setName($data);
                                $this->commands[$key] = $reflection->newInstanceArgs([
                                    $this->symfony,
                                    $this->config
                                ]);
                                $this->symfony->setCommand($this->commands[$key]);
                                break;
                            case 'info':
                                $this->symfony->setDescription($data);
                                break;
                        }
                    }
                }

                $this->app->add($this->symfony);
                $this->symfony = null;
            }
        }
    }
}
