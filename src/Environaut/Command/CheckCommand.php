<?php

namespace Environaut\Command;

use Environaut\Runner\CheckRunner;
use Environaut\Config\Reader\PhpConfigReader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Just a quick check command.
 */
class CheckCommand extends Command
{
    protected $config_path = 'environaut.json';

    protected function configure()
    {
        parent::configure();

        $this->setName('check');
        $this->addOption('config', 'c', InputArgument::OPTIONAL, 'Path to config file that defines the checks to process.');
        $this->addOption('config_handler', null, InputArgument::OPTIONAL, 'Classname of a custom config handler implementing Environaut\Config\IConfigHandler.');
        $this->setDescription('Check environment according to a set of checks.');
        $this->setHelp(<<<EOT

<info>This command checks the environment according to the checks from the configuration file.</info>

By default the current working directory will be used to find the configuration file and
all defined classes (checks and validators). Use <comment>--autoload_dir path/to/src</comment> to change the
autoload directory or <comment>--config path/to/environaut.json</comment> to change the file lookup path.
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

        if ($input->getOption('verbose')) {
            $output->writeln('<info>PHP Version</info>: ' . PHP_VERSION . ' on ' . PHP_OS . ' (installed to ' . PHP_BINDIR . ')');
            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                $output->writeln('<info>PHP Binary</info>: ' . PHP_BINARY);
            }
            $output->writeln('<info>User owning this script</info>: ' . get_current_user() . ' (uid=' . getmyuid() . ')');
            $output->writeln('<info>User running this script</info>: uid=' . posix_getuid() . ' (effective uid=' . posix_geteuid() . ') gid=' . posix_getgid() . ' (effective gid=' . posix_getegid() . ')' . PHP_EOL);
            $output->writeln('<info>Loaded php.ini File</info>: ' . php_ini_loaded_file());
            $output->writeln('<info>Additionally Scanned Files</info>: ' . php_ini_scanned_files());
            $output->writeln('<info>PHP Include Path</info>: ' . ini_get('include_path') . PHP_EOL);

        }

        $output->writeln('<info>Environaut Config</info>: ' . $this->config_path . PHP_EOL);

        $config_handler_implementor = $input->getOption('config_handler');
        if (empty($config_handler_implementor)) {
            $config_handler_implementor = 'Environaut\Config\DefaultConfigHandler';
        }
        $config_reader = new $config_handler_implementor();
        $config_reader->addLocation($this->config_path);
        //$config_reader->setLocations(array($this->config_path));

        $config = $config_reader->getConfig();
        $runner = $config->getRunnerImplementor();
        $runner = new $runner($config, $this);
        $runner->run();
        $report = $runner->getReport();

//        $exporter = $config->getExporterImplementor();
//        $exporter = new DefaultExporter();
//        $exporter->setCommand($this);
//
//        $success = $report->export($exporter);

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Report follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $console_report_text = $report->getFormatted();
        $output->writeln($console_report_text);

        $output->writeln('');
        $output->writeln('---------------------');
        $output->writeln('-- Config follows: --');
        $output->writeln('---------------------');
        $output->writeln('');

        $output->writeln(json_encode($report->getSettings()));

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
                throw new \InvalidArgumentException('Config file path "' . $config . '" is not readable.');
            }

            $this->config_path = $config;

            if ($input->getOption('verbose')) {
                $output->writeln('<comment>Config file path specified: ' . $this->config_path . '</comment>');
            }
        }
        else {
            $this->config_path = $this->getCurrentWorkingDirectory();

            if ($input->getOption('verbose')) {
                $output->writeln('<comment>Config file path not specified, using "' . $this->config_path . '" as default lookup location.</comment>');
            }
        }

        parent::initialize($input, $output);
    }
}
