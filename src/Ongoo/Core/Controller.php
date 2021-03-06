<?php

namespace Ongoo\Core;

abstract class Controller
{

    protected $app = null;
    protected $route = null;
    protected $data = array();
    protected $headers = array();
    protected $layout = 'layout.twig';
    protected $view = null;
    protected $name = null;
    protected $request = null;

    public function __construct(&$app)
    {
        $this->app = $app;
    }

    public function initialize($route)
    {
        $this->route = $route;
        $this->request = null;
    }

    public function getName()
    {
        if (is_null($this->name))
        {
            return preg_replace('#^(.*)\\\#', '', get_called_class());
        }
        return $this->name;
    }

    public function getIp()
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

    /**
     *
     * @return \Ongoo\Session\Session
     */
    public function getSession()
    {
        return $this->app['session'];
    }

    /**
     *
     * @return \Apps\Secure\Models\SecureUser
     */
    public function getSecureUser()
    {
        return $this->app['session']->getGuardUser();
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->app['request'];
    }

    /**
     *
     * @param url $to
     */
    public function redirect($to, $params = array())
    {
        if (preg_match('#^http(s)?://#', $to))
        {
            return $this->app()->redirect($to);
        }
        return $this->app()->redirect(url_for($to, $params));
    }
    
    public function forwardCodeUnless($code, $condition, $message = "error")
    {
        $this->forwardCode($code, !$condition, $message);
    }

    public function forwardCode($code, $condition, $message = "error")
    {
        if ($condition)
        {
            $this->app['logger']->error("aborting due to {0} {1}", array($code, $message));
            $this->app->abort($code, $message);
        }
        return;
    }

    public function forward404Unless($condition)
    {
        $this->forward404(!$condition);
    }

    public function forward404($condition)
    {
        if ($condition)
        {
            $this->app->abort(404, "Not found");
        }
        return;
    }

    public function forward401Unless($condition)
    {
        $this->forward401(!$condition);
    }

    public function forward401($condition)
    {
        if ($condition)
        {
            $this->app->abort(401, "Unauthorized");
        }
        return;
    }

    /**
     *
     * @return \Silex\Application
     */
    public function app()
    {
        return $this->app;
    }

    public function before(\Symfony\Component\HttpFoundation\Request $request)
    {
        $controller = $request->attributes->get('_controller');

        if (is_array($controller))
        {
            $action = $controller[1];
            $this->preExecute($action);
        }
    }

    public function after(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $controller = $request->attributes->get('_controller');

        if (is_array($controller))
        {
            $object = $controller[0];
            $action = $controller[1];

            $this->postExecute($action);

            if ($this->getView() == null)
            {
                $this->setView($action);
            }

            if ($response instanceof \Symfony\Component\HttpFoundation\RedirectResponse)
            {
                return $response;
            } else
            {
                return $response->setContent($this->render());
            }
        }
    }

    public function preExecute($action)
    {

    }

    public function postExecute($action)
    {

    }

    public function execute($action = 'index', $args = array())
    {
        $oldSelfPath = null;
        if (is_dir(dirname(dirname($this->getPathname())) . '/Views/' . $this->getName()))
        {
            $oldSelfPath = $this->app['twig.loader.filesystem']->getPaths('self');
            $this->app['twig.loader.filesystem']->setPaths(dirname(dirname($this->getPathname())) . '/Views/' . $this->getName(), 'self');
        }

        $this->preExecute($action);

        $fct = 'execute' . preg_replace_callback('/^([a-z])/', function($m)
                        {
                            return strtoupper($m[1]);
                        }, $action);

        $return = call_user_func_array(array($this, $fct), $args);

        $this->postExecute($action);

        if ($return instanceof \Symfony\Component\HttpFoundation\Response)
        {
            return $return;
        } else
        {
            if ($this->getView() == null)
            {
                $this->setView('@main/' . $this->getName() . "/$action");
                if( !$this->app['twig.loader']->exists($this->getView()))
                {
                    $this->setView("@self/$action");
                }
            }

            if (!$this->app['twig.loader']->exists($this->getView()))
            {
                $this->app['logger']->error($this->getView() . " not exists settting to @self/$action");
                $this->setView("@self/$action");
            }

            $path = dirname($this->app['twig.loader']->getCacheKey($this->getView()));
            if (is_dir($path))
            {
                $this->app['twig.loader.filesystem']->setPaths($path, 'local');
            }

            $content = $this->render();
            if( $oldSelfPath )
            {
                $this->app['twig.loader.filesystem']->setPaths($oldSelfPath, 'self');
            }
            return new \Symfony\Component\HttpFoundation\Response($content, 200, $this->headers);
        }
    }

    public function render()
    {
        return $this->app['twig']->render($this->getView(), $this->getData());
    }

    protected function getPathname()
    {
        $reflector = new \ReflectionObject($this);
        return $reflector->getFilename();
    }

    public function setView($action)
    {
        if (preg_match('#(.*)/(.*?)$#', $action, $m))
        {
            $m[1] = preg_replace('#^./#', '@self/', $m[1]);
            $m[2] = preg_replace_callback('/^([A-Z])/', function($m)
                    {
                        return strtolower($m[1]);
                    }, $m[2]);
            $view = $m[1] . DIRECTORY_SEPARATOR . $m[2];
        } else
        {
            $view = '@self/' . lcfirst($action);
        }

        $this->view = $view . 'Success.twig';
    }

    public function getView()
    {
        return $this->view;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }
    
    public function remove($name)
    {
        if(array_key_exists($name, $this->data))
        {
            unset($this->data[$name]);
        }
        return $this;
    }

    public function get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    protected function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    protected function getHeader($key)
    {
        if (array_key_exists($key, $this->headers))
        {
            return $this->headers[$key];
        }
        return null;
    }

    public function getData()
    {
        return $this->data;
    }

}