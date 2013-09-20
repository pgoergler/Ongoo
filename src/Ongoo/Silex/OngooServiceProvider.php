<?php

namespace Ongoo\Silex;

use Silex\Application;

/**
 * Description of OngooServiceProvider
 *
 * @author paul
 */
class OngooServiceProvider implements \Silex\ServiceProviderInterface
{

    public function boot(Application $app)
    {
        if ($app->offsetExists('error_handler') && $app['error_handler'] !== null)
        {
            set_error_handler($app['error_handler']);
        }
    }

    public function register(Application $app)
    {
        if (!$app->OffsetExists('application.mode'))
        {
            $app['application.mode'] = 'dev';
        }
        $databases = \Ongoo\Utils\ArrayUtils::merge(include(__CONFIG_DIR . '/databases.php'), $app['application.mode']);
        $globalConfig = \Ongoo\Utils\ArrayUtils::merge(include(__CONFIG_DIR . '/config.php'), $app['application.mode']);
        $loggers = \Ongoo\Utils\ArrayUtils::merge(include(__CONFIG_DIR . '/loggers.php'), $app['application.mode']);

        $app['configuration'] = $app->share(function()
                {
                    return \Ongoo\Core\Configuration::getInstance();
                });

        $app['configuration']->load(array('Databases' => $databases));
        $app['configuration']->append(array('Loggers' => $loggers), true);
        $app['configuration']->append($globalConfig, true);

        if ($app->offsetExists('logger') && $app['logger'] !== null)
        {
            $app['error_handler'] = $app->protect(function($errno, $errstr, $errfile, $errline) use(&$app)
                    {
                        if (!(error_reporting() & $errno))
                        {
                            // This error code is not included in error_reporting
                            return;
                        }

                        switch ($errno)
                        {
                            case E_ERROR:
                            case E_USER_ERROR:
                                $app['logger']->error("[$errno] In $errfile at $errline : $errstr");
                                break;
                            case E_NOTICE:
                            case E_USER_NOTICE:
                                $app['logger']->notice("[$errno] In $errfile at $errline : $errstr");
                                break;
                            case E_WARNING:
                            case E_USER_WARNING:
                                $app['logger']->warning("[$errno] In $errfile at $errline : $errstr");
                                break;
                            case E_DEPRECATED:
                                $app['logger']->info("[DEPRECATED] In $errfile at $errline : $errstr");
                                break;
                            case E_STRICT:
                                $app['logger']->alert("[STRICT] In $errfile at $errline : $errstr");
                                break;
                            default:
                                $app['logger']->critical("[$errno] In $errfile at $errline : $errstr");
                                break;
                        }
                    });
        }

        $app['bundles.menu'] = array();
        $app['bundles'] = array();

        $app['bundle.register'] = $app->protect(function($bundle, $mainBundle = false, $alias = null) use (&$app)
                {
                    $bundlePath = $app['dir_apps'] . '/' . $bundle;
                    if ($app->offsetExists('bundle.include_routes') && $app['bundle.include_routes'])
                    {
                        $routes = $bundlePath . '/config/routes.php';
                        if (is_file($routes))
                        {
                            include $routes;
                        }
                    }

                    if ($app->offsetExists('twig') && $app['twig'])
                    {
                        $views = $bundlePath . '/Views';
                        if (is_dir($views))
                        {
                            $app['twig.loader.filesystem']->addPath($views, "$bundle");
                            if ($mainBundle)
                            {
                                $app['twig.loader.filesystem']->addPath($views, "main");
                            }

                            if ($alias)
                            {
                                $app['twig.loader.filesystem']->addPath($views, "$alias");
                            }
                        }
                    }

                    $configFile = $bundlePath . '/config/config.php';
                    if (file_exists($configFile))
                    {
                        $app['configuration']->append(\Ongoo\Utils\ArrayUtils::merge(include($configFile), $app['application.mode']), true);
                    }
                    $bundles = $app['bundles'];
                    $bundles[$bundle] = true;
                    $app['bundles'] = $bundles;
                });


        $app['bundle.is_registered'] = $app->protect(function($bundle) use (&$app)
                {
                    return isset($app['bundles'][$bundle]) && $app['bundles'][$bundle] ? true : false;
                });

        $app['bundle.register.menu'] = $app->protect(function($bundle, $path = null) use (&$app)
                {
                    if ($path == null)
                    {
                        $path = $app['dir_apps'] . '/' . $bundle . '/config/menu.php';
                    }

                    if (file_exists($path))
                    {
                        $array = $app['bundles.menu'];
                        $array[$bundle] = $path;
                        $app['bundles.menu'] = $array;
                    } else
                    {
                        echo $path;
                    }
                });
    }

}

require_once('functions.php');
