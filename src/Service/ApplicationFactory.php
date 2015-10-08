<?php
/**
 * cPanel
 *
 * @link      http://github.com/jpg-consulting/cpanel-mvc
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CPanel\Mvc\Service;

use CPanel\Mvc\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationFactory implements FactoryInterface
{
    /**
     * Create the Application service
     *
     * Creates a CPanel\Mvc\Application service, passing it the configuration
     * service and the service manager instance.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Application
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Application($serviceLocator->get('Config'), $serviceLocator);
    }
}
