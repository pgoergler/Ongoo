<?php

namespace Ongoo\Helper\Helpers
{

    use \Ongoo\Helper\Helper,
        \Ongoo\Logger\Logger;

    class UrlHelper extends Helper
    {
        public function url_for($name, $params = array())
        {
            return $this->app['url_generator']->generate($name, $params);
        }
        
        public static function urlFor($name, $params = array())
        {
            $app = \Ongoo\Core\Configuration::getInstance()->get('application');
            return $app['url_generator']->generate($name, $params);
        }

    }
}

namespace
{
    function url_for($name, $params = array())
    {
        return \Ongoo\Helper\Helpers\UrlHelper::urlFor($name, $params);
    }
    
}