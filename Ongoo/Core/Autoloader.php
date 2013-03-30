<?php

namespace Ongoo\Core;

class Autoloader
{

    protected static $_instance = null;
    protected $_directories = array();
    protected $namespaces = array();

    private function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     *
     * @return Autoloader
     */
    public static function &getInstance()
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new Autoloader();
        }
        return self::$_instance;
    }

    public function register($namespace, $directory)
    {
        $this->namespaces[$namespace] = preg_replace('#/$#', '', $directory);
        return $this;
    }

    public function autoload($className)
    {
        //echo "className = $className\n";

        $file = $this->findRegistered($className);

        if ($file)
        {
            //echo "trying $className in " . $file . ".php\n";
            if (file_exists($file . '.php'))
            {
                require_once($file . '.php');
                return;
            } /*else
            {
                //require_once($file . '.php');
                echo "$className not found. File $file.php not exists.\n";
                //throw new \RuntimeException("$className not found. File $file.php not exists.");
            }*/
        }
        //print_r($solutions);
    }

    protected function findRegistered($className)
    {
        //echo "searching $className\n";
        if (isset($this->namespaces[$className]))
        {
            //echo "found $className in " . $this->namespaces[$className] . "\n";
            return $this->namespaces[$className];
        }

        if (preg_match('#\\\([a-zA-Z0-9_]+)$#', $className, $matches))
        {
            $ns = preg_replace('#\\\([a-zA-Z0-9_]+)$#', '', $className);
            //echo "trying with $ns\n";
            $res = $this->findRegistered($ns);

            //echo "got $ns in $res\n";
            return $res . DIRECTORY_SEPARATOR . $matches[1];
        }
        //echo "trying with $className not match\n";
        return null;
    }

}