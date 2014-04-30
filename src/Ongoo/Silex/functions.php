<?php

namespace
{

    function ip()
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

    function client_id()
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->OffsetExists('client_id'))
        {
            $dependencyInjection['client_id'] = \ip();
        }
        return $dependencyInjection['client_id'];
    }

    function me()
    {
        $me = \whoami();
        return $me ? $me->getLogin() : \client_id();
    }

    function getOngooUser()
    {
        return \me();
    }

    function whoami()
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->OffsetExists('whoami'))
        {
            $dependencyInjection['whoami'] = $dependencyInjection->protect(function() use (&$dependencyInjection)
                    {
                        if ($dependencyInjection->OffsetExists('session') && $dependencyInjection['session'] instanceof \Quartz\QuartzGuard\Session && $dependencyInjection['session']->getGuardUser())
                        {
                            return $dependencyInjection['session']->getGuardUser();
                        }
                        return null;
                    });
        }
        return $dependencyInjection['whoami']();
    }

    function groupby($array, $groupby, $notexists = '-')
    {
        $result = array();
        foreach ($array as $key => $data)
        {
            $id = isset($data[$groupby]) ? $data[$groupby] : $notexists;
            if (isset($result[$id]))
            {
                $result[$id][$key] = $data;
            } else
            {
                $result[$id] = array($key => $data);
            }
        }
        return $result;
    }

    /**
     *
     * @param Mixed $datetime
     * @return \DateTime
     */
    function datetime($datetime, $timezone = null)
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->OffsetExists('to_datetime'))
        {
            $dependencyInjection['to_datetime'] = $dependencyInjection->protect(function($datetime, $timezone = null)
                    {
                        $timezone = is_null($timezone) ? null : ($timezone instanceof \DateTimeZone ? $timezone : new \DateTimeZone($timezone)); 
                
                        if (is_string($datetime))
                        {
                            return new \DateTime($datetime, $timezone);
                        } elseif ($datetime instanceof \DateTime)
                        {
                            return clone $datetime;
                        } elseif (is_null($datetime))
                        {
                            return new \DateTime();
                        } else
                        {
                            throw new \InvalidArgumentException('$datetime must be a date/time string or a \DateTime');
                        }
                    });
        }
        return $dependencyInjection['to_datetime']($datetime, $timezone);
    }

    /**
     * Return the "now" DateTime, it could be overrided by overidding app['now']
     * @return \DateTime
     */
    function now()
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->OffsetExists('now'))
        {
            $dependencyInjection['now'] = $dependencyInjection->protect(function()
                    {
                        return new \DateTime();
                    });
        }
        return $dependencyInjection['now']();
    }

    function decimal($number)
    {
        return round($number, 2, PHP_ROUND_HALF_UP);
    }

    function trans($id, $values = array(), $domain = null, $locale = null)
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->offsetExists('translator'))
        {
            return $id;
        }
        return $dependencyInjection['translator']->trans($id, $values, $domain, $locale);
    }

    function transChoice($id, $number, $values = array(), $domain = null, $locale = null)
    {
        $dependencyInjection = \Ongoo\Core\Configuration::getInstance()->get('application');
        if (!$dependencyInjection->offsetExists('translator'))
        {
            return $id;
        }
        return $dependencyInjection['translator']->transChoice($id, $number, $values, $domain, $locale);
    }

}
