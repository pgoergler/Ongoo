<?php

namespace Ongoo\Quartz\Proxy;

/**
 * Description of PgsqlProxyConnection
 *
 * @author paul
 */
class PgsqlProxyConnection extends \Quartz\Connection\PgsqlConnection
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

    public function close()
    {
        if( $this->logger )
        {
            foreach( $this->transactions as $tr)
            {
                $this->logger->debug("rolling back transaction $tr");
            }
        }
        parent::close();
    }
}

?>
