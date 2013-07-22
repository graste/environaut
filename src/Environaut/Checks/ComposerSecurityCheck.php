<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

use SensioLabs\Security\SecurityChecker;

/**
 * The SensioLabs Security Checker is a command line tool that checks if your application
 * uses dependencies with known security vulnerabilities. It uses the SensioLabs Security
 * Check Web service and the Security Advisories Database behind the scenes.
 */
class ComposerSecurityCheck extends Check
{
    /**
     * @throws \RuntimeException in case of curl missing, file not found, server not responding etc.
     * @throws \InvalidArgumentException in case of wrong format parameter
     */
    public function run()
    {
        $output = $this->getOutputStream();

        $file = $this->parameters->get('file', 'composer.lock');
        $format = $this->parameters->get('format', 'text');
        $silent = $this->parameters->get('silent', true);

        $checker = new SecurityChecker();

        if (!$silent) {
            $output->write('Checking "' . $file . '" for known security vulnerabilities...');
        }

        try {
            $alerts = $checker->check($file, $format);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failure while running '. __CLASS__ . ': ' . $e->getMessage());
        }

        if (!$silent) {
            $output->writeln('done.');
        }

        if (!$silent && $checker->getLastVulnerabilityCount() > 0) {
            $output->writeln('Number of found known vulnerabilities: ' . $checker->getLastVulnerabilityCount());
        }

        if ($checker->getLastVulnerabilityCount() > 0) {
            $this->addError(
                'Number of found known vulnerabilities after checking "' . $file .
                '": ' . $checker->getLastVulnerabilityCount()
            );
            $this->addError($alerts);
        } else {
            $this->addInfo('No known vulnerabilities found after checking "' . $file . '".');
        }
    }
}
