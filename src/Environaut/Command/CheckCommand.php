<?php

namespace Environaut\Command;

use Environaut\Runner\CheckRunner;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Just a quick check command.
 */
class CheckCommand extends Command
{
    protected $config_path = 'environaut.xml';

    protected function configure()
    {
        $this->setName('check');

        $this->addOption('config', 'c', InputArgument::OPTIONAL, 'Path to config file that defines the checks to process.');
        $this->addOption('bootstrap', 'b', InputArgument::OPTIONAL, 'Path to bootstrap file that may define autoloads and include paths etc.');
        $this->addOption('include_path', 'i', InputArgument::OPTIONAL, 'Path that should be added to the default PHP include_path.');

        $this->setDescription('Check environment according to a set of checks.');
        $this->setHelp(<<<EOT
<info>php environaut check</info>
EOT
        );
    }

    protected function doExecute()
    {
        $this->output->writeln('<info>Environment Check</info>');
        $this->output->writeln('=================' . PHP_EOL);

        $this->output->writeln('<info>Loaded php.ini File</info>: ' . php_ini_loaded_file() . PHP_EOL);
        if ($this->getInput()->getOption('verbose')) {
            $this->output->writeln('<info>Additionally Scanned Files</info>: ' . php_ini_scanned_files());
            $this->output->writeln('<info>PHP Include Path</info>: ' . ini_get('include_path') . PHP_EOL);
        }
        $this->output->writeln('<info>Environaut Config</info>: ' . $this->config_path . PHP_EOL);

        $checks = $this->getChecksFromConfig();
        $checker = new CheckRunner($checks, $this);
        $checker->run();

        $this->output->writeln('');
        $this->output->writeln('---------------------');
        $this->output->writeln('-- Report follows: --');
        $this->output->writeln('---------------------');
        $this->output->writeln('');

        $report = $checker->getReport();
        $console_report = $report->getFormatted();
        $this->output->writeln($console_report);

        $this->output->writeln('');
        $this->output->writeln('---------------------');
        $this->output->writeln('-- Config follows: --');
        $this->output->writeln('---------------------');
        $this->output->writeln('');

        $settings = $report->getSettings();
        $this->output->writeln(json_encode($settings));

        $this->output->writeln('');
        $this->output->writeln('<info>Done.</info>');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('include_path');
        if (!empty($path)) {
            ini_set('include_path', $path . PATH_SEPARATOR . ini_get('include_path'));
        }

        $bootstrap_path = $input->getOption('bootstrap');
        if (!empty($bootstrap_path)) {
            if (!is_readable($bootstrap_path)) {
                throw new \InvalidArgumentException('Bootstrap file "' . $bootstrap_path . '" is not readable.');
            }

            require $bootstrap_path;
        }

        $config = $input->getOption('config');
        if (!empty($config)) {
            if (!is_readable($config))
            {
                throw new \InvalidArgumentException('Config file "' . $config . '" is not readable.');
            }
            $this->config_path = $config;
        }
    }

    protected function getChecksFromConfig()
    {
        $base_href_params = array(
            'name' => 'base_href',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'base_href',
            'setting_question' => 'Wie lautet der BaseHref?',
            'setting_default_value' => 'http://honeybee-showcase.dev/',
            'setting_autocomplete_values' => array('http://cms.honeybee-showcase.dev/', 'http://google.de/', 'http://heise.de/'),
            'setting_validator' => 'Validator',
        );

        $simple_string_params = array(
            'name' => 'trololo',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'trololo',
            'setting_question' => 'Wie lautet der Vorname des Trololo Manns?',
            'setting_autocomplete_values' => array('Mr.', 'Herr', 'omgomgomg'),
        );

        $password_params = array(
            'name' => 'password',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'super_secret_password',
            'setting_question' => 'Wie lautet das geheime Passwort?',
            'hidden' => true,
            'allow_fallback' => true,
        );

        $checks = array();

        $test = new $base_href_params['class']($base_href_params['name'], $base_href_params);
        $test->setCommand($this);
        $checks[] = $test;

        $test1 = new $simple_string_params['class']($simple_string_params['name'], $simple_string_params);
        $test1->setCommand($this);
        $checks[] = $test1;

        $testw = new $password_params['class']($password_params['name'], $password_params);
        $testw->setCommand($this);
        $checks[] = $testw;

        return $checks;
    }
}
