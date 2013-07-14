<?php

namespace Environaut\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for environaut commands.
 */
abstract class Command extends BaseCommand
{
    protected $input;
    protected $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getInput()
    {
        return $this->input;
    }
}

