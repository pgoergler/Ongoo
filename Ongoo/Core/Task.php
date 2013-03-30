<?php

namespace Ongoo\Core;

/**
 * Description of Command
 *
 * @author paul
 */
abstract class Task extends \Symfony\Component\Console\Command\Command
{

    protected $app = null;
    protected $input = null;
    protected $output = null;

    public function __construct(\Silex\Application &$application)
    {
        parent::__construct();
        $this->app = $application;
    }

    public function getStrName()
    {
        return str_replace(':', '_', $this->getName());
    }

    public function &getApplication()
    {
        return $this->app;
    }

    protected function configure()
    {
        $this->addOption('env', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Task environment', 'prod');
    }

    protected function initialize(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->app['debug'] = $input->hasOption('debug') ? $input->getOption('debug') : true;
        $this->app['application.mode'] = $input->hasOption('env') ? $input->getOption('env') : 'dev';
    }

    protected function bootstrap(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->initialize($input, $output);
        $app = $this->getApplication();
        include __W_BOOTSTRAP_DIR . '/configure.php';
        include __W_BOOTSTRAP_DIR . '/console_plugins.php';
        $app->boot();

        $app['orm']->init($app['quartz.databases']);

        $root = $app['logger.factory']->get('cli');
        $root->set('app', $this->getStrName());
        $app['logger.factory']->add($root, 'root');
        $app['logger'] = $root;

        \Ongoo\Core\Configuration::getInstance()->set('application', $app);
        $this->app = $app;
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        try
        {
            $this->bootstrap($input, $output);
            $this->onStart($input, $output);
        } catch (\Exception $e)
        {
            $this->onInitException($e);
            return -1;
        }
        try
        {
            $this->process($input, $output);
        } catch (\Exception $e)
        {
            $this->onException($e);
        }
        $this->onFinish();
    }

    protected function onInitException(\Exception $e)
    {
        $this->app['logger']->error($e);
    }

    protected function onException(\Exception $e)
    {
        $this->app['logger']->error($e);
    }

    protected function onStart(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function onFinish()
    {

    }

    abstract protected function process(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output);
}

?>
