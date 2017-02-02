<?php

namespace SocialEngine\Console\Helper;

use SocialEngine\Console\Command;

/**
 * Class BaseCommand
 * @package SocialEngine\Console\Helper
 */
abstract class BaseCommand
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * BaseCommand constructor.
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
}
