<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ongoo\Controllers;

class CallbackResolver extends \Silex\CallbackResolver
{

    protected $app;

    public function __construct(\Pimple $app)
    {
        parent::__construct($app);
        $this->app = &$app;
    }

    /**
     * Returns true if the string is a valid service method representation.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isValid($name)
    {
        if ((is_string($name) && (false !== strpos($name, '::'))))
        {
            list($class, $method) = explode('::', $name, 2);
            return class_exists($class);
        }
        return false;
    }

    /**
     * Returns a callable given its string representation.
     *
     * @param string $name
     *
     * @return array A callable array
     *
     * @throws \InvalidArgumentException In case the method does not exist.
     */
    public function convertCallback($name)
    {
        list($class, $method) = explode('::', $name, 2);
        return array($class, 'execute', $method);
    }

}
