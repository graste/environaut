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
 *
 * Supported parameters are:
 * - "command": name of executable/command to check
 * - "setting": name of setting to use for absolute path storage (for export)
 * - "setting_group": name of settings group to use (for export; defaults to "config")
 * - "question": question to ask the user (defaults to "Path to the executable [command]")
 * - "default": default command or executable path to try (defaults to "/usr/bin/[command]")
 * - "validator": validator callable to use (defaults to one, that uses "which" to find the path of the command)
 * - "max_attempts": maximum number of attempts a user is allowed to have for correct input of a path
 */
class ExecutableCheck extends Check
{
    /**
     * Default group of settings that this check stores in the result.
     */
    const DEFAULT_SETTING_GROUP_NAME = 'config';

    /**
     * Default group name used in messages of the report.
     * By default also used as default setting group name if not customized.
     */
    const DEFAULT_CUSTOM_GROUP_NAME = 'Executables';

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName()
    {
        return self::DEFAULT_CUSTOM_GROUP_NAME;
    }

    /**
     * Returns the default group name this check uses for settings when none is specified.
     *
     * @return string default group name of settings of this check
     */
    public function getDefaultSettingGroupName()
    {
        if ($this->group !== self::DEFAULT_CUSTOM_GROUP_NAME) {
            return $this->group;
        }

        return self::DEFAULT_SETTING_GROUP_NAME;
    }

    /**
     * Ask user for an absolute path to the given executable or try to determine it
     * by itself using the default value (on confirmation).
     *
     * @return boolean true
     *
     * @throws \InvalidArgumentException on setup errors like "choices" can't be interpreted or parameters being wrong
     * @throws \RuntimeException if there is no data to read in the input stream
     * @throws \Exception when the maximum number of attempts has been reached and no valid response has been given
     */
    public function run()
    {
        $output = $this->getOutputStream();
        $dialog = $this->getDialogHelper();

        $command = $this->parameters->get('command', $this->getName());
        $setting = $this->parameters->get('setting', 'cmd.' . $command);
        $setting_group = $this->parameters->get('setting_group');
        $default = $this->parameters->get('default', '/usr/bin/' . $command);
        $choices = $this->parameters->get(
            'choices',
            array(
                '/bin/',
                '/usr/bin/',
                '/usr/sbin/',
                '/usr/local/bin/',
                '/usr/local/sbin/',
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

        // ask for path to executable with validation and autocomplete of common executable directories like /usr/bin
        $absolute_executable_path = $dialog->askAndValidate(
            $output,
            $question,
            $validator,
            $max_attempts,
            $default,
            $choices
        );

        $this->addSetting($setting, $absolute_executable_path, $setting_group);
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
