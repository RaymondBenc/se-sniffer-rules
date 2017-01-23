<?php

namespace SocialEngine\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
   * SymfonyCommand constructor.
   * @param Command $command
   */
  public function __construct(Command $command)
  {
    $this->command = $command;

    parent::__construct();
  }

  /**
   * Creating configuration based on $this->command values,
   * which will get passed along to Symfony Command.
   */
  protected function configure()
  {
    $this->setName($this->command->name);
    $this->setDescription($this->command->description);

    if ($this->command->arguments) {
      foreach ($this->command->arguments as $argument) {
        $this->addArgument($argument);
      }
    }
  }

  /**
   * Execute a command via Symfony and pass along input/output objects.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return bool TRUE on success or FALSE on failure.
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $this->input = $input;

    return $this->command->process();
  }
}
