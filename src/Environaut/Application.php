<?php

namespace Environaut;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Environaut\Command;

/**
 * The console application that handles all
 * supported command line arguments.
 */
class Application extends BaseApplication
{
    public function __construct($version)
    {
        parent::__construct('Environaut', $version);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('--profile')) {
            $start_time = microtime(true);
        }

        $result = parent::doRun($input, $output);

        if (isset($start_time)) {
            $output->writeln(
                PHP_EOL . '<comment>Memory usage: ' . round(memory_get_usage() / 1024 / 1024, 2) .
                'MB (peak: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) .
                'MB), time: ' . round(microtime(true) - $start_time, 2) . 's</comment>' . PHP_EOL
            );
        }

        return $result;
    }

    /**
     * Initializes all necessary commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\AboutCommand();
        $commands[] = new Command\CheckCommand();

        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(
            new InputOption(
                'profile',
                null,
                InputOption::VALUE_NONE,
                'Display timing and memory usage information.'
            )
        );

        return $definition;
    }
}
