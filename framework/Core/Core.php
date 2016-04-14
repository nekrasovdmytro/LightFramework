<?php

namespace Core;

use Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Core implements HttpKernelInterface
{
    /**
     * @var RouteCollection $routes
    */
    protected $routes;

    /**
     * @var EventDispatcher $dispatcher
    */
    protected $dispatcher;

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->dispatcher = new EventDispatcher();
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $event = new RequestEvent();
        $event->setRequest($request);

        $this->dispatcher->dispatch('request', $event);

        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $attributes = $matcher->match($request->getPathInfo());
            $controller = $attributes['controller'];

            unset($attributes['controller']);

            $response = call_user_func_array($controller, $attributes);
        } catch (ResourceNotFoundException $e) {
            $response = new Response("Not found", Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /**
     * @param string $event
     * @param callable $callback
    */
    public function on($event, $callback)
    {
        $this->dispatcher->addListener($event, $callback);
    }

    public function map($path, $controller)
    {
        //$this->routes[$path] = $controller;
        $this->routes->add($path, new Route($path, array(
            'controller' => $controller
        )));
    }
}