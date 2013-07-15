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

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->doExecute();
    }

    /**
     * Executes the current command.
     */
    protected function doExecute()
    {
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return \Symfony\Component\Console\Helper\DialogHelper
     */
    public function getDialogHelper()
    {
        return $this->getHelper('dialog');
    }
}
