<?php

namespace Ongoo\Controllers;

/**
 * Description of ControllerResolver
 *
 * @author paul
 */
class ControllerResolver extends \Silex\ServiceControllerResolver
{
    protected $app;
    protected $controller = null;
    protected $controllerAction = null;
    
    public function __construct(\Silex\Application $app, \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $controllerResolver, \Silex\CallbackResolver $callbackResolver)
    {
        parent::__construct($controllerResolver, $callbackResolver);
        $this->app = $app;
    }
    
    public function getController(\Symfony\Component\HttpFoundation\Request $request)
    {
        $controller = parent::getController($request);
        if( is_array($controller) && array_key_exists(2, $controller))
        {
            $this->controller = $controller[0];
            $this->controllerAction = $controller[2];
            return array($this->instantiateController($controller[0]), $controller[1]);
        }
        return $controller;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getArguments(\Symfony\Component\HttpFoundation\Request $request, $controller)
    {
        if( !is_null($this->controllerAction) )
        {
            $action = 'execute' . preg_replace_callback('/^([a-z])/', function($m)
                        {
                            return strtoupper($m[1]);
                        }, $this->controllerAction);
            $args = parent::getArguments($request, array($this->controller, $action));
            $arguments = array($this->controllerAction, $args);
        }
        else
        {
            $arguments = parent::getArguments($request, $controller);
        }
        return $arguments;
        
    }
    
    protected function instantiateController($class)
    {
        return new $class($this->app);
    }
}
