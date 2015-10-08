<?php
/**
 * cPanel
 *
 * @link      http://github.com/jpg-consulting/cpanel-mvc
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CPanel\Mvc\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Stdlib\ArrayUtils;

class ServiceManagerConfig extends Config
{
    /**
     * Services that can be instantiated without factories
     *
     * @var array
     */
    protected $invokables = array(
        'SharedEventManager' => 'Zend\EventManager\SharedEventManager',
    );

    /**
     * Service factories
     *
     * @var array
     */
    protected $factories = array(
        'EventManager'  => 'CPanel\Mvc\Service\EventManagerFactory',
        // ZendFramework does NOT do this here!
        'Request' => 'CPanel\Mvc\Service\RequestFactory',
        'Response' => 'CPanel\Mvc\Service\ResponseFactory',
    );

    /**
     * Abstract factories
     *
     * @var array
     */
    protected $abstractFactories = array();

    /**
     * Aliases
     *
     * @var array
     */
    protected $aliases = array();

    /**
     * Shared services
     *
     * Services are shared by default; this is primarily to indicate services
     * that should NOT be shared
     *
     * @var array
     */
    protected $shared = array(
        'EventManager' => false,
    );

    /**
     * Delegators
     *
     * @var array
     */
    protected $delegators = array();

    /**
     * Initializers
     *
     * @var array
     */
    protected $initializers = array();

    /**
     * Constructor
     *
     * Merges internal arrays with those passed via configuration
     *
     * @param  array $configuration
     */
    public function __construct(array $configuration = array())
    {
        $this->initializers = array(
            'EventManagerAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof EventManagerAwareInterface) {
                    $eventManager = $instance->getEventManager();

                    if ($eventManager instanceof EventManagerInterface) {
                        $eventManager->setSharedManager($serviceLocator->get('SharedEventManager'));
                    } else {
                        $instance->setEventManager($serviceLocator->get('EventManager'));
                    }
                }
            },
            'ServiceManagerAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($serviceLocator instanceof ServiceManager && $instance instanceof ServiceManagerAwareInterface) {
                    $instance->setServiceManager($serviceLocator);
                }
            },
            'ServiceLocatorAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof ServiceLocatorAwareInterface) {
                    $instance->setServiceLocator($serviceLocator);
                }
            },
        );

        $this->factories['ServiceManager'] = function (ServiceLocatorInterface $serviceLocator) {
            return $serviceLocator;
        };

        parent::__construct(ArrayUtils::merge(
            array(
                'invokables'         => $this->invokables,
                'factories'          => $this->factories,
                'abstract_factories' => $this->abstractFactories,
                'aliases'            => $this->aliases,
                'shared'             => $this->shared,
                'delegators'         => $this->delegators,
                'initializers'       => $this->initializers,
            ),
            $configuration
        ));
    }
}
