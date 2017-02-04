<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;

/**
 * @inheritdoc
 */
class Check extends Command
{
    /**
     * @cli-command check:standard
     * @cli-info Run a code check via PHP CodeSniffer. Errors report to ./tmp/standard-error.log
     */
    public function standard()
    {
        $dir = 'application/vendor/raymondbenc/socialengine-coding-standards/SocialEngine/';
        $default = $this->getConfig('path') . $dir;
        $standards = $this->getConfig('phpcs-standard', $default);
        $path = $this->getConfig('path');

        $bin = $this->getBin('php') . ' ' . $this->getBaseDir() . 'vendor/bin/phpcs --standard="' . $standards . '" ';

        chdir($path);

        $files = [];
        $currentBranch = $this->git('rev-parse --abbrev-ref HEAD');
        $mainBranch = 'develop';
        if ($currentBranch != $mainBranch) {
            $diffLines = explode("\n", $this->git('diff --name-only  ' . $mainBranch . ' ' . $currentBranch));
            foreach ($diffLines as $file) {
                $file = trim($file);
                if ($this->shouldSkip($file)) {
                    continue;
                }

                $files[$file] = $path . $file;
            }
        }

        $log = [];
        foreach ($files as $file) {
            $this->write($file);
            if (file_exists($file)) {
                $report = trim($this->exec($bin . ' ' . $file));
                if (empty($report)) {
                    $this->color('green');
                    $report = 'OK';
                } else {
                    $this->color('red');
                    $log[] = $report;
                    $report = 'Failed';
                }
            } else {
                $this->color('red');
                $report = 'File not found';
            }
            $this->write($report);
        }

        if (count($log)) {
            $logFile = $this->getTempDir() . 'standard-errors.log';
            file_put_contents($logFile, implode("\n", $log));
            $this->writeResults([
                'Found ' . count($log) . ' out of ' . count($files) . ' error(s)',
                'Error log: ' . $logFile
            ]);
        } else {
            $this->writeResults('All good!');
        }
    }

    /**
     * Check if we should skip a check on a file.
     *
     * @param string $path Path to file
     * @return bool
     */
    private function shouldSkip($path)
    {
        $skip = false;
        if (substr($path, -4) != '.php') {
            $skip = true;
        }

        return $skip;
    }
}
