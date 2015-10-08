<?php
/**
 * cPanel
 *
 * @link      http://github.com/jpg-consulting/cpanel-mvc
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CPanel\Mvc;

use Zend\EventManager\EventsCapableInterface;

interface ApplicationInterface extends EventsCapableInterface
{
    /**
     * Get the locator object
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager();

    /**
     * Get the request object
     *
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest();

    /**
     * Get the response object
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponse();

    /**
     * Run the application
     *
     * @return self
     */
    public function run();
}
