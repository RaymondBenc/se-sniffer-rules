<?php

namespace SocialEngine\Console\Helper;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

/**
 * Class Config Helper
 *
 * @package SocialEngine\Console\Helper
 */
class Config extends BaseCommand
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * Path to config file.
     *
     * @var string
     */
    private $file;

    /**
     * Config constructor.
     * @param Command $command
     * @param $config array
     */
    public function __construct(Command $command, $config)
    {
        parent::__construct($command);

        $this->file = $this->command->getBaseDir() . '.config.json';
        $this->config = $config;
    }

    /**
     * Get a config value.
     *
     * @param string $name Config name
     * @param null|string $default Config default value
     * @return string|null
     */
    public function get($name, $default = null)
    {
        $value = isset($this->config[$name]) ? $this->config[$name] : $default;
        if ($name == 'path') {
            $value = rtrim($value, '/') . '/';
        }

        return $value;
    }

    /**
     * Set a config file and save to file.
     *
     * @param string $name Key
     * @param string $value Value
     * @throws Exception\Helper If we cannot write to config file.
     * @return bool
     */
    public function set($name, $value)
    {
        $this->config[$name] = $value;

        @file_put_contents($this->file, json_encode($this->config, JSON_PRETTY_PRINT));

        if (!file_exists($this->file)) {
            throw new Exception\Helper('Unable to write to config file: ' . $this->file);
        }

        return true;
    }

    /**
     * Return all config params.
     *
     * @return array
     */
    public function all()
    {
        return $this->config;
    }
}
