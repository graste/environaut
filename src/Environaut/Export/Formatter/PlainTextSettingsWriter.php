<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;
use RuntimeException;

class PlainTextSettingsWriter extends BaseFormatter
{
    /**
     * Writes all or specific groups of settings as a plain text file and
     * returns a message with some information about that.
     *
     * @param IReport $report report to take results (and settings) from
     *
     * @return string messages for CLI output
     */
    public function format(IReport $report)
    {
        $params = $this->getParameters();

        $file = $params->get('location', 'environaut-config');
        $groups = $params->get('groups');
        $output = $this->startOutput($file, $groups);

        $embed_group_path = $params->get('embed_group_path', true);
        $filter_settings = $params->get('filter_settings', array());
        $template = $params->get('template', false);

        if (!$template) {
            throw new RuntimeException(
                sprintf("The %s does not support a default template. Please define one.", __CLASS__)
            );
        }

        $template_settings = array();
        foreach ($report->getSettingsAsArray($groups) as $setting) {
            if ($embed_group_path === true) {
                $setting_key = $setting['group'] . '.' . $setting['name'];
            } else {
                $setting_key = $setting['name'];
            }
            $template_settings[$setting_key] = $setting['value'];
        }

        $content = self::vksprintf($template, $template_settings);

        return $this->endOutput(
            file_put_contents($file, $content, LOCK_EX)
        );
    }

    protected function startOutput($file, $groups)
    {
        $groups = is_array($groups) ? $groups : array();
        $output = '';

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

        return $output;
    }

    protected function endOutput($everything_ok)
    {
        if (!$everything_ok !== false) {
            return '<error>FAILED</error>.';
        } else {
            return '<info>ok</info>.';
        }
    }
}
