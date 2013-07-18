<?php

namespace Environaut\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Displays information about Environaut.
 */
class AboutCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setName('about');
        $this->setDescription('Information about Environaut.');
        $this->setHelp('Displays detailed information about Environaut.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(<<<EOT

<info>Environaut - Environment checker for PHP applications.</info>

<comment>Environaut is an environment checker and configurator for your applications.
See http://github.com/graste/environaut/ for more information.</comment>

EOT
        );
    }
}

