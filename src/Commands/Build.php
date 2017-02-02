<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Helper\Packages;

/**
 * SE Builder
 */
class Build extends Command
{
    /**
     * @cli-command build:packages
     * @cli-info Build all SE packages
     */
    public function packages()
    {
        $packages = new Packages($this);

        $this->write('Building packages...');

        foreach ($packages->getJsonFiles() as $file) {
            unlink($file->getRealPath());
        }

        file_put_contents($this->config->get('path') . 'application/packages/index.html', '');

        $write = function ($manifestPath) use ($packages) {
            $package = $packages->buildPackageFile($manifestPath);
            if ($package) {
                $packageFileName = $this->config->get('path') . 'application/packages/' . $package->getKey() . '.json';
                file_put_contents($packageFileName, json_encode($package->toArray(), JSON_PRETTY_PRINT));
                $this->write(' -> ' . str_replace($this->config->get('path'), '', $packageFileName));
            }
        };

        foreach ($packages->getStructure() as $type => $info) {
            if (in_array($type, $packages->getActions())) {
                $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($info['path'], '/\\'));
                $path = $this->config->get('path') . $path;

                if (!$info['array']) {
                    $manifest = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($info['manifest']));
                    $manifestPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $manifest;
                    $write($manifestPath);
                } else {
                    $dirs = scandir($path);
                    foreach ($dirs as $dir) {
                        $dirPath = $path . DIRECTORY_SEPARATOR . $dir;

                        if ($dir[0] == '.' || !is_dir($dirPath)) {
                            continue;
                        }

                        $manifest = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($info['manifest']));
                        $manifestPath = $dirPath . DIRECTORY_SEPARATOR . $manifest;
                        $write($manifestPath);
                    }
                }
            }
        }

        $packages->buildPackageDb();

        $this->write('Done!');
    }
}
