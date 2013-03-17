<?php

namespace Environaut\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Gransow <graste@mivesto.de>
 */
class AboutCommand extends Command
{
    protected function configure()
    {
        $this->setName('about')
            ->setDescription('Information about environaut.')
            ->setHelp(<<<EOT
<info>php environaut about</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $colors = array('red', 'blue', 'yellow');
        $color = $dialog->select(
                $output,
                'Please select your favorite color (default to red)',
                $colors,
                0
        );
        $output->writeln('You have just selected: ' . $colors[$color]);

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, 50);
        $i = 0;
        while ($i++ < 50) {
            usleep(15000);
            $progress->advance();
        }
        $progress->finish();

        $output->writeln(<<<EOT

<info>Environaut - Environment checker for PHP applications.</info>
<comment>Environaut is an environment checker and configurator for your applications.
See http://github.com/graste/environaut/ for more information.</comment>

EOT
        );
    }
}

