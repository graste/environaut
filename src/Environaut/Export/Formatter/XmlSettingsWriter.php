<?php

namespace Environaut\Export\Formatter;

use Environaut\Report\IReport;
use Environaut\Export\Formatter\BaseFormatter;

/**
 * Writes all or specific groups of settings as XML to a file.
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
        $options = $this->getOptions();

        $file = $options->get('location', 'environaut-config.xml');
        $groups = $options->get('groups', array());

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

        $file_template = $options->get('file_template', $default_file_template);
        $group_template = $options->get('group_template', $default_group_template);
        $setting_template = $options->get('setting_template', $default_setting_template);

        $all_settings = $report->getSettingsAsArray($groups);

        $group_content = '';
        foreach ($all_settings as $group_name => $settings) {
            $settings_content = '';

            foreach ($settings as $key => $value) {
                if (is_bool($value)) {
                    $value = var_export($value, true);
                }
                $settings_content .= self::vksprintf(
                    $setting_template,
                    array(
                        'setting_name' => htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
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

    /**
     * Like vsprintf, but accepts $args keys instead of order index.
     * Both numeric and strings matching /[a-zA-Z0-9_-]+/ are allowed.
     *
     * Base version of the method:
     * @see http://www.php.net/manual/de/function.vsprintf.php#110666
     *
     * @example: vskprintf('y = %y$d, x = %x$1.1f, key = %key$s', array('x' => 1, 'y' => 2, 'key' => 'MyKey'))
     * Result:  'y = 2, x = 1.0'
     *
     * '%s' without argument name works fine too. Everything vsprintf() can do
     * is supported.
     *
     * @author Josef Kufner <jkufner(at)gmail.com>
     * @author Oskar Stark <oskar.stark@exozet.com>
     */
    public static function vksprintf($str, array $args)
    {
        if (empty($args)) {
            return $str;
        }

        $map = array_flip(array_keys($args));

        $new_str = preg_replace_callback(
            '/(^|[^%])%([a-zA-Z0-9_-]+)\$/',
            function ($m) use ($map) {
                if (isset($map[$m[2]])) {
                    return $m[1] . '%' . ($map[$m[2]] + 1) . '$';
                } else {
                    /*
                     * HACK!
                     * vsprintf all time removes '% and the following character'
                     *
                     * so we add 6 x # to the string.
                     * vsprintf will remove '%#' and later we remove the rest #
                     */
                    return $m[1] . '%######' . $m[2][0] . '%' . $m[2] . '$';
                }
            },
            $str
        );

        $replaced_str = vsprintf($new_str, $args);

        return str_replace('#####', '%', $replaced_str);
    }
}
