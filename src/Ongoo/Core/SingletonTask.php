<?php

namespace Ongoo\Core;

/**
 * Description of SingletonTask
 *
 * @author paul
 */
abstract class SingletonTask extends Task
{

    protected $lockFile = null;

    protected function configure()
    {
        parent::configure();
        $this->addOption('pid-file', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'PID file to exclude concurent run', null);
        $this->addOption('logger', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Logger name to use', 'cli');
    }

    public function signalUsr1($signo)
    {

    }

    protected function onStart(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        parent::onStart($input, $output);

        $loggerName = $input->getOption('logger');

        $root = $this->app['logger.factory']->get($loggerName);
        $root->set('app', $this->getStrName());
        $this->app['logger.factory']->add($root, 'root');
        $this->app['logger'] = $root;

        declare( ticks = 1);
        pcntl_signal(SIGUSR1, array(&$this, 'signalUsr1'));

        $this->lockFile = $input->getOption('pid-file');
        if (!$this->lockFile)
        {
            $this->lockFile = '/tmp/' . $this->getStrName() . '.pid';
        }

        if (file_exists($this->lockFile))
        {
            $pid = \trim(file_get_contents($this->lockFile));
            if (posix_kill(intval($pid), SIGUSR1))
            {
                throw new \RuntimeException($this->getName() . " already started pid [$pid] in : " . $this->lockFile);
            }
        }
        file_put_contents($this->lockFile, getmypid());
    }

    protected function onFinish()
    {
        unlink($this->lockFile);
        parent::onFinish();
    }

}

?>
