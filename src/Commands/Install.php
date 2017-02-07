<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;
use SocialEngine\Console\Helper\Db;

class Install extends Command
{
    /**
     * @cli-command install
     * @cli-info Install a clean copy of Social Engine.
     */
    public function process()
    {
        $base = $this->getConfig('path');

        if (!file_exists($base . 'application/libraries/Engine/Api.php')) {
            throw new Exception\Command('Does not seem like SE resides here.');
        }

        $required = [
            'license-key' => 'What is your Social Engine license key?',
            'db-host' => 'DB host?',
            'db-user' => 'DB username?',
            'db-pass' => 'DB password?'
        ];

        $values = [
            'username' => 'admin',
            'email' => 'admin@socialengine.com',
            'password' => '123456'
        ];

        foreach ($required as $name => $ask) {
            $value = $this->getConfig($name);

            if (empty($value)) {
                $value = $this->ask($ask);
                $this->setConfig($name, $value);
            }
        }

        $name = 'se_' . time();

        (new Reset($this->symfony, $this->config))->process();

        $this->step('Creating database', function () use ($name) {
            $options = $this->getBin('mysql');
            $options .= ' -h ' . $this->getConfig('db-host');
            $options .= ' -u ' . $this->getConfig('db-user');
            $options .= ' --password="' . $this->getConfig('db-pass') . '"';
            $this->exec('echo "CREATE DATABASE ' . $name . '" | ' . $options);
        });

        $this->step('Creating config file', function () use ($name) {
            $configFile = APPLICATION_PATH . 'application/settings/database.php';
            $config = [
                'adapter' => 'mysqli',
                'params' => [
                    'host' => $this->getConfig('db-host'),
                    'username' => $this->getConfig('db-user'),
                    'password' => $this->getConfig('db-pass'),
                    'dbname'   => $name,
                    'charset'  => 'UTF-8',
                    'adapterNamespace' => 'Zend_Db_Adapter'
                ],
                'isDefaultTableAdapter' => true,
                'tablePrefix' => "engine4_",
                'tableAdapterClass' => "Engine_Db_Table",
            ];
            file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
        });

        $db = new Db($this);

        $this->step('Install database tables', function () use ($db) {

            $files = array(
                APPLICATION_PATH . '/application/modules/Core/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Authorization/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Activity/settings/my.sql',
                APPLICATION_PATH . '/application/modules/User/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Messages/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Network/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Invite/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Fields/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Storage/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Announcement/settings/my.sql',
                APPLICATION_PATH . '/application/modules/Payment/settings/my.sql',
            );

            try {
                foreach ($files as $file) {
                    $sql = file_get_contents($file);
                    $queries = \Engine_Package_Utilities::sqlSplit($sql);
                    foreach ($queries as $query) {
                        if (preg_match('/CREATE TABLE IF NOT EXISTS `engine4_(.*?)`/is', $query, $matches)) {
                            $this->write(' -> engine4_' . $matches[1]);
                        }
                        $db->factory->query($query);
                    }
                }
            } catch (\Exception $e) {
                $this->color('red')->write($e->getMessage());
            }
        });

        $this->step('Install packages', function () {
            (new Build($this->symfony, $this->config))->packages();
        });

        $this->step('Setup site', function () use ($db) {
            $settingsTable = new \Zend_Db_Table(array(
                'db' => $db->factory,
                'name' => 'engine4_core_settings',
            ));

            // Generate new secret key
            $row = $settingsTable->find('core.secret')->current();
            if (null === $row) {
                $row = $settingsTable->createRow();
                $row->name = 'core.secret';
            }
            if ($row->value == 'staticSalt' || $row->value == 'NULL' || !$row->value) {
                $row->value = sha1(time() . php_uname() . dirname(__FILE__) . rand(1000000, 9000000));
                $row->save();
            }

            // Save key
            $row = $settingsTable->find('core.license.key')->current();
            if (null === $row) {
                $row = $settingsTable->createRow();
                $row->name = 'core.license.key';
            }
            $row->value = $this->getConfig('license-key');
            $row->save();

            // Save creation date
            $row = $settingsTable->find('core.site.creation')->current();
            if (null === $row) {
                $row = $settingsTable->createRow();
                $row->name = 'core.site.creation';
            }
            $row->value = date('Y-m-d H:i:s');
            $row->save();
        });

        $this->step('Create user', function () use ($db, $values) {

            $settingsTable = new \Zend_Db_Table(array(
                'db' => $db->factory,
                'name' => 'engine4_core_settings',
            ));
            $usersTable = new \Zend_Db_Table(array(
                'db' => $db->factory,
                'name' => 'engine4_users',
            ));
            $levelTable = new \Zend_Db_Table(array(
                'db' => $db->factory,
                'name' => 'engine4_authorization_levels',
            ));

            // Get static salt
            $staticSalt = $settingsTable->find('core.secret')->current();
            if (is_object($staticSalt)) {
                $staticSalt = $staticSalt->value;
            } elseif (!is_string($staticSalt)) {
                $staticSalt = '';
            }

            // Get superadmin level
            $superAdminLevel = $levelTable->fetchRow($levelTable->select()->where('flag = ?', 'superadmin'));
            if (is_object($superAdminLevel)) {
                $superAdminLevel = $superAdminLevel->level_id;
            } else {
                $superAdminLevel = 1;
            }

            // Adjust values
            $values['salt'] = (string) rand(1000000, 9999999);
            $values['password'] = md5($staticSalt . $values['password'] . $values['salt']);
            $values['level_id'] = $superAdminLevel;
            $values['enabled'] = 1;
            $values['verified'] = 1;
            $values['creation_date'] = date('Y-m-d H:i:s');
            $values['creation_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
            $values['displayname'] = $values['username'];

            // Insert
            $row = $usersTable->createRow();
            $row->setFromArray($values);
            $row->save();
        });

        $this->step('Set default theme', function () use ($db) {
            try {
                $themeName = 'clean';
                $themeTable = new \Zend_Db_Table(array(
                    'db' => $db->factory,
                    'name' => 'engine4_core_themes',
                ));
                $themeSelect = $themeTable->select()
                    ->orWhere('theme_id = ?', $themeName)
                    ->orWhere('name = ?', $themeName)
                    ->limit(1);

                $theme = $themeTable->fetchRow($themeSelect);
                $db = $themeTable->getAdapter();
                $db->beginTransaction();

                $themeTable->update(array(
                    'active' => 0,
                ), array(
                    '1 = ?' => 1,
                ));
                $theme->active = true;
                $theme->save();

                $db->commit();
            } catch (\Exception $e) {
                $db->rollBack();
                exit($e->getMessage());
            }
        });

        $results = [
            'Database: ' . $name
        ];

        foreach ($values as $key => $value) {
            $results[] = ucwords($key) . ': ' . $value;
        }

        $this->writeResults($results);
    }
}
