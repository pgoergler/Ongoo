<?php

namespace
{

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
