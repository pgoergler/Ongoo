<?php

namespace Ongoo\Controllers;

/**
 * Description of OngooControllerTrait
 *
 * @author paul
 */
trait OngooControllerTrait
{

    protected $flashes = array();
    
    public function timzeonePreExecute($action)
    {
        parent::preExecute($action);
        
        $dateFormat = 'Y-m-d H:i:sP';
        $timezone = date_default_timezone_get();
        
        $user = $this->getUser();
        if( $user )
        {
            $userInfos = $user->getExtraInfos();
            $dateFormat = isset($userInfos['date_format'])?$userInfos['date_format'] : $dateFormat;
            $timezone = isset($userInfos['timezone'])?$userInfos['timezone'] : $timezone;
        }
        $this->app['twig']->getExtension('core')->setDateFormat($dateFormat);
        $this->app['twig']->getExtension('core')->setTimezone($timezone);
    }
    
    /**
     *
     * @return \Apps\Secure\Models\SecureUser
     */
    public function getUser()
    {
        if( $this->getSession() && $this->getSession()->isStarted() )
        {
            return $this->getSecureUser();
        }
        return null;
    }
    
    public function getFlashes()
    {
        return $this->flashes;
    }

    public function flash($message, $class = 'danger', $id = null)
    {
        $id = is_null($id) ? \Ongoo\Utils\StringUtils::slugify($message) : $id;
        $this->flashes[$id] = array(
            'class' => $class,
            'text' => $message
        );
        $this->app['session']->getFlashBag()->add($class, array('id' => $id, 'text' => $message));
        return $this;
    }

}
