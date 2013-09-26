<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;
use Environaut\Checks\ICheck;

/**
 * Writes all or specific groups of settings as SHELL variables to a file.
 *
 * Supported parameters include:
 * - "location": Path and filename to write (defaults to 'environaut-config.sh').
 * - "groups": Array of group names. Only settings with those groups are written.
 *             By default all settings regardless of their group are written.
 * - "template": Content of file to write with placeholder '%settings$s' where
 *               settings will be put as shell variables (separated by newlines).
 * - "use_group_as_prefix": Defines if the group name of the setting should be
 *                          used as a prefix (defaults to false).
 * - "capitalize_names": Whether to convert variable names to all uppercase or
 *                       not (defaults to false).
 */
class ShellSettingsWriter extends BaseFormatter
{
    /**
     * Writes all or specific groups of settings as a shell file and
     * returns a message with some information about that.
     *
     * @param IReport $report report to take results (and settings) from
     *
     * @return string messages for CLI output
     */
    public function format(IReport $report)
    {
        $output = '';
        $params = $this->getParameters();

        $file = $params->get('location', 'environaut-config.sh');
        $groups = $params->get('groups');

        if (is_writable($file)) {
            $output .= '<comment>Overwriting</comment> ';
        } else {
            $output .= 'Writing ';
        }

        if (empty($groups)) {
            $output .= 'all groups ';
        } else {
            $output .= 'group(s) "' . implode(', ', $groups) . '" ';
        }

        $output .= 'to file "<comment>' . $file . '</comment>"...';

        $default_template = <<<EOT
%settings\$s
EOT;

        $template = $params->get('template', $default_template);

        $all_settings = $report->getSettingsAsArray($groups);

        $grouped_settings = array();
        $content = '';

        foreach ($all_settings as $setting) {
            $name = $this->makeShellVariableName($setting['name'], $setting['group']);
            $value = $this->mapValue($setting['value']);

            $content .= $name . "='" . $value ."'\n";
        }

        $content = self::vksprintf($template, array('settings' => $content));

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output .= '<info>ok</info>.';
        } else {
            $output .= '<error>FAILED</error>.';
        }

        return $output;
    }

    protected function mapValue($value)
    {
        switch (gettype($value)) {
            case "boolean":
                return $value ? "1" : "";
            case "array":
                return $this->transformArrayValue($value);
            default:
                return $value;
        }
    }

    protected function transformArrayValue(array $value)
    {
        return implode("\n", $value);
    }

    protected function makeShellVariableName($setting_name, $group)
    {
        $params = $this->getParameters();

        $use_group_as_prefix = $params->get('use_group_as_prefix', false);
        $capitalize_names = $params->get('capitalize_names', false);
        $valid_name_regex = '/^[A-z_][A-z0-9_]*$/';

        if ($use_group_as_prefix && $group && $group !== ICheck::DEFAULT_GROUP_NAME) {
            $prefix = $group . '_';
        } else {
            $prefix = '';
        }

        $name = $prefix . $setting_name;

        if (!preg_match($valid_name_regex, $name)) {
            throw new \Exception('"'.$name.'" is not a valid shell variable identifier.');
        }

        if ($capitalize_names) {
            $name = strtoupper($name);
        }

        return $name;
    }
}
