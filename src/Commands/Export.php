<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;

/**
 * SE Builder
 */
class Export extends Command
{
    /**
     * @throws Exception\Command
     *
     * @cli-command export:module
     * @cli-argument name
     * @cli-info Export a module
     */
    public function module()
    {
        $packages = [];
        $name = $this->getArgument('name');
        if (!empty($name)) {
            $packages = [$name];
        } else {
            $modules = $this->getConfig('path') . 'application/modules/';
            foreach (new \RecursiveDirectoryIterator($modules, \RecursiveDirectoryIterator::SKIP_DOTS) as $dir) {
                if ($dir instanceof \SplFileInfo) {
                    if ($dir->isFile()) {
                        continue;
                    }
                    $packages[] = $dir->getFilename();
                }
            }
        }

        $tempDir = $this->getTempDir() ;
        if (is_dir($tempDir)) {
            $this->exec('rm -rf ' . $tempDir);
        }
        mkdir($tempDir);

        $exported = [];
        foreach ($packages as $package) {
            $response = $this->step('Exporting module: ' . $package, function () use ($package) {
                $path = $this->getConfig('path') . 'application/modules/' . $package . '/';
                $manifestFile = $path . 'settings/manifest.php';

                if (!file_exists($manifestFile)) {
                    return false;
                }

                $manifest = require($manifestFile);
                $temp = $this->getTempDir() . 'export-module-' . $package . '/';
                if (is_dir($temp)) {
                    $this->exec('rm -rf ' . $temp);
                }
                mkdir($temp);

                list($major, $minor, $build) = explode('.', $manifest['package']['version']);
                /*
                $minor++;
                $build = 0;
                */
                $manifest['package']['version'] = implode('.', [$major, $minor, $build]);

                $skip = [];
                $distFile = $this->getConfig('path') . '.dist-ignore';
                if (file_exists($distFile)) {
                    $skip = array_map('trim', explode("\n", trim(file_get_contents($distFile))));
                }

                $gitPath = $path . '.git/';
                if (is_dir($gitPath)) {
                    $exec = 'cd ' . $this->getConfig('path') . ' && ';
                    $exec .= $this->getBin('git') . ' ls-tree --full-tree --name-only -r HEAD';
                    $files = $this->exec($exec);
                    $files = explode("\n", $files);
                } else {
                    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                        if ($file instanceof \SplFileInfo) {
                            if ($file->isDir()) {
                                continue;
                            }

                            $dir = str_replace($this->getConfig('path'), '', $temp . $file->getPath());
                            if (!is_dir($dir)) {
                                mkdir($dir, 0777, true);
                            }

                            copy($file->getRealPath(), $dir . '/' . $file->getFilename());
                        }
                    }
                }

                if (isset($manifest['package']['files'])) {
                    foreach ($manifest['package']['files'] as $file) {
                        if (empty($file) || substr($file, 0, 1) == '.' || in_array($file, $skip)) {
                            continue;
                        }

                        $info = new \SplFileInfo($temp . $file);

                        mkdir($info->getPath(), 0777, true);

                        copy($this->getConfig('path') . $file, $temp . $file);
                    }
                }

                $enginePackage = new \Engine_Package_Manifest_Entity_Package($manifest['package'], [
                    'path' => 'application/modules/' . $package . '/',
                    'basePath' => $this->getConfig('path')
                ]);

                file_put_contents($temp . 'package.json', json_encode($enginePackage->toArray(), JSON_PRETTY_PRINT));

                $name = 'module-' . $manifest['package']['name'] . '-' . $manifest['package']['version'] . '.json';
                $tarName = str_replace('.json', '.tar', $name);
                mkdir($temp . 'application/packages/');

                copy($temp . 'package.json', $temp . 'application/packages/' . $name);

                $this->exec('cd ' . $temp . ' &&  tar -zcf ../' . $tarName . ' .');
                $this->exec('rm -rf ' . $temp);

                return 'Exported to: ' . $this->getTempDir() . $tarName;
            });

            if ($response) {
                $exported[] = $response;
            }
        }

        if (empty($name)) {
            $tarName = 'social-engine-php-upgrade.tar';
            $this->exec('cd ' . $this->getTempDir() . ' &&  tar -zcf ../' . $tarName . ' .');
            copy($this->getTempDir() . '../' . $tarName, $this->getTempDir() . $tarName);
            unlink($this->getTempDir() . '../' . $tarName);
            $exported[] = $this->getTempDir() . $tarName;
        }

        $this->writeResults($exported);
    }
}
