<?php

namespace Ongoo\Helper
{

    class Helper
    {

        protected $app = null;

        public function __construct(\Pimple &$app = null)
        {
            if (is_null($app))
            {
                $app = \Ongoo\Core\Configuration::getInstance()->get('application');
            }
            $this->app = $app;
        }

        public static function use_helper($name, \Pimple $app)
        {
            if (strpos('\\', $name) === false)
            {
                $name = '\\Ongoo\\Helper\\Helpers\\' . $name;
            }
            return new $name($app);
        }

    }

}

namespace
{

    function use_helper($name, \Pimple $app = null)
    {
        return Ongoo\Helper\Helper::use_helper($name, $app);
    }

}