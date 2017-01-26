<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

/**
 * @inheritdoc
 */
class Check extends Command
{
    /**
     * @cli-command check
     * @cli-info Run a code check via PHP CodeSniffer
     */
    public function process()
    {
        $standards = SE_CONSOLE_DIR . '/application/vendor/raymondbenc/socialengine-coding-standards/SocialEngine/';
        $bin = $this->getBin('php') . ' ' . $this->getBin('phpcs') . ' --standard="' . $standards . '" ';

        $files = explode("\n", $this->git('ls-tree --full-tree --name-only -r HEAD'));
        foreach ($files as $file) {
            $file = trim($file);
            if (empty($file)) {
                continue;
            }

            if (substr($file, -4) == '.php') {
                $this->write($this->exec($bin . ' ' . SE_CONSOLE_DIR . $file));
            }
        }
    }
}
