<?php

namespace SocialEngine\Console;

class SymfonyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test all commands that they load correctly
     */
    public function testCommandCreation()
    {
        $dir = __DIR__ . '/../src/Commands/';
        foreach (scandir($dir) as $command) {
            if ($command == '.' || $command == '..') {
                continue;
            }

            if (substr($command, -4) == '.php') {
                $command = 'SocialEngine\\Console\\Commands\\' . str_replace('.php', '', $command);
                $ref = new \ReflectionClass($command);
                $object = $ref->newInstanceArgs([$command, $ref]);

                $this->assertInstanceOf('\SocialEngine\Console\Command', $object);
            }
        }
    }
}
