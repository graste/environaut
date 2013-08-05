<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Writes all or specific groups of settings as JSON to a file.
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
            $grouped_settings[$setting['group']][$setting['name']] = $setting['value'];
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
