<?php

namespace Environaut\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for environaut commands.
 */
abstract class Command extends BaseCommand
{
    protected $input;
    protected $output;

    protected function configure()
    {
        parent::configure();

        $this->addOption('bootstrap', 'b', InputArgument::OPTIONAL, 'Path to bootstrap file that should be required prior start.');
        $this->addOption('include_path', 'i', InputArgument::OPTIONAL, 'Path that should be prepended to the default PHP include_path.');
        $this->addOption('autoload_dir', 'a', InputArgument::OPTIONAL, 'Path from where to load custom classes specified in config etc.');
    }


    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->input = $input;
        $this->output = $output;

        // preped include_path if demanded
        $path = $input->getOption('include_path');
        if (!empty($path)) {
            ini_set('include_path', $path . PATH_SEPARATOR . ini_get('include_path'));
        }

        // run given bootstrap file if necessary
        $bootstrap_path = $input->getOption('bootstrap');
        if (!empty($bootstrap_path)) {
            if (!is_readable($bootstrap_path)) {
                throw new \InvalidArgumentException('Bootstrap file "' . $bootstrap_path . '" is not readable.');
            }

            if ($this->input->getOption('verbose')) {
                $output->writeln('<comment>Requiring boostrap file from "' . $bootstrap_path . '".</comment>');
            }
            require $bootstrap_path;
        }

        // we autoload classes from the current working directory or the specified autoload_dir
        $autoload_dir = $input->getOption('autoload_dir');
        if (!empty($autoload_dir)) {
            if (!is_readable($autoload_dir))
            {
                throw new \InvalidArgumentException('Autoload path "' . $autoload_dir . '" is not readable. Please specify an existing directory.');
            }

            if ($this->input->getOption('verbose')) {
                $output->writeln('<comment>Classes will be autoloaded from "' . $autoload_dir . '".</comment>');
            }
        } else {
            if ($this->input->getOption('verbose')) {
                $output->writeln('<comment>No autoload_dir specified, using "' . $this->getCurrentWorkingDirectory() . '" to autoload classes from.</comment>');
            }
        }

        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Autoloads the given class if it exists.
     *
     * @param string $class class name like 'Foo\Bar\Baz'
     */
    protected function autoload($class)
    {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $autoload_dir = $this->getInput()->getOption('autoload_dir');
        if (empty($autoload_dir))
        {
            $autoload_dir = $this->getCurrentWorkingDirectory();
        }

        $file_path = $autoload_dir . DIRECTORY_SEPARATOR . $class . '.php';

        if ($this->input->getOption('verbose')) {
            $this->output->write('<info>Autoloading</info>: ');
        }

        if (file_exists($file_path)) {
            $this->output->writeln($file_path);
            include_once($file_path);
        } else {
            $this->output->writeln('<error>File "' . $file_path . '" not found!</error>' . PHP_EOL);
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
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

    /**
     * @return string current working directory
     */
    protected function getCurrentWorkingDirectory()
    {
        $dir = getcwd();

        if (false === $dir) {
            $dir = __DIR__;
        }

        return $dir;
    }
}
