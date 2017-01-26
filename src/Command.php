<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class Command
 *
 * All comments must extend this abstract class.
 *
 * @package SocialEngine\Console
 */
abstract class Command
{
    /**
     * @var SymfonyCommand
     */
    private $symfony;

    /**
     * Local bin paths
     *
     * @var array
     */
    private $bin = [
        'git' => 'git',
        'php' => 'php',
        'phpcs' => 'phpcs'
    ];

    /**
     * Command constructor.
     *
     * @param string $name Class name
     * @param \ReflectionClass $reflection
     */
    public function __construct($name, \ReflectionClass $reflection)
    {
        $this->symfony = new SymfonyCommand($name, $reflection, $this);
    }

    /**
     * Will return the value of an argument that was set via $this->arguments.
     *
     * @param string $key Name of the argument.
     * @return mixed
     */
    protected function get($key)
    {
        return $this->symfony->input->getOption($key);
    }

    /**
     * Ask a question to the user.
     *
     * @param string $question Question to ask
     * @return string Users answer
     */
    protected function ask($question)
    {
        $helper = $this->symfony->getHelper('question');
        if ($helper instanceof QuestionHelper) {
            return $helper->ask($this->symfony->input, $this->symfony->output, new Question($question));
        }
    }

    /**
     * Execute a command.
     *
     * @param string $command
     * @return string
     */
    protected function exec($command)
    {
        if ($this->get('v')) {
            $this->write($command);
        }
        return shell_exec($command);
    }

    /**
     * Execute a git command.
     *
     * @param string $command
     * @return string
     */
    protected function git($command)
    {
        return $this->exec($this->getBin('git') . ' ' . $command);
    }

    /**
     * Attempt to load bin for certain programs, such as PHP or GIT.
     *
     * @see $this->bin
     * @param string $program Program to load
     * @return string
     * @throws \Exception
     */
    protected function getBin($program)
    {
        if (!isset($this->bin[$program])) {
            throw new \Exception('Unable to find the bin: ' . $program);
        }

        if ($program == 'phpcs') {
            return SE_CONSOLE_DIR . 'application/vendor/bin/phpcs';
        }

        return $this->bin[$program];
    }

    /**
     * Write to CLI
     *
     * @param string $string
     */
    public function write($string)
    {
        $this->symfony->output->writeln($string);
    }

    /**
     * Returning Symfony for late binding.
     *
     * @param $name
     * @return null|SymfonyCommand
     */
    public function __get($name)
    {
        if ($name != '__attach') {
            return null;
        }

        return $this->symfony;
    }
}
