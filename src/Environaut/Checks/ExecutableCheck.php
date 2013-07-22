<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

/**
 * Check to get absolute paths to executable files that
 * may be used within an application. As even paths to
 * simple tools like "ls", "find" or "grep" may not be
 * accessible in e.g. crontabs due to non-configured
 * default PATH environment variables they can be searched
 * and configured prior running the application (and thus
 * always use the validated absolute executable paths).
 */
class ExecutableCheck extends Check
{
    public function run()
    {
        $output = $this->getOutputStream();
        $dialog = $this->getDialogHelper();

        $command = $this->parameters->get('command', $this->getName());
        $setting = $this->parameters->get('setting', 'cmd.' . $command);
        $default = $this->parameters->get('default', '/usr/bin/' . $command);
        $choices = $this->parameters->get(
            'choices',
            array(
                '/usr/bin/',
                '/usr/sbin/',
                '/usr/local/sbin/',
                '/usr/local/bin/'
            )
        );
        $validator = $this->parameters->get('validator', array($this, 'validExecutable'));
        $max_attempts = $this->parameters->get('max_attempts', false);

        $question = '<question>' . $this->parameters->get(
            'question',
            'Path to the executable "' . $command . '"'
        );

        // add default value to question if specified
        if (null !== $default) {
            $question .= "</question> (Default: $default)";
        } else {
            $question .=  '</question>';
        }
        $question .= ': ';

        // as for path to executable with validation and autocomplete of common executable directories like /usr/bin
        $absolute_executable_path = $dialog->askAndValidate(
            $output,
            $question,
            $validator,
            $max_attempts,
            $default,
            $choices
        );

        $this->addSetting($setting, $absolute_executable_path);
        $this->addInfo('Got path to executable "' . $command . '": ' . $absolute_executable_path);

        return true;
    }

    /**
     * Checks whether the given input value leads to an executable that
     * matches the configured version_mask parameter.
     *
     * @param string $value path to an executable
     *
     * @return string absolute path to executable
     *
     * @throws \InvalidArgumentException in case of non-existing executable at the given path or version mismatch
     */
    public function validExecutable($value)
    {
        $val = trim($value);

        if (empty($val)) {
            throw new \InvalidArgumentException(
                'Not a valid executable path. Please specify a command (like "ls") or a path (like "/usr/bin/ls").'
            );
        }

        $executable = trim(shell_exec('which ' . $val));
        if (!$executable) {
            throw new \InvalidArgumentException('Could not find executable: ' . $val);
        }

        $command = $this->parameters->get('command', $this->getName());
        $cli_option = $this->parameters->get('version_parameter', '--version');
        $version_mask = $this->parameters->get('version_mask', '/Version/');

        if ($version_mask) {
            $version_info_raw = trim(shell_exec('cat /dev/null | ' . $executable . ' ' . $cli_option . ' 2>&1'));
            if (!preg_match($version_mask, $version_info_raw, $matches, PREG_OFFSET_CAPTURE)) {
                throw new \InvalidArgumentException(
                    'Could not get version information for "' . $command . '" using "' . $executable . '".'
                );
            }
        }

        return $executable;
    }
}
