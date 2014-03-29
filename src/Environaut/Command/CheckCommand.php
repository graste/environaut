<?php

namespace Environaut\Command;

use Environaut\Cache\ICache;
use Environaut\Cache\IReadOnlyCache;
use Environaut\Cache\ReadOnlyCache;
use Environaut\Command\Command;
use Environaut\Config\Parameters;
use Environaut\Runner\IRunner;
use Environaut\Export\IExport;
use Symfony\Component\Console\Input\InputOption;
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
     * @var \Environaut\Cache\IReadOnlyCache
     */
    protected $readonly_cache;

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
            InputOption::VALUE_OPTIONAL,
            'Path to config file that defines the checks to process.'
        );

        $this->addOption(
            'config-handler',
            null,
            InputOption::VALUE_OPTIONAL,
            'Classname of a custom config handler implementing Environaut\Config\IConfigHandler.'
        );

        $this->addOption(
            'cache-location',
            null,
            InputOption::VALUE_OPTIONAL,
            'File path and name for the cache location to read from. Overrides defaults and config file value.'
        );

        $this->addOption(
            'no-cache',
            null,
            InputOption::VALUE_NONE,
            'Disables the use of caching (reading/writing). Remember, that the old cache file still survives!'
        );

        $this->setDescription('Check environment according to a set of checks.');

        $this->setHelp(
<<<EOT

<info>This command checks the environment according to the checks from the configuration file.</info>

By default the <comment>current working directory</comment> will be used to find the configuration file and
all defined classes (checks and validators etc.).

Use
<comment>--autoload-dir [path/to/src]</comment> to change the autoload directory or
<comment>--config [path/to/environaut.(json|xml|php)]</comment> to set the configuration file lookup path or
<comment>--no-cache</comment> to disable to usage of a cache file.
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

        if (!$runner instanceof IRunner) {
            throw new \InvalidArgumentException('The given runner "' . $runner_impl . '" must implement IRunner.');
        }

        $runner->setConfig($this->config);
        $runner->setCommand($this);
        $runner->setParameters(new Parameters($this->config->get('runner', array())));
        $runner->setCache($this->getLoadedCache());

        $run_was_successful = $runner->run();

        $this->report = $runner->getReport();

        $this->writeCache($run_was_successful);
    }

    /**
     * Runs the export with the collected report from the checks.
     */
    protected function runExport()
    {
        $export_impl = $this->config->getExportImplementor();

        $exporter = new $export_impl();

        if (!$exporter instanceof IExport) {
            throw new \InvalidArgumentException('The given exporter "' . $export_impl . '" must implement IExport.');
        }

        $exporter->setCommand($this);
        $exporter->setReport($this->report);
        $exporter->setParameters(new Parameters($this->config->get('export', array())));

        $exporter->run();
    }

    /**
     * Write the cachable settings from the run checks to a cache file for later reuse.
     *
     * @todo should cache only be written if all checks succeeded?
     *
     * @param boolean $run_was_successful whether or not the checks ran successfully
     */
    protected function writeCache($run_was_successful)
    {
        $disable_caching = $this->input->getOption('no-cache');
        if (!empty($disable_caching)) {
            return;
        }

        $cache_implementor = $this->config->getCacheImplementor();
        $cache = new $cache_implementor();

        if (!$cache instanceof ICache) {
            throw new \InvalidArgumentException(
                'The given cache class "' . $cache_implementor . '" does not implement ICache.'
            );
        }

        $cache_params = new Parameters($this->config->get('cache', array()));
        $cache->setParameters($cache_params);

        // TODO use something like getenv('ENVIRONAUT_CACHE_DIR')?
        $cache_location = $this->input->getOption('cache-location');
        if (!empty($cache_location)) {
            $cache->setLocation($cache_location);
        }

        $cache->addAll(
            $this->report->getCachableSettings() // gets only cachable settings from successful checks
        );

        if ($cache->save()) {
            $this->output->writeln(
                PHP_EOL . 'Writing cachable settings to "<comment>' . $cache->getLocation() .
                '</comment>" for subsequent runs...<info>ok</info>.' . PHP_EOL
            );
        } else {
            $this->output->writeln(
                PHP_EOL . 'Writing cachable settings to "<comment>' . $cache->getLocation() .
                '</comment>" for subsequent runs...<error>FAILED</error>.' . PHP_EOL
            );
        }
    }

    /**
     * Creates a cache instance and tries to load it according to command line argument or config.
     *
     * The cache is used in the runner to allow checks to access old values that may already have
     * been configured and thus improve their flow and e.g. not ask values again or just confirm them.
     *
     * @return IReadOnlyCache cache instance
     */
    protected function getLoadedCache()
    {
        if ($this->input->getOption('no-cache')) {
            $this->readonly_cache = new ReadOnlyCache();
            return $this->readonly_cache;
        }

        $cache_implementor = $this->config->getReadOnlyCacheImplementor();
        $cache = new $cache_implementor();

        if (!$cache instanceof IReadOnlyCache) {
            throw new \InvalidArgumentException(
                'The given cache class "' . $cache_implementor . '" does not implement IReadOnlyCache.'
            );
        }

        $cache_params = new Parameters($this->config->get('cache', array()));
        $cache_location = $this->input->getOption('cache-location');

        $this->readonly_cache = $cache;
        $this->readonly_cache->setParameters($cache_params);
        if (!empty($cache_location)) {
            if (is_readable($cache_location)) {
                $this->readonly_cache->setLocation($cache_location);
            } else {
                $this->output->writeln(
                    '<comment>Given cache location could not be read. ' .
                    'Using fallback from config or default location.</comment>'
                );
            }
        }

        $this->readonly_cache->load();

        $this->output->writeln(
            '<info>Loaded <comment>' . count($cache->getAll()) . '</comment> cached settings from:</info> ' .
            $cache->getLocation()
        );
        $this->output->writeln('');

        return $this->readonly_cache;
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

        $config_handler_implementor = $input->getOption('config-handler');
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
