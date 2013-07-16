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
        parent::configure();

        $this->setName('check');
        $this->addOption('config', 'c', InputArgument::OPTIONAL, 'Path to config file that defines the checks to process.');
        $this->setDescription('Check environment according to a set of checks.');
        $this->setHelp(<<<EOT
<info>php environaut check</info>
EOT
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Environment Check</info>');
        $output->writeln('=================' . PHP_EOL);

        $output->writeln('<info>Loaded php.ini File</info>: ' . php_ini_loaded_file() . PHP_EOL);
        if ($input->getOption('verbose')) {
            $output->writeln('<info>Additionally Scanned Files</info>: ' . php_ini_scanned_files());
            $output->writeln('<info>PHP Include Path</info>: ' . ini_get('include_path') . PHP_EOL);
        }
        $output->writeln('<info>Environaut Config</info>: ' . $this->config_path . PHP_EOL);

        $checks = $this->getChecksFromConfig();
        $checker = new CheckRunner($checks, $this);
        $checker->run();

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Report follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $report = $checker->getReport();
        $console_report = $report->getFormatted();
        $output->writeln($console_report);

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Config follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $settings = $report->getSettings();
        $output->writeln(json_encode($settings));

        $output->writeln('');
        $output->writeln('<info>Done.</info>');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $config = $input->getOption('config');
        if (!empty($config)) {
            if (!is_readable($config))
            {
                throw new \InvalidArgumentException('Config file "' . $config . '" is not readable.');
            }
            $this->config_path = $config;
        }

        parent::initialize($input, $output);
    }

    protected function getChecksFromConfig()
    {
        $base_href_params = array(
            'name' => 'base_href',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'base_href',
            'question' => 'Wie lautet der BaseHref?',
            'default_value' => 'http://honeybee-showcase.dev/',
            'autocomplete_values' => array('http://cms.honeybee-showcase.dev/', 'http://google.de/', 'http://heise.de/'),
            'validator' => 'Environaut\Checks\Validator::validUrl',
            //'validator' => 'Foo\Validator::validUrl',
            'max_attempts' => 5
        );

        $simple_string_params = array(
            'name' => 'trololo',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'contact.name',
            'introduction' => "Trololo is a video of the nationally-honored Russian singer Eduard Khil (AKA Edward Khill, Edward Hill) performing the Soviet-era pop song “I am Glad, ‘cause I’m Finally Returning Back Home” (Russian: Я очень рад, ведь я, наконец, возвращаюсь домой). The video is often used as a bait-and-switch prank, in similar vein to the practice of Rickrolling.\n\nSource: http://knowyourmeme.com/memes/trololo-russian-rickroll\n\n",
            'question' => 'Wie lautet der Vorname des Trololo Manns?',
            'autocomplete_values' => array('Mr.', 'Eduard', 'Edward', 'omgomgomg'),
        );

        $simple_email_params = array(
            'name' => 'contact',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'contact.email',
            'question' => 'Wie lautet seine Emailadresse?',
            'autocomplete_values' => array('mr.trololo@example.com'),
            'validator' => 'Environaut\Checks\Validator::validEmail',
            'max_attempts' => 5
        );

        $password_params = array(
            'name' => 'password',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'super_secret_password',
            'question' => 'Wie lautet das geheime Passwort?',
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

        $test2 = new $simple_email_params['class']($simple_email_params['name'], $simple_email_params);
        $test2->setCommand($this);
        $checks[] = $test2;

        $testw = new $password_params['class']($password_params['name'], $password_params);
        $testw->setCommand($this);
        $checks[] = $testw;

        return $checks;
    }
}
