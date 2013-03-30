<?php

namespace Ongoo\Utils;

/**
 * Description of XmlUtils
 *
 * @author paul
 */
class XmlUtils
{

    public static function encode($string)
    {
        $result = str_replace('&', '&amp;', $string);
        $result = str_replace('<', '&lt;', $result);
        $result = str_replace('>', '&gt;', $result);
        return $result;
    }

    public static function decode($string)
    {
        $result = str_replace('&lt;', '<', $string);
        $result = str_replace('&gt;', '>', $result);
        $result = str_replace('&amp', '&', $result);

        return $result;
    }

}

?>
