<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class MbInternalEncodingCheck extends Check
{
    /**
     * Default group name used in messages of the report.
     * By default also used as default setting group name if not customized.
     */
    const DEFAULT_CUSTOM_GROUP_NAME = 'Configuration';

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName()
    {
        if ($this->group !== self::DEFAULT_GROUP_NAME) {
            return $this->group;
        }

        return self::DEFAULT_CUSTOM_GROUP_NAME;
    }

    public function run()
    {
        if (!function_exists('mb_substr')) {
            $this->addError(
                $this->getParameters()->get(
                    'missing_message',
                    'The multibyte string extension does not seem to be installed.' . PHP_EOL .
                    'Please compile and configure PHP with "--enable-mbstring" and "--enable-mbregex".' . PHP_EOL .
                    'More information can be found here: http://php.net/manual/en/book.mbstring.php'
                )
            );
            return false;
        }

        $error_msg = sprintf(
            $this->getParameters()->get(
                'error_message',
                'The internal encoding for multibyte strings seems to differ from UTF-8. It is: %s' . PHP_EOL .
                'This means mb_* functions need the $encoding="UTF-8" parameter set to work correctly with UTF-8.'
            ),
            mb_internal_encoding()
        );

        $success_msg = sprintf(
            $this->getParameters()->get(
                'success_message',
                'Internal multibyte string encoding seems to be working correctly for UTF-8. It is: ' .
                mb_internal_encoding()
            )
        );

        $handle_as_error = $this->getParameters()->get('handle_as_error', false);

        $string_as_latin1 = mb_convert_encoding('CHARSET - WÄHLE UTF-8 AS SENSIBLE DEFAULT!', 'LATIN1', 'UTF-8');
        $string_part_as_utf8 = mb_substr($string_as_latin1, 0, 13, 'UTF-8');
        $string_part_by_default = mb_substr($string_as_latin1, 0, 13);

        if ($string_part_as_utf8 !== $string_part_by_default) {
            if ($handle_as_error) {
                $this->addError($error_msg);
            } else {
                $this->addNotice($error_msg);
            }
            return false;
        } else {
            if (false !== $success_msg) {
                $this->addInfo($success_msg);
            }
        }

        return true;
    }
}
