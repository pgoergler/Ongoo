<?php

namespace Ongoo\Utils;

/**
 * Description of StringUtils
 *
 * @author paul
 */
class StringUtils
{

    protected static $cyphers = array(
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"
    );

    public static function convert($num, $base = 62)
    {
        $maxBase = count(self::$cyphers);
        if ($base > $maxBase)
        {
            $base = $maxBase;
        }

        $num = abs($num);

        $result = "";
        while ($num != 0)
        {
            $modulo = $num % $base;
            if ($modulo < 0 || $modulo > $base)
            { // ??? When it could happens ?
                $modulo = 0;
            }

            if ($modulo % 2 == 0)
            {
                $result = $result . self::$cyphers[$modulo];
            } else
            {
                $result = self::$cyphers[$modulo] . $result;
            }

            $num = intval($num / $base);
        }
        return $result;
    }

    public static function stripAccents($string)
    {
        return strtr($string, utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public static function toGSM($str)
    {
        $str = self::toASCII($str);
        return str_replace("\t", " ", $str);
    }

    public static function toASCII($str)
    {
        if (!mb_check_encoding($str, 'UTF-8'))
        {
            $str = utf8_encode($str);
            \Logging\LoggersManager::getInstance()->get()->debug("need to encode");
        }

        /*
          if (!mb_check_encoding(utf8_encode($str), 'UTF-8'))
          {
          $str = utf8_encode($str);
          \Logging\LoggersManager::getInstance()->get()->warn("need to RE encode");
          } */
        try
        {
            $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
        } catch (\Exception $e)
        {
            \Logging\LoggersManager::getInstance()->get()->error($str);
            \Logging\LoggersManager::getInstance()->get()->error($e);
            throw $e;
        }
        //\Logging\LoggersManager::getInstance()->get()->debug($str);
        //$str = preg_replace('#&euro;#', 'eur', $str);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml|caron|uro);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        if (function_exists('iconv'))
        {
            $locale = setlocale(LC_ALL, 0);
            setlocale(LC_ALL, 'fr_FR');
            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            setlocale(LC_ALL, $locale);
            if ($string !== false)
            {
                return $string;
            }
        }
        return strtr(utf8_decode($str), utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'), 'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    }

    public static function slugify($text, $default = 'n-a')
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv'))
        {
            $locale = setlocale(LC_ALL, 0);
            setlocale(LC_ALL, 'fr_FR');
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            setlocale(LC_ALL, $locale);
        }
        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text))
        {
            return $default;
        }

        return $text;
    }

    public static function secToTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours * 3600)) / 60);
        $seconds = $seconds - ($hours * 3600 ) - ($mins * 60);
        return sprintf("%02d:%02d:%02d", $hours, $mins, $seconds);
    }

    public static function timeToSec($time)
    {
        if (preg_match('#^(\d+):(\d{2}):(\d{2})$#', $time, $m))
        {
            return $m[1] * 3600 + $m[2] * 60 + $m[3];
        }
        return 0;
    }

    public static function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last)
        {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
            case 'gb':
                $val *= 1024;
            case 'm':
            case 'mb':
                $val *= 1024;
            case 'k':
            case 'kb':
                $val *= 1024;
        }

        return $val;
    }

    /**
     *
     * @param int $bytes number of bytes
     * @param string $unit GB|MB|KB|B|null
     * @param string $format output result as "%size% %unit%"
     * @return string
     */
    public static function bytesTo($bytes, $unit = null, $format = '%size% %unit%')
    {
        $methods = array(
            'GB' => function($bytes)
            {
                return \decimal($bytes / 1073741824);
            },
            'MB' => function($bytes)
            {
                return \decimal($bytes / 1048576);
            },
            'KB' => function($bytes)
            {
                return \decimal($bytes / 1024);
            },
            'B' => function($bytes)
            {
                return $bytes;
            },
        );
        $methods['auto'] = function($bytes)
                {
                    if ($bytes >= 1073741824)
                    {
                        return $methods['GB'];
                    } elseif ($bytes >= 1048576)
                    {
                        return $methods['MB'];
                    } elseif ($bytes >= 1024)
                    {
                        return $methods['KB'];
                    } else
                    {
                        return $methods['B'];
                    }
                };

        $key = is_null($unit) ? 'auto' : strtoupper($unit);
        if( !array_key_exists($key, $methods) )
        {
            $key = 'auto';
        }
        $replace = array(
            '%size%' => $methods[$key]($bytes),
            '%unit%' => $unit,
        );
        return strtr($format, $replace);
    }

    public static function utf8htmlentities($string, $flags = null)
    {
        if (is_null($flags))
        {
            if (!defined('ENT_HTML401')) // PHP5.4
            {
                define('ENT_HTML401', 0);
            }
            $flags = ENT_COMPAT | ENT_HTML401;
        }
        return htmlentities($string, $flags, 'UTF-8');
    }

    /**
     * Convert a string into valid UTF-8. This function is quite slow.
     *
     * When invalid byte subsequences are encountered, they will be replaced with
     * U+FFFD, the Unicode replacement character.
     *
     * @param   string  String to convert to valid UTF-8.
     * @return  string  String with invalid UTF-8 byte subsequences replaced with
     *                  U+FFFD.
     * @group utf8
     */
    public static function utf8ize($string)
    {
        if (self::isUtf8($string))
        {
            return $string;
        }

        // There is no function to do this in iconv, mbstring or ICU to do this, so
        // do it (very very slowly) in pure PHP.
        // TODO: Provide an optional fast C implementation ala fb_utf8ize() if this
        // ever shows up in profiles?

        $result = array();

        $regex =
                "/([\x01-\x7F]" .
                "|[\xC2-\xDF][\x80-\xBF]" .
                "|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]" .
                "|[\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF])" .
                "|(.)/";

        $offset = 0;
        $matches = null;
        while (preg_match($regex, $string, $matches, 0, $offset))
        {
            if (!isset($matches[2]))
            {
                $result[] = $matches[1];
            } else
            {
                // Unicode replacement character, U+FFFD.
                $result[] = "\xEF\xBF\xBD";
            }
            $offset += strlen($matches[0]);
        }

        return implode('', $result);
    }

    /**
     * Determine if a string is valid UTF-8.
     *
     * @param string  Some string which may or may not be valid UTF-8.
     * @return bool    True if the string is valid UTF-8.
     * @group utf8
     */
    public static function isUtf8($string)
    {
        if (function_exists('mb_check_encoding'))
        {
            // If mbstring is available, this is significantly faster than using PHP
            // regexps.
            return mb_check_encoding($string, 'UTF-8');
        }

        $regex =
                "/^(" .
                "[\x01-\x7F]+" .
                "|([\xC2-\xDF][\x80-\xBF])" .
                "|([\xE0-\xEF][\x80-\xBF][\x80-\xBF])" .
                "|([\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]))*\$/";

        return preg_match($regex, $string);
    }

    /**
     * Find the character length of a UTF-8 string.
     *
     * @param string A valid utf-8 string.
     * @return int   The character length of the string.
     * @group utf8
     */
    public static function utf8Len($string)
    {
        return strlen(utf8_decode($string));
    }

}

?>
