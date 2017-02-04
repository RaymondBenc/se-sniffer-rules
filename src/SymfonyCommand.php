<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SymfonyCommand
 *
 * Creates a facade class to connect to Symfony Command
 *
 * @package SocialEngine\Console
 * @see Symfony\Component\Console\Command\Command
 */
class SymfonyCommand extends BaseCommand
{
    /**
     * @var InputInterface
     */
    public $input;

    /**
     * @var OutputInterface
     */
    public $output;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * Parent class name
     *
     * @var string
     */
    private $name;

    private $method;

    /**
     * SymfonyCommand constructor.
     * @param null|string $name
     * @param $method
     */
    public function __construct($name, $method)
    {
        $this->name = $name;
        $this->method = $method;

        parent::__construct($name);
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Creating configuration based on doc comments for each method.
     */
    protected function configure()
    {
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path to SE.', null);
        $this->addOption('v', null, InputOption::VALUE_OPTIONAL, 'Enable verbose');
    }

    /**
     * Execute a command via Symfony and pass along input/output objects.
     *
     * @throws \Exception
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        // $this->output->writeln($this->name . ' -> ' . $this->method);

        /*
        if (!isset($this->command->map[$this->name])) {
            throw new \Exception('Class "' . $this->name . '" is missing from command map.');
        }

        $method = $this->command->map[$this->name];
        */
        $arguments = $input->getArguments();
        if (isset($arguments['command'])) {
            unset($arguments['command']);
        }

        $response = call_user_func_array([$this->command, $this->method], $arguments);

        return $response;
    }
}
