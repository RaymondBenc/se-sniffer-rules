<?php

namespace SocialEngine\Console\Helper;

use SocialEngine\Console\Console;

class DocGenerator
{
    private $data = '';

    public function __construct(Console $console)
    {
        $this->out('# Social Engine Console Commands');
        $this->out('');
        foreach ($console->getCommands() as $command) {
            $this->out('**' . $command->getName() . '**');
            $this->out('');
            $this->out($command->getDescription());
            $this->out('');
            $this->out('');
        }

        file_put_contents(__DIR__ . '/../../docs/Commands.md', $this->data);
        fwrite(STDOUT, "Doc Generation complete!" . PHP_EOL);
        exit;
    }

    private function out($string)
    {
        $this->data .= $string . "\n";
    }
}
