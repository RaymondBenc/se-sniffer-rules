<?php

namespace SocialEngine\Console;

class SymfonyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test all commands that they load correctly
     */
    public function testCommandCreation()
    {
        foreach ($this->getCommands() as $command => $object) {
            $this->assertInstanceOf('\SocialEngine\Console\Command', $object);
        }
    }

    /**
     * Test all commands to make sure methods exist
     */
    public function testCommandMethods()
    {
        foreach ($this->getCommands() as $command => $object) {
            list(, $method) = explode('->', $command);
            $this->assertTrue(method_exists($object, $method));
        }
    }

    /**
     * Array of all commands
     *
     * @return array
     */
    private function getCommands()
    {
        $configFile = __DIR__ . '/../.config.json';
        $config = [];

        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        }
        $console = new Console($config);

        return $console->getCommands();
    }
}
