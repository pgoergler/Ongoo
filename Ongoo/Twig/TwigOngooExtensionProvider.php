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

                    foreach( \Ongoo\Core\Configuration::getInstance()->get('Ongoo.web.js', array()) as $js )
                    {
                        $helper->js($js);
                    }

                    return $helper;
                });

        $twig = $app['twig'];
        // $twig->addFunction('url_for', new \Twig_Function_Function('\url_for'), array('is_safe', 'html'));

        $filter = new \Twig_SimpleFilter('json_decode', function ($string, $asArray = true) {
            return json_decode($string, $asArray);
        });
        $twig->addFilter($filter);

        $fct = new \Twig_SimpleFunction('include_stylesheets', function() use( &$app)
                        {
                            return $app['ongoo.helper.html']->include_stylessheets();
                        }, array('is_safe' => array('html'))
        );

        $twig->addFunction($fct);


        $fct = new \Twig_SimpleFunction('include_javascripts', function() use( &$app)
                        {
                            return $app['ongoo.helper.html']->include_javascripts();
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
    }

}

?>
