<?php
namespace CPanel\Mvc;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ResponseInterface;

class Application implements ApplicationInterface
{
    /**
     * Default application event listeners
     *
     * @var array
     */
    protected $defaultListeners = array(
        'RouteListener',
        //'DispatchListener',
        //'HttpMethodListener',
        //'ViewManager',
        //'SendResponseListener',
    );
    
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

        $this->request        = $serviceManager->get('Request');
        $this->response       = $serviceManager->get('Response');
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
     * Bootstrap the application
     *
     * Defines and binds the MvcEvent, and passes it the request, response, and
     * router. Attaches the ViewManager as a listener. Triggers the bootstrap
     * event.
     *
     * @param array $listeners List of listeners to attach.
     * @return Application
     */
    public function bootstrap(array $listeners = array())
    {
        $serviceManager = $this->serviceManager;
        $events         = $this->events;

        $listeners = array_unique(array_merge($this->defaultListeners, $listeners));

        foreach ($listeners as $listener) {
            $events->attach($serviceManager->get($listener));
        }

        // Setup MVC Event
        $this->event = $event  = new MvcEvent();
        $event->setTarget($this);
        $event->setApplication($this)
              ->setRequest($this->request)
              ->setResponse($this->response);

        // Trigger bootstrap events
        $events->trigger(MvcEvent::EVENT_BOOTSTRAP, $event);

        return $this;
    }

    /**
     * Run the application
     *
     * @return self
     */
    public function run()
    {
        $events = $this->events;
        $event  = $this->event;

        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }
            if ($event->getError()) {
                return true;
            }
            return false;
        };

        // TODO: Authentication

        // Trigger route event
        $result = $events->trigger(MvcEvent::EVENT_ROUTE, $event, $shortCircuit);
        if ($result->stopped()) {
            $response = $result->last();
            if ($response instanceof ResponseInterface) {
                $event->setTarget($this);
                $event->setResponse($response);
                $events->trigger(MvcEvent::EVENT_FINISH, $event);
                $this->response = $response;
                return $this;
            }
        }

        if ($event->getError()) {
            return $this->completeRequest($event);
        }

        // Trigger dispatch event
        $result = $events->trigger(MvcEvent::EVENT_DISPATCH, $event, $shortCircuit);

        // Complete response
        $response = $result->last();
        if ($response instanceof ResponseInterface) {
            $event->setTarget($this);
            $event->setResponse($response);
            $events->trigger(MvcEvent::EVENT_FINISH, $event);
            $this->response = $response;
            return $this;
        }

        $response = $this->response;
        $event->setResponse($response);
        $this->completeRequest($event);

        die("Running");
        return $this;
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
        $serviceManager->setService('Config', $configuration);
        
        return $serviceManager->get('Application')->bootstrap(array());
    }

    /**
     * Complete the request
     *
     * Triggers "render" and "finish" events, and returns response from
     * event object.
     *
     * @param  MvcEvent $event
     * @return Application
     */
    protected function completeRequest(MvcEvent $event)
    {
        $events = $this->events;
        $event->setTarget($this);
        $events->trigger(MvcEvent::EVENT_RENDER, $event);
        $events->trigger(MvcEvent::EVENT_FINISH, $event);
        return $this;
    }
}