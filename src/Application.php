<?php
namespace CPanel\Mvc;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;

class Application implements ApplicationInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var \Zend\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @var \Zend\Stdlib\ResponseInterface
     */
    protected $response;

    /**
     * @var ServiceManager
     */
    protected $serviceManager = null;

    /**
     * Constructor
     *
     * @param mixed $configuration
     * @param ServiceManager $serviceManager
     */
    public function __construct($configuration, ServiceManager $serviceManager)
    {
        $this->configuration  = $configuration;
        $this->serviceManager = $serviceManager;

        $this->setEventManager($serviceManager->get('EventManager'));

        //$this->request        = $serviceManager->get('Request');
        //$this->response       = $serviceManager->get('Response');
    }

    /**
     * Set the event manager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return Application
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $this->events = $eventManager;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Get the locator object
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get the request object
     *
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Run the application
     *
     * @return self
     */
    public function run()
    {
        die("Application is running");
    }

    /**
     * Static method for quick and easy initialization of the Application.
     *
     * If you use this init() method, you cannot specify a service with the
     * name of 'ApplicationConfig' in your service manager config. This name is
     * reserved to hold the array from application.config.php.
     *
     * The following services can only be overridden from application.config.php:
     *
     * - ModuleManager
     * - SharedEventManager
     * - EventManager & Zend\EventManager\EventManagerInterface
     *
     * All other services are configured after module loading, thus can be
     * overridden by modules.
     *
     * @param array $configuration
     * @return Application
     */
    public static function init($configuration = array())
    {
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $serviceManager = new ServiceManager(new Service\ServiceManagerConfig($smConfig));
        $serviceManager->setService('ApplicationConfig', $configuration);
        
        return new Application($configuration, $serviceManager);
    }
}