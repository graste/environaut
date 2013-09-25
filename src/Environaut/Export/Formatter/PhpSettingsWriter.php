<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Writes all or specific groups of settings as a PHP file that may be included.
 */
class PhpSettingsWriter extends BaseFormatter
{
    /**
     * Writes all or specific groups of settings as a PHP file and
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

        $file = $params->get('location', 'environaut-config.php');
        $groups = $params->get('groups');
        $nested = $params->get('nested', true);

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
<?php return %settings\$s;
EOT;

        $template = $params->get('template', $default_template);

        $all_settings = $report->getSettingsAsArray($groups);

        $grouped_settings = array();
        foreach ($all_settings as $setting) {
            if ($nested === true) {
                $grouped_settings[$setting['group']][$setting['name']] = $setting['value'];
            } else {
                $grouped_settings[$setting['name']] = $setting['value'];
            }
        }

        $content = XmlSettingsWriter::vksprintf($template, array('settings' => var_export($grouped_settings, true)));

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output .= '<info>ok</info>.';
        } else {
            $output .= '<error>FAILED</error>.';
        }

        return $output;
    }
}
