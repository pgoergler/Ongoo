<?php

namespace Ongoo\Controllers;

/**
 * Description of OngooController
 *
 * @author paul
 */
class OngooController extends \Ongoo\Core\Controller
{

    use OngooControllerTrait;
    
    public function preExecute($action)
    {
        $this->timzeonePreExecute($action);
    }

}
