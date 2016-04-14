<?php

$loader = require "vendor/autoload.php";
$loader->register();

use Core\Core;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

$app = new Core();

/**
 * ROUTING
 * */
$app->map('/', function(){
   return new Response("home page");
});

$app->map('/{id}', function($id){
    return new Response("page #" . $id);
});

/**
 * END ROUTING
 */

/**
 * DISPATCHER
*/

$app->on('request', function(\Event\RequestEvent $event){
    if ($event->getRequest()->getPathInfo() == '/admin') {
        echo "Access denied!!!";
        exit;
    }
});

/**
 * END DISPATCHER
 */
$response = $app->handle($request);

$response->send();
