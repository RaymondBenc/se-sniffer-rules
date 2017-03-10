<?php

namespace SocialEngine\Console\Helper\Seed;

use SocialEngine\Console\Command;
use SocialEngine\Console\Helper\BaseCommand;
use SocialEngine\Console\Helper\Db;

use Zend_Db_Table;

/**
 * Class Config Helper
 *
 * @package SocialEngine\Console\Helper
 */
class User extends BaseCommand
{
    private $db;

    public function __construct(Command $command)
    {
        parent::__construct($command);

        $this->db = new Db($command);
    }

    public function make($values)
    {
        $adapter = $this->db->factory;

        $settingsTable = new Zend_Db_Table(array(
            'db' => $adapter,
            'name' => 'engine4_core_settings',
        ));

        $usersTable = new Zend_Db_Table(array(
            'db' => $adapter,
            'name' => 'engine4_users',
        ));

        $levelTable = new Zend_Db_Table(array(
            'db' => $adapter,
            'name' => 'engine4_authorization_levels',
        ));

        $superAdminLevel = $levelTable->fetchRow($levelTable->select()->where('flag = ?', 'superadmin'));
        if (is_object($superAdminLevel)) {
            $superAdminLevel = $superAdminLevel->level_id;
        } else {
            $superAdminLevel = 1;
        }

        $staticSalt = $settingsTable->find('core.secret')->current();
        if (is_object($staticSalt)) {
            $staticSalt = $staticSalt->value;
        } elseif (!is_string($staticSalt)) {
            $staticSalt = '';
        }

        $values['salt'] = (string) rand(1000000, 9999999);
        $values['password'] = md5($staticSalt . $values['password'] . $values['salt']);
        $values['level_id'] = $superAdminLevel;
        $values['enabled'] = 1;
        $values['verified'] = 1;
        $values['creation_date'] = date('Y-m-d H:i:s');
        $values['creation_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
        $values['displayname'] = $values['username'];

        try {
            $row = $usersTable->createRow();
            $row->setFromArray($values);
            $row->save();
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
}
