<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

class Clean extends Command
{
    /**
     * @throws Exception\Command
     * @cli-command clean
     * @cli-info Reset your Social Engine script to a clean state.
     */
    public function process()
    {
        $base = $this->config->get('path');

        if (!file_exists($base . 'application/libraries/Engine/Api.php')) {
            throw new Exception\Command('Does not seem like SE resides here.');
        }

        $remove = [
            'application/settings/database.php'
        ];

        foreach ($remove as $file) {
            $this->exec('rm -f ' . $base . $file);
        }

        $packages = $base . 'application/packages/';
        foreach (scandir($packages) as $package) {
            if ($package == '.' || $package == '..') {
                continue;
            }

            if (substr($package, -5) == '.json') {
                $this->exec('rm -f ' . $packages . $package);
            }
        }

        $this->write('Done!');
    }
}
