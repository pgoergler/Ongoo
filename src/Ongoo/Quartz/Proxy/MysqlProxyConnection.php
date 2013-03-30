<?php

namespace Ongoo\Quartz\Proxy;

/**
 * Description of MysqlProxyConnection
 *
 * @author paul
 */
class MysqlProxyConnection extends \Quartz\Connection\MysqlConnection
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
            $this->logger->debug($sQuery);
        }
        return parent::query($sQuery, $unbuffered);
    }

    public function insert(\Quartz\Object\Table $table, $object)
    {
        if ($this->logger)
        {
            $this->logger->debug($sQuery);
        }
        return parent::insert($table, $object);
    }

    public function update($table, $query, $object, $options = array())
    {
        if ($this->logger)
        {
            $this->logger->debug($sQuery);
        }
        return parent::update($table, $query, $object, $options);
    }

}

?>
