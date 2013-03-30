<?php

namespace Ongoo\Core;

class Configuration
{

    protected static $_instance = null;

    protected function __construct()
    {

    }

    /**
     *
     * @return Configuration
     */
    public static function &getInstance()
    {
        if (is_null(self::$_instance))
        {
            $classname = get_called_class();
            self::$_instance = new $classname();
        }
        return self::$_instance;
    }

    protected $config = array();

    /**
     * Retrieves a config parameter.
     *
     * @param string $name    A config parameter name
     * @param mixed  $default A default config parameter value
     *
     * @return mixed A config parameter value, if the config parameter exists, otherwise null
     */
    public function get($name, $default = null)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

    /**
     * Indicates whether or not a config parameter exists.
     *
     * @param string $name A config parameter name
     *
     * @return bool true, if the config parameter exists, otherwise false
     */
    public function has($name)
    {
        return array_key_exists($name, $this->config);
    }

    /**
     * Sets a config parameter.
     *
     * If a config parameter with the name already exists the value will be overridden.
     *
     * @param string $name  A config parameter name
     * @param mixed  $value A config parameter value
     */
    public function set($name, &$value)
    {
        $this->config[$name] = $value;
    }

    /**
     * Sets an array of config parameters.
     *
     * If an existing config parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @param array $parameters An associative array of config parameters and their associated values
     * @return Configuration
     */
    public function append($parameters = array(), $flattern = false, $callbackToFlattern = null)
    {
        if ($flattern)
        {
            if (is_callable($callbackToFlattern))
            {
                $parameters = $callbackToFlattern($parameters);
            } else
            {
                $parameters = \Ongoo\Utils\ArrayUtils::flatternPathConfiguration($parameters);
            }
        }
        $this->config = \Ongoo\Utils\ArrayUtils::merge_recursive_simple($this->config, $parameters);
        return $this;
    }

    /**
     * Sets an array of config parameters.
     *
     * @param array $parameters An associative array of config parameters and their associated values
     */
    public function init($parameters = array())
    {
        $this->config = $parameters;
    }

    /**
     * Retrieves all configuration parameters.
     *
     * @return array An associative array of configuration parameters.
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Clears all current config parameters.
     */
    public function clear()
    {
        $this->config = array();
    }

    public function load($configuration, $callbackToFlattern = null)
    {
        if (is_callable($callbackToFlattern))
        {
            $parameters = $callbackToFlattern($configuration);
        } else
        {
            $parameters = \Ongoo\Utils\ArrayUtils::flatternPathConfiguration($configuration);
        }
        $this->init($parameters);
    }

}