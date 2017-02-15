<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

/**
 * SE File system scanner
 */
class Scan extends Command
{
    /**
     * @throws Exception\Command If missing version number
     *
     * @cli-command scan:sql-new-columns
     * @cli-argument version
     * @cli-info Scans all modules for a specific version if there are new columns.
     *
     * @param string $version
     */
    public function sqlNewColumns($version)
    {
        if (empty($version)) {
            throw new Exception\Command('Missing version number.');
        }

        $logFile = $this->getTempDir() . 'sql-new-columns.txt';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        touch($logFile);

        $modules = $this->getConfig('path') . 'application/modules/';
        foreach (new \RecursiveDirectoryIterator($modules, \RecursiveDirectoryIterator::SKIP_DOTS) as $dir) {
            if ($dir instanceof \SplFileInfo) {
                if ($dir->isFile()) {
                    continue;
                }

                $name = $dir->getFilename();
                $settings = $dir->getRealPath() . '/settings/';
                $dt = new \RecursiveDirectoryIterator($settings, \RecursiveDirectoryIterator::SKIP_DOTS);
                foreach ($dt as $module) {
                    if ($module instanceof \SplFileInfo
                        && $module->getExtension() == 'sql'
                        && substr($module->getRealPath(), -9) == $version . '.sql'
                    ) {
                        $lines = file($module->getRealPath());
                        $changes = [];
                        foreach ($lines as $line) {
                            $regex = '/ALTER TABLE `(.*?)` ADD `(.*?)` (.*?);/i';
                            if (preg_match($regex, $line, $matches)) {
                                if (!isset($changes[$matches[1]])) {
                                    $changes[$matches[1]] = [];
                                }
                                $changes[$matches[1]][] = $matches[2];
                            }
                        }

                        if ($changes) {
                            $data = "\n\n## {$name} ##\n";

                            $data .= "'{$version}' => array(\n";
                            $iteration = 0;
                            foreach ($changes as $table => $columns) {
                                $iteration++;
                                $data .= "\t'{$table}' => array(";
                                foreach ($columns as $column) {
                                    $data .= "'{$column}', ";
                                }
                                $data = rtrim($data, ', ');
                                $data .= ")";
                                if (count($changes) != $iteration) {
                                    $data .= ",";
                                }
                                $data .= "\n";
                            }
                            $data .= ")\n";
                            file_put_contents($logFile, $data, FILE_APPEND);
                        }
                    }
                }
            }
        }

        $this->writeResults('Log file: ' . $logFile);
    }
}
