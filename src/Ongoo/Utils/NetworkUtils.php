<?php

namespace Ongoo\Utils;

/**
 * Description of NetworkUtils
 *
 * @author paul
 */
class NetworkUtils
{

    public static function getIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR']))
        {
            return $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['TERM']))
        {
            return $_SERVER['TERM'];
        }
        return '0.0.0.0';
    }

}

?>
