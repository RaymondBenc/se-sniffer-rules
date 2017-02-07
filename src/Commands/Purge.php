<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Helper;

class Purge extends Command
{
    /**
     * @cli-command purge:databases
     * @cli-info Purges all auto-created databases.
     */
    public function databases()
    {
        $config = $this->getSocialEngineConfig();
        $params = $config['params'];
        $sql = [
            $this->getBin('mysql'),
            '-h ' . $params['host'],
            '-u ' . $params['username'],
            '--password="' . $params['password'] . '"'
        ];

        $response = $this->exec(array_merge(['echo "SHOW DATABASES" |'], $sql));

        $toDelete = [];
        $this->write('The following databases we plan to drop...');
        foreach (explode("\n", $response) as $database) {
            $database = trim($database);
            if (empty($database)) {
                continue;
            }

            if (preg_match('/^se_([0-9]+){9}$/i', $database)) {
                $toDelete[] = $database;
                $this->write(' -> ' . $database);
            }
        }

        $answer = $this->ask('Can we drop these databases? [yes or no]');
        if ($answer == 'yes') {
            $this->step('Dropping databases', function () use ($toDelete, $sql) {
                foreach ($toDelete as $delete) {
                    $this->exec(array_merge(['echo "DROP DATABASE ' . $delete . '" |'], $sql));
                }
            });
        }
    }
}
