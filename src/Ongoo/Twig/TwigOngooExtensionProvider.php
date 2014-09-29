<?php

namespace Ongoo\Twig;

use Silex\Application;

/**
 * Description of TwigOngooExtensionProvider
 *
 * @author paul
 */
class TwigOngooExtensionProvider implements \Silex\ServiceProviderInterface
{

    public function boot(Application $app)
    {

    }

    public function register(Application $app)
    {
        $urlHelper = \Ongoo\Helper\Helper::use_helper('UrlHelper', $app);
        $app['ongoo.helper.url'] = $app->share(function() use(&$urlHelper)
                {
                    return $urlHelper;
                }
        );

        $app['ongoo.helper.html'] = $app->share(function() use(&$app)
                {
                    $helper = \Ongoo\Helper\Helper::use_helper('HtmlHelper', $app);

                    $defaultCss = array(
                        'media' => 'all',
                        'type' => 'text/css',
                    );
                    foreach (\Ongoo\Core\Configuration::getInstance()->get('Ongoo.web.css', array()) as $k => $css)
                    {
                        if (is_array($css))
                        {
                            $conf = array_merge($defaultCss, $css);
                            $css = $k;
                            $helper->css($css, $conf['media'], $conf['type']);
                        } else
                        {
                            $helper->css($css);
                        }
                    }

                    foreach (\Ongoo\Core\Configuration::getInstance()->get('Ongoo.web.js', array()) as $js)
                    {
                        $helper->js($js);
                    }

                    foreach (\Ongoo\Core\Configuration::getInstance()->get('Ongoo.web.link', array()) as $link)
                    {
                        $helper->addLink($link['rel'], $link['href'], $link['type'], isset($link['media']) ? $link['media'] : null);
                    }

                    return $helper;
                });

        $twig = $app['twig'];
        $twig->addFunction('url_for', new \Twig_Function_Function('\url_for'), array('is_safe', 'html'));
        $filter = new \Twig_SimpleFilter('json_decode', function ($string, $asArray = true)
                {
                    return json_decode($string, $asArray);
                });
        $twig->addFilter($filter);


        $filter = new \Twig_SimpleFilter('startsWith', function ($string)
                {
                    $regex = str_replace('#', '\\#', $string);
                    return preg_match("#^$regex#", $string);
                });
        $twig->addFilter($filter);
        $filter = new \Twig_SimpleFilter('endsWith', function ($string)
                {
                    $regex = str_replace('#', '\\#', $string);
                    return preg_match("#$regex$#", $string);
                });
        $twig->addFilter($filter);

        $fct = new \Twig_SimpleFunction('include_stylesheets', function($path = 'css/') use( &$app)
                {
                    return $app['ongoo.helper.html']->include_stylessheets($path);
                }, array('is_safe' => array('html'))
        );

        $twig->addFunction($fct);


        $fct = new \Twig_SimpleFunction('include_javascripts', function($path = 'js/') use( &$app)
                {
                    return $app['ongoo.helper.html']->include_javascripts($path);
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);


        $fct = new \Twig_SimpleFunction('include_links', function() use( &$app)
                {
                    return $app['ongoo.helper.html']->include_links();
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $app['user'] = $app->share(function() use (&$app)
                {
                    return $app['session']->getGuardUser();
                });

        $fct = new \Twig_SimpleFunction('ip', function() use( &$app)
                {
                    return \ip();
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('me', function() use( &$app)
                {
                    return \me();
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('whoami', function() use( &$app)
                {
                    return \whoami();
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $fct = new \Twig_SimpleFunction('now', function() use( &$app)
                {
                    return \now();
                }, array('is_safe' => array('html'))
        );
        $twig->addFunction($fct);

        $filter = new \Twig_SimpleFilter('decimal', function ($number)
                {
                    return \decimal($number);
                });
        $twig->addFilter($filter);
        $filter = new \Twig_SimpleFilter('interval', function ($number)
                {
                    return \Ongoo\Utils\StringUtils::secToTime($number);
                });
        $twig->addFilter($filter);
        
        $filter = new \Twig_SimpleFilter('slugify', function ($text, $default = 'n-a')
                {
                    return \Ongoo\Utils\StringUtils::slugify($text, $default);
                });
        $twig->addFilter($filter);
    }

}

?>
