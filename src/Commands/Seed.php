<?php

namespace SocialEngine\Console\Commands;

use SocialEngine\Console\Command;
use SocialEngine\Console\Exception;
use SocialEngine\Console\Helper\Seed\User;
use Faker\Factory;

/**
 * SE Seed
 */
class Seed extends Command
{
    /**
     * @throws Exception\Command If missing version number
     *
     * @cli-command seed
     * @cli-info Seeds an SE site
     *
     */
    public function process()
    {
        $user = new User($this);

        $this->step('Seeding users...', function () use ($user) {
            $faker = Factory::create();

            for ($i = 0; $i < 10; $i++) {
                // $this->write($faker->image())

                $userId = $user->make([
                    'username' => $faker->userName,
                    'email' => $faker->email,
                    'password' => '123456'
                ]);

                $this->write($faker->userName);
            }

            $this->write('foo');
        });
    }
}
