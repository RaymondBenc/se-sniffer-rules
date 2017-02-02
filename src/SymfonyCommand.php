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

    /**
     * Map that connects the command to the correct class method.
     *
     * @var array
     */
    private $map = [];

    /**
     * SymfonyCommand constructor.
     *
     * @param string $name
     * @param \ReflectionClass $reflection
     * @param Command $command
     */
    public function __construct($name, \ReflectionClass $reflection, Command $command)
    {
        $this->reflection = $reflection;
        $this->command = $command;
        $this->name = $name;

        parent::__construct();
    }

    /**
     * Creating configuration based on doc comments for each method.
     */
    protected function configure()
    {
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->class == $this->name && $method->getName() != '__construct') {
                $docComments = $method->getDocComment();

                if (empty($docComments)) {
                    throw new \Exception('Missing doc comments for this method: ' .
                        $method->class . '::' . $method->getName());
                }

                $comments = explode("\n", $docComments);
                foreach ($comments as $comment) {
                    $comment = trim(str_replace('*', '', $comment));
                    if (substr($comment, 0, 5) == '@cli-') {
                        $parts = explode(' ', trim(explode('@cli-', $comment)[1]));
                        $name = trim($parts[0]);
                        unset($parts[0]);
                        $data = implode(' ', $parts);

                        switch ($name) {
                            case 'argument':
                                $this->addArgument($data);
                                break;
                            case 'command':
                                $this->setName($data);
                                if (!isset($this->map[$this->name])) {
                                    $this->map[$this->name] = [];
                                }
                                $this->map[$this->name][$data] = $method->getName();
                                break;
                            case 'info':
                                $this->setDescription($data);
                                break;
                        }
                    }
                }
            }
        }

        if (!$this->getName()) {
            throw new \Exception('Missing a name for: ' . $this->name);
        }

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

        if (!isset($this->map[$this->name])) {
            throw new \Exception('Class "' . $this->name . '" is missing from command map.');
        }

        $method = $this->map[$this->name][$this->getName()];
        $arguments = $input->getArguments();
        if (isset($arguments['command'])) {
            unset($arguments['command']);
        }

        $response = call_user_func_array([$this->command, $method], $arguments);

        return $response;
    }
}
