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

    }

    public function register(Application $app)
    {
        $app['bundles.menu'] = array();
        $app['bundles'] = array();

        $app['bundle.register'] = $app->protect(function($bundle, $mainBundle = false, $alias = null) use (&$app)
                {
                    $bundlePath = __W_APPS_DIR . '/' . $bundle;
                    if ($app->offsetExists('bundle.include_routes') && $app['bundle.include_routes'])
                    {
                        $routes = $bundlePath . '/config/routes.php';
                        if (is_file($routes))
                        {
                            include $routes;
                        }
                    }

                    if ($app->offsetExists('twig') && $app['twig'] )
                    {
                        $views = $bundlePath . '/Views';
                        if (is_dir($views))
                        {
                            $app['twig.loader.filesystem']->addPath($views, "$bundle");
                            if ($mainBundle)
                            {
                                $app['twig.loader.filesystem']->addPath($views, "main");
                            }

                            if( $alias )
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
                        $path = __W_APPS_DIR . '/' . $bundle . '/config/menu.php';
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

?>
