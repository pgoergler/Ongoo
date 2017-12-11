<?php

namespace Ongoo\Twig;

use Pimple\Container;

/**
 * Description of TwigOngooExtensionProvider
 *
 * @author paul
 */
class TwigOngooExtensionProvider implements \Pimple\ServiceProviderInterface
{

    public function boot(Container $app)
    {
        
    }

    public function register(Container $app)
    {
        $twig = $app['twig'];
        $urlFor = new \Twig_SimpleFunction('url_for', function ($name, $params = array()) {
            return \Ongoo\Helper\Helpers\UrlHelper::urlFor($name, $params);
        });
        $twig->addFunction($urlFor, array('is_safe', 'html'));
        $filter = new \Twig_SimpleFilter('json_decode', function ($string, $asArray = true) {
            return json_decode($string, $asArray);
        });
        $twig->addFilter($filter);


        $filter = new \Twig_SimpleFilter('startsWith', function ($string) {
            $regex = str_replace('#', '\\#', $string);
            return preg_match("#^$regex#", $string);
        });
        $twig->addFilter($filter);
        $filter = new \Twig_SimpleFilter('endsWith', function ($string) {
            $regex = str_replace('#', '\\#', $string);
            return preg_match("#$regex$#", $string);
        });
        $twig->addFilter($filter);

        $fct = new \Twig_SimpleFunction('include_stylesheets', function($path = 'css/') use( &$app) {
            return $app['ongoo.helper.html']->include_stylessheets($path);
        }, array('is_safe' => array('html'))
        );

        $twig->addFunction($fct);


        $fct = new \Twig_SimpleFunction('include_javascripts', function($path = 'js/') use( &$app) {
            return $app['ongoo.helper.html']->include_javascripts($path);
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);


        $fct = new \Twig_SimpleFunction('include_links', function() use( &$app) {
            return $app['ongoo.helper.html']->include_links();
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $app['user'] = function() use (&$app) {
            return $app['session']->getGuardUser();
        };

        $fct = new \Twig_SimpleFunction('ip', function() use( &$app) {
            return \ip();
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('me', function() use( &$app) {
            return \me();
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('whoami', function() use( &$app) {
            return \whoami();
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('now', function() use( &$app) {
            return \now();
        }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $filter = new \Twig_SimpleFilter('decimal', function ($number) {
            return \decimal($number);
        });
        $twig->addFilter($filter);
        $filter = new \Twig_SimpleFilter('interval', function ($number) {
            return \Ongoo\Utils\StringUtils::secToTime($number);
        });
        $twig->addFilter($filter);

        $filter = new \Twig_SimpleFilter('slugify', function ($text, $default = 'n-a') {
            return \Ongoo\Utils\StringUtils::slugify($text, $default);
        });
        $twig->addFilter($filter);
    }

}

?>
