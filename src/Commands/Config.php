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
     * @cli-argument config:set:name
     * @cli-argument config:set:value
     * @cli-info Set a configuration value
     */
    public function set($name, $value)
    {
        $this->config->set($name, $value);
        $this->write('/.config.json updated.');
    }

    /**
     * @cli-command config:get
     * @cli-argument config:get:name
     * @cli-info Get a configuration value
     */
    public function get($name)
    {
        $this->write($this->config->get($name));
    }
}
