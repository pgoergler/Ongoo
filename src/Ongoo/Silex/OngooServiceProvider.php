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
        $app['resolver'] = $app->extend('resolver', function ($resolver, $app) {
            return new \Ongoo\Controllers\ControllerResolver($app, $resolver, new \Ongoo\Controllers\CallbackResolver($app));
        });
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

        $app['configuration'] = function() {
            return \Ongoo\Core\Configuration::getInstance();
        };

        $app['configuration']->load(array('Databases' => $databases));
        $app['configuration']->append(array('Loggers' => $loggers), true);
        $app['configuration']->append($globalConfig, true);

        $app['bundles.menu'] = array();
        $app['bundles'] = array();

        $app['bundle.register'] = $app->protect(function($bundle, $mainBundle = false, $alias = null, $include_routes = true) use (&$app) {
            $bundlePath = $app['dir_apps'] . '/' . $bundle;
            if ($include_routes && $app->offsetExists('bundle.include_routes') && $app['bundle.include_routes'])
            {
                $routes = $bundlePath . '/config/routes.php';
                if (is_file($routes))
                {
                    include_once($routes);
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

            $bootstrapFile = $bundlePath . '/config/bootstrap.php';
            if (file_exists($bootstrapFile))
            {
                include_once($bootstrapFile);
            }
            $bundles = $app['bundles'];
            $bundles[$bundle] = true;
            $app['bundles'] = $bundles;
        });


        $app['bundle.is_registered'] = $app->protect(function($bundle) use (&$app) {
            return isset($app['bundles'][$bundle]) && $app['bundles'][$bundle] ? true : false;
        });

        $app['bundle.register.menu'] = $app->protect(function($bundle, $path = null) use (&$app) {
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
        if (class_exists('\OngooUtils\OngooUtils'))
        {
            \OngooUtils\OngooUtils::getInstance()->setInjector($app);
        }
    }

}

require_once('functions.php');
