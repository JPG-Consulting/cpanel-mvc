<?php
/**
 * cPanel
 *
 * @link      http://github.com/jpg-consulting/cpanel-mvc
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CPanel\Mvc;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class RouteListener extends AbstractListenerAggregate
{
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'));
    }

    /**
     * Listen to the "route" event and attempt to route the request
     *
     * If no matches are returned, triggers "dispatch.error" in order to
     * create a 404 response.
     *
     * Seeds the event with the route match on completion.
     *
     * @param  MvcEvent $e
     * @return null|Router\RouteMatch
     */
    public function onRoute($e)
    {
        $target     = $e->getTarget();
        $request    = $e->getRequest();
        //$router     = $e->getRouter();
        //$routeMatch = $router->match($request);

        //if (!$routeMatch instanceof Router\RouteMatch) {
        //    $e->setError(Application::ERROR_ROUTER_NO_MATCH);

        //    $results = $target->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $e);
        //    if (count($results)) {
        //        return $results->last();
        //    }

        //    return $e->getParams();
        //}

        //$e->setRouteMatch($routeMatch);
        //return $routeMatch;
    }
}
