<?php

namespace Environaut\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Displays information about Environaut.
 */
class AboutCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setName('about');
        $this->setDescription('Information about Environaut.');
        $this->setHelp('Displays detailed information about Environaut.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
<<<EOT

<info>Environaut</info> should enable and help developers to define the environment of an application
and check if all defined constraints are met. This includes assertions and requirements
of the application environment as well as some configuration that may be necessary to
make an application run.

<info>environaut</info> <comment>[ɪnˌvaɪrənˈaut]</comment>, noun
1. Advocacy for or work toward protecting the application runtime environment from
   destruction or pollution.
2. (Psychology) an adherent of environmentalism
3. (Software Sciences & Allied Applications / Environmental Science / Information Technology)
   a library that is concerned with the maintenance of ecological balance and the conservation
   of the application environment.
4. (Software Sciences & Allied Applications / Environmental Science / Information Technology)
   a software component concerned with issues that affect the application runtime environment,
   such as pollution of environment variables or extinct links to other programs and applications.

<comment>Environaut is an environment checker and configurator for your applications.
See http://github.com/graste/environaut/ for more information.</comment>

EOT
        );
    }
}
