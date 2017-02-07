<?php

namespace SocialEngine\Console\Helper;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

/**
 * Class Database Helper
 *
 * @package SocialEngine\Console\Db
 */
class Db
{
    private $config;

    public $factory;

    public function __construct(Command $command)
    {
        $configFile = APPLICATION_PATH . 'application/settings/database.php';

        if (!file_exists($configFile)) {
            throw new Exception\Helper('This command requires SE to be installed.');
        }

        $this->config = require($configFile);
        $this->factory = \Zend_Db::factory($this->config['adapter'], $this->config['params']);
    }
}
