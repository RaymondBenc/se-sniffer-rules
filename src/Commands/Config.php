<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

/**
 * Console Configuration
 */
class Config extends Command
{
    /**
     * @cli-command config:set
     * @cli-argument name
     * @cli-argument value
     * @cli-info Set a configuration value
     */
    public function set($name, $value)
    {
        $this->setConfig($name, $value);
        $this->write('/.config.json updated.');
    }

    /**
     * @cli-command config:get
     * @cli-argument name
     * @cli-info Get a configuration value
     */
    public function get($name)
    {
        $this->write($this->getConfig($name));
    }
}
