<?php

namespace Ongoo\Helper\Helpers
{

    use \Ongoo\Helper\Helper,
        \Ongoo\Logger\Logger;

    class HtmlHelper extends Helper
    {

        protected static $instance = null;
        
        protected $root_url = null;
        protected $root_uri = null;
        protected $js = array();
        protected $css = array();
        protected $links = array();
        
        /*
        public static function getInstance()
        {
            $app = \Ongoo\Core\Configuration::getInstance()->get('application');
            return $app['ongoo.helper.html'];
        }*/

        protected function buildRootUrl()
        {
            $ctx = $this->app['request_context'];
            $uri = $ctx->getScheme() . '://' . $ctx->getHost();
            if ($ctx->getScheme() == 'http' && $ctx->getHttpPort() != 80)
            {
                $uri .= ':' . $ctx->getHttpPort();
            } else if ($ctx->getScheme() == 'https' && $ctx->getHttpsPort() != 443)
            {
                $uri .= ':' . $ctx->getHttpsPort();
            }

            return $uri;// . $ctx->getBaseUrl();
        }

        protected function buildRootUri($default = null)
        {
            $base = $this->app['request_context']->getBaseUrl();
            $info = pathinfo($base);

            $res = preg_replace('#' . $info['filename'] . '\.php$#', '', $base);
            return $res ? $res : $default;
        }

        public function getRootUrl()
        {
            if (is_null($this->root_url))
            {
                $this->root_url = $this->buildRootUrl();
            }
            return $this->root_url;
        }

        public function getRootUri()
        {
            if (is_null($this->root_uri))
            {
                $this->root_uri = $this->buildRootUri();
            }
            return $this->root_uri;
        }

        public function addLink($rel, $href, $type, $media = null)
        {
            $this->links[] = array(
                'rel' => $rel,
                'href' => $href,
                'type' => $type,
                'media' => $media,
            );
        }

        public function css($css, $media = 'all', $type = 'text/css')
        {
            if (!preg_match('#.css$#', $css))
            {
                $css .= '.css';
            }

            $this->css[] = array(
                'rel' => 'stylesheet',
                'href' => $css,
                'media' => $media,
                'type' => $type
            );
        }

        public function js($js)
        {
            if (!preg_match('#.js$#', $js))
            {
                $js .= '.js';
            }

            $this->js[] = array(
                'type' => 'text/javascript',
                'src' => $js,
            );
        }

        public function getCss()
        {
            return $this->css;
        }

        public function getJs()
        {
            return $this->js;
        }

        public function getLinks()
        {
            return $this->links;
        }

        public function include_stylessheets()
        {
            $str = '';
            foreach ($this->getCss() as $css)
            {
                if (!preg_match('#^(http://|/)#', $css['href']))
                {
                    $css['href'] = ($this->getRootUri() ? $this->getRootUri() : '/') . 'css/' . $css['href'];
                }
                
                $str .= '<link rel="' . $css['rel'] . '" type="' . $css['type'] . '" ' . (is_null($css['media']) ? '' : 'media="' . $css['media'] . '" ') . 'href="' . $css['href'] . '" />' . "\n";
            }
            return $str;
        }

        public function include_javascripts()
        {
            foreach ($this->getJs() as $js)
            {
                if (!preg_match('#^(http://|/)#', $js['src']))
                {
                    $js['src'] = ($this->getRootUri() ? $this->getRootUri() : '/') . 'js/' . $js['src'];
                }
                
                echo '<script type="' . $js['type'] . '" src="' . $js['src'] . '"></script>' . "\n";
            }
        }

        public function include_links()
        {
            foreach ($this->getLinks() as $link)
            {
                echo '<link rel="' . $link['rel'] . '" type="' . $link['type'] . '" ' . (is_null($link['media']) ? '' : 'media="' . $link['media'] . '" ') . 'href="' . $link['href'] . '" />' . "\n";
            }
        }

    }

}

namespace
{
    function include_stylessheets(\Silex\Application $app)
    {
        if( !isset($app['ongoo.helper.html']) )
        {
            return;
        }
        
        foreach ($app['ongoo.helper.html']->getCss() as $css)
        {
            echo '<link rel="' . $css['rel'] . '" type="' . $css['type'] . '" ' . (is_null($css['media']) ? '' : 'media="' . $css['media'] . '" ') . 'href="' . $css['href'] . '" />' . "\n";
        }
    }
    
    /*
    function use_ressource($rel, $href, $type, $media = null)
    {
        \Ongoo\Helper\Helpers\HtmlHelper::getInstance()->addLink($rel, $href, $type, $media = null);
    }

    function use_javascript($js)
    {
        if (!preg_match('#.js$#', $js))
        {
            $js .= '.js';
        }

        \Ongoo\Helper\Helpers\HtmlHelper::getInstance()->js($js);
    }

    function use_stylesheet($css)
    {
        if (!preg_match('#.css$#', $css))
        {
            $css .= '.css';
        }

        \Ongoo\Helper\Helpers\HtmlHelper::getInstance()->css($css);
    }

    function include_stylessheets()
    {
        foreach (\Ongoo\Helper\Helpers\HtmlHelper::getInstance()->getCss() as $css)
        {
            echo '<link rel="' . $css['rel'] . '" type="' . $css['type'] . '" ' . (is_null($css['media']) ? '' : 'media="' . $css['media'] . '" ') . 'href="' . $css['href'] . '" />' . "\n";
        }
    }

    function include_javascripts()
    {
        foreach (\Ongoo\Helper\Helpers\HtmlHelper::getInstance()->getJs() as $js)
        {
            echo '<script type="' . $js['type'] . '" src="' . $js['src'] . '"></script>' . "\n";
        }
    }

    function include_links()
    {
        foreach (\Ongoo\Helper\Helpers\HtmlHelper::getInstance()->getLinks() as $link)
        {
            echo '<link rel="' . $link['rel'] . '" type="' . $link['type'] . '" ' . (is_null($link['media']) ? '' : 'media="' . $link['media'] . '" ') . 'href="' . $link['href'] . '" />' . "\n";
        }
    }
    */
}