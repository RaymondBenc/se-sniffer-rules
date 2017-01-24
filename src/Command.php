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
   * Name of your command.
   *
   * @see \Symfony\Component\Console\Command\Command::setName()
   * @var string
   */
  public $name;

  /**
   * Information about your command and what it does.
   *
   * @see \Symfony\Component\Console\Command\Command::setDescription()
   * @var string
   */
  public $description;

  /**
   * Additional arguments that can be passed to your command.
   *
   * @see \Symfony\Component\Console\Command\Command::addArgument()
   * @var array
   */
  public $arguments = [];

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

  public function __construct()
  {
    if (empty($this->name)) {
      throw new \Exception('Provide a name for your command: ' . get_called_class());
    }

    $this->symfony = new SymfonyCommand($this);
  }

  /**
   * Your command must override this method.
   * This is where all your CLI code goes.
   *
   * @throws \Exception
   * @return bool
   */
  public function process()
  {
    throw new \Exception('You must override this method.');
  }

  /**
   * Will return the value of an argument that was set via $this->arguments.
   *
   * @param string $key Name of the argument.
   * @return mixed
   */
  protected function get($key)
  {
    return $this->symfony->input->getArgument($key);
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
    return shell_exec($this->getBin('git') . ' ' . $command);
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
  protected function write($string)
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
