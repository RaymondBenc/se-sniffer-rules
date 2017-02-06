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

        $bin = $this->getBin('php') . ' ' . $this->getBaseDir() . 'vendor/bin/phpcs --standard="' . $standards . '" ';

        $log = [];
        $files = $this->getFiles();
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
     * @cli-command check:compatibility
     * @cli-info Check PHP compatibility on PHP 5.2 -> 7.1
     */
    public function compatibility()
    {
        $dockerFile = file_get_contents($this->getBaseDir() . 'build/docker/template');
        $images = [
            '5.2' => 'helder/php-5.2',
            '5.3' => 'helder/php-5.3',
            '5.4' => 'cytopia/php-fpm-5.4',
            '5.5' => 'cytopia/php-fpm-5.5',
            '5.6' => 'php:5.6-cli',
            '7.0' => 'php:7.0-cli',
            '7.1' => 'php:7.1-cli'
        ];

        $tmp = $this->getTempDir() . 'docker/';

        if (is_dir($tmp)) {
            $this->exec('rm -rf ' . $tmp);
        }
        mkdir($tmp);

        $fix = function ($file) {
            return str_replace($this->getConfig('path'), '/app/', $file);
        };
        file_put_contents($tmp . 'docker-files.log', implode("\n", array_map($fix, $this->getFiles())));

        copy($this->getBaseDir() . 'build/docker/test.php', $tmp . 'test.php');

        $files = $this->getFiles();
        foreach ($files as $relative => $file) {
            $spl = new \SplFileInfo($relative);
            $baseDir = $tmp . $spl->getPath();
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }
            copy($file, $tmp . $relative);
        }

        foreach ($images as $version => $image) {
            $newFile = str_replace('{{ PHP_VERSION }}', $image, $dockerFile);

            $dockerFilePath = $tmp . 'Dockerfile';
            file_put_contents($dockerFilePath, $newFile);

            chdir($tmp);

            $reportedErrors = new \stdClass;
            $reportedErrors->errors = [];
            $response = $this->exec('docker build --no-cache=true -t=image --rm=true .');
            $callback = function ($matches) use ($reportedErrors) {
                $errors = [];
                eval('$errors = ' . $matches[1] . ';');
                foreach ($errors as $file => $reports) {
                    $reportedErrors->errors[$file] = $reports[0];
                }
            };
            $response = preg_replace_callback('/<docker-error-log>(.*?)<\/docker-error-log>/is', $callback, $response);
            if ($reportedErrors->errors) {
                $errors = '##################################' . PHP_EOL;
                $errors .= '# PHP Version: ' . $version . PHP_EOL;
                $errors .= '##################################' . PHP_EOL . PHP_EOL;
                foreach ($reportedErrors->errors as $name => $error) {
                    $errors .= ' - ' . $name . PHP_EOL;
                    $errors .= ' ----> ' . $error . PHP_EOL;
                }

                $fileName = $this->getTempDir() . 'php-errors-' . $version . '.log';
                file_put_contents($fileName, $errors);

                $this->writeResults([
                    'PHP ' . $version . ' found ' . count($reportedErrors->errors) . ' error(s)',
                    'Error log: ' . $fileName
                ]);
            } else {
                $this->writeResults('PHP ' . $version . ' found no error(s)');
            }
        }

        $this->exec('rm -rf ' . $tmp);
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

        if (!file_exists($path)) {
            $skip = true;
        }

        return $skip;
    }

    /**
     * Get git branch files.
     *
     * @return array
     */
    private function getFiles()
    {
        $current = getcwd();
        $path = $this->getConfig('path');
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

        chdir($current);

        return $files;
    }
}
