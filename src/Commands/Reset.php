<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

class Reset extends Command
{
    /**
     * @throws Exception\Command
     * @cli-command reset
     * @cli-info Reset your Social Engine script to a clean state.
     */
    public function process()
    {
        $base = $this->getConfig('path');

        if (!file_exists($base . 'application/libraries/Engine/Api.php')) {
            throw new Exception\Command('Does not seem like SE resides here.');
        }

        $this->step('Resetting SE', function () use ($base) {
            $remove = [
                'application/settings/database.php',
                'temporary/log/import-phpfox.log'
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
        });
    }
}
