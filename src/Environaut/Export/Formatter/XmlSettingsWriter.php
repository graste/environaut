<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Writes all or specific groups of settings as XML to a file.
 *
 * Supported parameters are:
 * - "location": Path and name of the filename to write (defaults to 'environaut-config.xml').
 * - "groups": Array with names of setting groups that should be written to that file.
 *             By defaults all settings regardless of their group are written.
 * - "file_template": Template string for the file content. Should contain a named argument "%group_template$s".
 * - "group_template": Template that is used as a wrapper for each settings group. Should contain two named
 *                     arguments "%group_name$s" and "%setting_template$s".
 * - "setting_template": Template string to use for each setting that is written. Should contain two named
 *                        arguments "%setting_name$s" and "%setting_value$s".
 *
 * There is no "nested" parameter as you can just define a "group_template" like '%setting_template$s'
 * that achieves a similar effect as "nested" parameter in the shell and json settings writer.
 */
class XmlSettingsWriter extends BaseFormatter
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

        $file = $params->get('location', 'environaut-config.xml');
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

        $default_file_template = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ae:configurations
    xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.0"
    xmlns:xi="http://www.w3.org/2001/XInclude"
    xmlns="http://agavi.org/agavi/config/parts/settings/1.0"
>
    <ae:configuration>
%group_template\$s
    </ae:configuration>
</ae:configurations>

EOT;

        $default_group_template = <<<EOT

        <settings prefix="%group_name\$s.">%setting_template\$s
        </settings>

EOT;

        $default_setting_template = <<<EOT

            <setting name="%setting_name\$s">%setting_value\$s</setting>
EOT;

        $file_template = $params->get('file_template', $default_file_template);
        $group_template = $params->get('group_template', $default_group_template);
        $setting_template = $params->get('setting_template', $default_setting_template);

        $all_settings = $report->getSettings($groups);

        $grouped_settings = array();
        foreach ($all_settings as $setting) {
            $grouped_settings[$setting->getGroup()][] = $setting;
        }

        $group_content = '';
        foreach ($grouped_settings as $group_name => $settings) {
            $settings_content = '';
            // remove control characters like vertical tabs, up/down arrows etc. as it breaks sprintf templates -.-
            $group_name = preg_replace('/[[:cntrl:]]/', '', $group_name);

            foreach ($settings as $setting) {
                $value = $setting->getValue();
                if (is_bool($value)) {
                    $value = var_export($value, true); // we want "true"/"false" instead of "1"/"" in the output
                }

                // remove control characters like vertical tabs, up/down arrows etc.
                // this leads arrow-up key "\033[A" being converted to "[A" which is probably not useful,
                // but at least does not break the cheapo sprintf templating for the moment...
                $name = preg_replace('/[[:cntrl:]]/', '', $setting->getName());
                $value = preg_replace('/[[:cntrl:]]/', '', $value);
                //$value = preg_replace('/[^\p{L}\s]/u','',$value);
                //$value = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $value);
                $settings_content .= self::vksprintf(
                    $setting_template,
                    array(
                        'setting_name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                        'setting_value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                        'group_name' => htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8')
                    )
                );
            }

            $group_content .= self::vksprintf(
                $group_template,
                array(
                    'setting_template' => $settings_content,
                    'group_name' => htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8')
                )
            );
        }

        $content = self::vksprintf(
            $file_template,
            array(
                'group_template' => $group_content
            )
        );

        $output .= 'to file "<comment>' . $file . '</comment>"...';

        $ok = file_put_contents($file, $content, LOCK_EX);

        if ($ok !== false) {
            $output .= '<info>ok</info>.';
        } else {
            $output .= '<error>FAILED</error>.';
        }

        return $output;
    }
}
