<?php

namespace Environaut\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for environaut commands.
 */
abstract class Command extends BaseCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Defines default options for all commands.
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'bootstrap',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Path to bootstrap file that should be required prior start.'
        );

        $this->addOption(
            'include-path',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Path that should be prepended to the default PHP include_path.'
        );

        $this->addOption(
            'autoload-dir',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Path from where to load custom classes specified in config etc.'
        );
    }

    /**
     * Handles the default options all commands have in common.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \InvalidArgumentException in case of configuration errors
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->input = $input;
        $this->output = $output;

        // prepend include_path if demanded
        $path = $input->getOption('include-path');
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
        $autoload_dir = $input->getOption('autoload-dir');
        if (!empty($autoload_dir)) {
            if (!is_readable($autoload_dir)) {
                throw new \InvalidArgumentException(
                    'Autoload path "' . $autoload_dir . '" is not readable. Please specify an existing directory.'
                );
            }

            if ($this->input->getOption('verbose')) {
                $output->writeln('<comment>Classes will be autoloaded from "' . $autoload_dir . '".</comment>');
            }
        } else {
            if ($this->input->getOption('verbose')) {
                $output->writeln(
                    '<comment>No autoload_dir specified, using "' . $this->getCurrentWorkingDirectory() .
                    '" to autoload classes from.</comment>'
                );
            }
        }

        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Autoloads the given class if it exists.
     *
     * @param string $class class name like 'Foo\Bar\Baz'
     *
     * @throws \InvalidArgumentException in case of errors (file
     */
    protected function autoload($class)
    {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $autoload_dir = $this->getInput()->getOption('autoload-dir');
        if (empty($autoload_dir)) {
            $autoload_dir = $this->getCurrentWorkingDirectory();
        }

        $file_path = $autoload_dir . DIRECTORY_SEPARATOR . $class . '.php';

        if ($this->input->getOption('verbose')) {
            $this->output->write('<info>Autoloading</info>: ');
        }

        if (is_readable($file_path)) {
            if ($this->input->getOption('verbose')) {
                $this->output->writeln($file_path);
            }

            include_once($file_path);
        } else {
            $this->output->writeln('<error>Autoload error: "' . $file_path . '" not found!</error>' . PHP_EOL);
            // don't exit here to let other autoloaders get their chance
            //throw new \InvalidArgumentException('Could not include unreadable file: ' . $file_path);
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
            $dir = __DIR__; // fallback to folder of current class in case of strange errors
        }

        return $dir;
    }
}
