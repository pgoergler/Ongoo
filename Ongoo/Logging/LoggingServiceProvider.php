<?php

namespace Ongoo\Logging;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Description of Log4PhpServiceProvider
 *
 * @author paul
 */
class LoggingServiceProvider implements ServiceProviderInterface
{

    public function boot(Application $app)
    {
        $dirs = array(
            'root' => __W_ROOT_DIR,
            'apps' => __W_APPS_DIR,
            'config' => __W_CONFIG_DIR,
            'data' => __W_DATA_DIR,
            'upload' => __W_UPLOAD_DIR,
            'vendor' => __W_VENDOR_DIR,
            'log' => __W_LOG_DIR,
            'web' => __W_WEB_DIR,
            'css' => __W_CSS_DIR,
            'js' => __W_JS_DIR,
            'img' => __W_IMG_DIR,
        );

        foreach ($dirs as $dir => $value)
        {
            $app['logger.factory']->set('dir_' . $dir, $value);
        }

        $app['logger.factory']->configure($app['ongoo.loggers']);
        $root = $app['logger.factory']->get('root');
        $app['logger'] = $root;
    }

    public function register(Application $app)
    {
        $app['logger.factory'] = $app->share(function() use(&$app)
                {
                    $factory = \Logging\LoggersManager::getInstance();
                    if( $app->offsetExists('logger.class'))
                    {
                        $factory->setLoggerClass($app['logger.class']);
                    }
                    return $factory;
                });

        $app->error(function (\Exception $e, $code) use(&$app)
                {
                    switch ($code)
                    {
                        case 404:
                            $message = 'The requested page could not be found.';
                            break;
                        default:
                            $message = 'We are sorry, but something went terribly wrong.';
                    }

                    $app['logger']->error("Error catcher has catch:");
                    $app['logger']->error($e);
                });
    }

}

class Logger extends \Logging\Logger implements \Symfony\Component\HttpKernel\Log\LoggerInterface
{

}