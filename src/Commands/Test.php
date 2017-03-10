<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;
use SocialEngine\Console\Helper\Seed\User;
use Faker\Factory;

use Engine_Api;

/**
 * SE Seed
 */
class Test extends Command
{
    /**
     * @cli-command test:email
     * @cli-argument email
     * @cli-info Test email
     *
     */
    public function email($email)
    {
        if (empty($email)) {
            throw new Exception\Command('Provide an email to send to.');
        }

        $mail = new \Zend_Mail();
        $mailConfig = require(APPLICATION_PATH . '/application/settings/mail.php');
        $args = (!empty($mailConfig['args']) ? $mailConfig['args'] : []);
        $r = new \ReflectionClass($mailConfig['class']);
        $transport = $r->newInstanceArgs($args);
        $mail->addTo($email)
            ->setSubject('Hello World!')
            ->setBodyText('Mail is working')
            ->setFrom('noreply@socialengine.com')
            ->send($transport);

        $this->writeResults([
            'Successfully sent email!',
            'Class: ' . $mailConfig['class']
        ]);
    }
}
