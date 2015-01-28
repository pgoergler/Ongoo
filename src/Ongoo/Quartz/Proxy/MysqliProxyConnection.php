<?php

namespace Ongoo\Quartz\Proxy;

/**
 * Description of MysqliProxyConnection
 *
 * @author paul
 */
class MysqliProxyConnection extends \Quartz\Connection\MysqliConnection
{

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    public function configure()
    {
        parent::configure();
        if (isset($this->extra['logger']))
        {
            $logger = $this->extra['logger'];
            if ($logger instanceof \Psr\Log\LoggerInterface)
            {
                $this->logger = $logger;
            } elseif ($logger instanceof \Closure)
            {
                $this->logger = $logger();
            } else
            {
                $this->logger = \Logging\LoggersManager::getInstance()->get($logger);
            }
        }
    }

    public function query($sQuery, $unbuffered = false)
    {
        if ($this->logger)
        {
            $logger = $this->logger;
            $rows = preg_split("#\n#", $sQuery);
            array_walk($rows, function(&$row) use($logger){
                $row = \trim($row);
            });
            
            $this->logger->debug(implode(' ', $rows));
        }
        return parent::query($sQuery, $unbuffered);
    }

}

?>
