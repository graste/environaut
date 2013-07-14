<?php

namespace Environaut\Command;

use Environaut\Runner\CheckRunner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->writeln('Environment Check:');
        $config = $input->getArgument('config');
        if (!$config) {
            $config = 'environaut.xml';
        }
        $checks = $this->getChecksFromConfig($config);
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
    }

    protected function getChecksFromConfig($config)
    {
        $checks = array();
        $test = new \Environaut\Checks\Configurator('test');
        $test->setCommand($this);
        $checks[] = $test;
        $test1 = new \Environaut\Checks\Configurator('blub', array('keyname' => 'foo'));
        $test1->setCommand($this);
        $checks[] = $test1;
        $testw = new \Environaut\Checks\Configurator('asdf', array('keyname' => 'foo.hahaha'));
        $testw->setCommand($this);
        $checks[] = $testw;
        return $checks;
    }
}

