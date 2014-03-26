<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Writes all or specific groups of settings as JSON to a file.
 *
 * Supported parameters include:
 * - "location": Path and filename to write (defaults to 'environaut-config.json').
 * - "groups": Array of group names. Only settings with those groups are written.
 *             By default all settings regardless of their group are written.
 * - "pretty": Whether to write the JSON object pretty printed to the file.
 *             Defaults to true, but only works from PHP 5.4 upwards.
 * - "nested": Defines whether to group settings by their group (defaults to true).
 */
class JsonSettingsWriter extends BaseFormatter
{
    /**
     * Writes all or specific groups of settings as a JSON file and
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

        $file = $params->get('location', 'environaut-config.json');
        $pretty = $params->get('pretty', true);
        $nested = $params->get('nested', true);
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

        $flags = JSON_FORCE_OBJECT;
        if ($pretty && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $all_settings = $report->getSettingsAsArray($groups);

        $grouped_settings = array();
        foreach ($all_settings as $setting) {
            if ($nested === true) {
                $grouped_settings[$setting['group']][$setting['name']] = $setting['value'];
            } else {
                $grouped_settings[$setting['name']] = $setting['value'];
            }
        }

        $content = json_encode($grouped_settings, $flags);

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output .= '<info>ok</info>.';
        } else {
            $output .= '<error>FAILED</error>.';
        }

        return $output;
    }
}
