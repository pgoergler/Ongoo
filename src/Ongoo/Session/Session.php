<?php

namespace Ongoo\Session;

use Symfony\Component\HttpFoundation\Session\Session AS SfSession;

/**
 * Description of Session
 *
 * @author paul
 */
class Session extends SfSession
{

    public function setFlashAlert($name, $text, $classname = 'info')
    {
        $this->getFlashBag()->add('alert.' . $name, array('class' => $classname, 'text' => $text));
    }

    public function getFlashesAlert()
    {
        $alerts = array();
        foreach ($this->getFlashBag()->all() as $name => $messages)
        {
            foreach ($messages as $message)
            {
                if (preg_match('#^alert\.(.+?)$#', $name, $m))
                {
                    $alerts[$m[1]] = $message;
                } else
                {
                    $this->setFlash($name, $message);
                }
            }
        }
        return $alerts;
    }

}

?>
