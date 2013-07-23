<?php

namespace Environaut\Checks;

use Environaut\Checks\Check;

class MbInternalEncodingCheck extends Check
{
    public function run()
    {
        if (!function_exists('mb_substr')) {
            $this->addError('The multibyte extension does not seem to be installed.');
            return false;
        }

        $string_as_latin1 = mb_convert_encoding('CHARSET - WÄHLE UTF-8 AS SENSIBLE DEFAULT!', 'LATIN1', 'UTF-8');
        $string_part_as_utf8 = mb_substr($string_as_latin1, 0, 13, 'UTF-8');
        $string_part_by_default = mb_substr($string_as_latin1, 0, 13);

        if ($string_part_as_utf8 !== $string_part_by_default) {
            $this->addNotice(
                'Warning! The internal encoding for multibyte strings seems to differ from UTF-8. It is: ' .
                mb_internal_encoding()
            );
            $this->addNotice('This means mb_* functions need the encoding parameter set to work correctly with UTF-8.');
            return false;
        } else {
            $this->addInfo(
                'Internal multibyte string encoding seems to be working correctly for UTF-8. It is: ' .
                mb_internal_encoding()
            );
        }

        return true;
    }
}
