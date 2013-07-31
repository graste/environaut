<?php

namespace Environaut\Command;

use Environaut\Command\Command;
use Environaut\Config\Parameters;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks the environment according to the given configuration,
 * collects results in a report and exports the messages and
 * settings from the report via an exporter.
 */
class CheckCommand extends Command
{
    /**
     * @var \Environaut\Config\IConfig
     */
    protected $config;

    /**
     * @var \Environaut\Report\IReport
     */
    protected $report;

    /**
     * @var string config path from CLI option (or default fallback name)
     */
    protected $config_path;

    /**
     * @var \Environaut\Config\IConfigHandler
     */
    protected $config_handler;

    /**
     * Define options supported by this command and set name, description and help texts.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('check');

        $this->addOption(
            'config',
            'c',
            InputArgument::OPTIONAL,
            'Path to config file that defines the checks to process.'
        );

        $this->addOption(
            'config_handler',
            null,
            InputArgument::OPTIONAL,
            'Classname of a custom config handler implementing Environaut\Config\IConfigHandler.'
        );

        $this->setDescription('Check environment according to a set of checks.');

        $this->setHelp(
<<<EOT

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
        $this->displayIntro();

        $this->readConfig();

        $this->runChecks();

        $this->runExport();

        $this->displayOutro();
    }

    /**
     * Reads configuration via default or given config file handler
     */
    protected function readConfig()
    {
        $this->config = $this->getConfigHandler()->getConfig();

        if ($this->config->has('introduction')) {
            $this->output->writeln($this->config->get('introduction'));
            $this->output->writeln('');
        }
    }

    /**
     * Runs all checks and collects results in a report.
     */
    protected function runChecks()
    {
        $runner_impl = $this->config->getRunnerImplementor();

        $runner = new $runner_impl();
        $runner->setConfig($this->config);
        $runner->setCommand($this);
        $runner->setParameters(new Parameters($this->config->get('runner', array())));

        $runner->run();

        $this->report = $runner->getReport();
    }

    /**
     * Runs the export with the collected report from the checks.
     */
    protected function runExport()
    {
        $export_impl = $this->config->getExportImplementor();

        $exporter = new $export_impl();
        $exporter->setCommand($this);
        $exporter->setReport($this->report);
        $exporter->setParameters(new Parameters($this->config->get('export', array())));

        $exporter->run();
    }

    /**
     * Displays introductory text and some PHP configuration
     * and information (if --verbose was used as CLI option)
     */
    protected function displayIntro()
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $output->writeln('<info>Environment Check</info>');
        $output->writeln('=================' . PHP_EOL);

        if ($input->getOption('verbose')) {
            $output->writeln(
                '<info>PHP Version</info>: ' . PHP_VERSION . ' on ' . PHP_OS .
                ' (installed to ' . PHP_BINDIR . ')'
            );
            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                $output->writeln('<info>PHP Binary</info>: ' . PHP_BINARY);
            }
            $output->writeln(
                '<info>User owning this script</info>: ' . get_current_user() .
                ' (uid=' . getmyuid() . ')'
            );
            $output->writeln(
                '<info>User running this script</info>: uid=' . posix_getuid() .
                ' (effective uid=' . posix_geteuid() . ') gid=' . posix_getgid() .
                ' (effective gid=' . posix_getegid() . ')' . PHP_EOL
            );
            $output->writeln('<info>Loaded php.ini File</info>: ' . php_ini_loaded_file());
            $output->writeln('<info>Additionally Scanned Files</info>: ' . php_ini_scanned_files());
            $output->writeln('<info>PHP Include Path</info>: ' . ini_get('include_path') . PHP_EOL);

        }

        $output->writeln('<info>Reading configuration from</info>: ' . $this->config_path . PHP_EOL);
    }

    /**
     * Display some text after the main command executed.
     */
    protected function displayOutro()
    {
        $output = $this->getOutput();

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
        parent::initialize($input, $output); // necessary to register autoloader

        $config = $input->getOption('config');
        if (!empty($config)) {
            if (!is_readable($config)) {
                throw new \InvalidArgumentException('Config file path "' . $config . '" is not readable.');
            }

            $this->config_path = $config;

            if ($input->getOption('verbose')) {
                $output->writeln('<comment>Config file path specified: ' . $this->config_path . '</comment>');
            }
        } else {
            $this->config_path = $this->getCurrentWorkingDirectory();

            if ($input->getOption('verbose')) {
                $output->writeln(
                    '<comment>Config file path not specified, using "' . $this->config_path .
                    '" as default lookup location.</comment>'
                );
            }
        }

        $config_handler_implementor = $input->getOption('config_handler');
        if (empty($config_handler_implementor)) {
            $config_handler_implementor = 'Environaut\Config\ConfigHandler';
        }

        $this->config_handler = new $config_handler_implementor();
        $this->config_handler->addLocation($this->config_path);

        // TODO allow multiple locations for config option?
        // $config_reader->setLocations(array($this->config_path));
    }

    /**
     * @return IConfigHandler currently used config handler instance
     */
    public function getConfigHandler()
    {
        return $this->config_handler;
    }

    /**
     * @return string path to default config file location
     */
    public function getConfigPath()
    {
        return $this->config_path;
    }
}
