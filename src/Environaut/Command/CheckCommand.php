<?php

namespace Environaut\Command;

use Environaut\Runner\CheckRunner;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Just a quick check command.
 */
class CheckCommand extends Command
{
    protected function configure()
    {
        $this->setName('check');
        $this->setDescription('Check environment according to a set of checks.');
        $this->addArgument('config', InputArgument::OPTIONAL, 'Path to config file that defines the checks to process.');
        $this->setHelp(<<<EOT
<info>php environaut check</info>
EOT
        );
    }

    protected function doExecute()
    {
        $this->output->writeln('Environment Check:');
        $config = $this->input->getArgument('config');
        if (!$config) {
            $config = 'environaut.xml';
        }
        $checks = $this->getChecksFromConfig($config);
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

    protected function getChecksFromConfig($config)
    {
        $base_href_params = array(
            'setting_name' => 'base_href',
            'setting_question' => 'Wie lautet der BaseHref?',
            'setting_default_value' => 'http://honeybee-showcase.dev/',
            'setting_autocomplete_values' => array('http://cms.honeybee-showcase.dev/', 'http://google.de/', 'http://heise.de/'),
        );

        $simple_string_params = array(
            'setting_name' => 'trololo',
            'setting_question' => 'Wie lautet der Vorname des Trololo Manns?',
            'setting_autocomplete_values' => array('Mr.', 'Herr', 'omgomgomg'),
        );

        $password_params = array(
            'setting_name' => 'super_secret_password',
            'setting_question' => 'Wie lautet das geheime Passwort?',
            'hidden' => true,
            'allow_fallback' => true
        );

        $checks = array();
        $test = new \Environaut\Checks\Configurator('base_href', $base_href_params);
        $test->setCommand($this);
        $checks[] = $test;
        $test1 = new \Environaut\Checks\Configurator('trololo', $simple_string_params);
        $test1->setCommand($this);
        $checks[] = $test1;
        $testw = new \Environaut\Checks\Configurator('password', $password_params);
        $testw->setCommand($this);
        $checks[] = $testw;
        return $checks;
    }
}
