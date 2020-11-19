<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes

// home index route (also is the default)
$router->add('', ['controller' => 'Home', 'action' => 'index']);

// generic route with one or more arguments
// TODO: protect arguments against special characters?
$router->add('{controller}/{action}/?{args:.*}');

// generic route for controller/action without paramenter
$router->add('{controller}/{action}');

// generic route for controller without method, it will call "indexAction" from the controller
$router->add('{controller}/?');
    
$router->dispatch($_SERVER['QUERY_STRING']);
