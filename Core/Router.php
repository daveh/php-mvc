<?php

namespace Core;

/**
 * Router
 *
 * PHP version 7.0
 */
class Router
{
    /**
     * Request URI index constants
     */
    const CONTROLLER_URI_INDEX = 1;
    const ACTION_URI_INDEX = 2;
    const ID_URI_INDEX = 3;
    
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $params = [];

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function add($route, $params = [])
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) {
                    $controller_object->$action();

                } else {
                    throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
                }
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Dispatch the request URI, creating the controller object
     * and running the action method with id and other params.
     * An automatic routing without using routing table
     *
     * Request URI format:
     * [/controller[/action[/id]]][?param1=value1[&param2=value2...]]
     *
     * @param string|null $requestUri The request URI
     * 
     * @return void
     */
    public function dispatchRequest($requestUri = null)
    {
        // Parse request URI
        $requestUri = $requestUri ?? $_SERVER['REQUEST_URI'];
        [$request, $query] = strpos($requestUri, '?') === false ? [$requestUri, ''] : explode('?', $requestUri);
        $request = strpos($request, '/') === false ? [] : explode('/', $request);
        $queryParams = [];
        parse_str($query, $queryParams);

        // Dispatch request
        $controller = !empty($request[self::CONTROLLER_URI_INDEX]) ? $request[self::CONTROLLER_URI_INDEX] : \App\Config::DEFAULT_CONTROLLER;
        $controller = $this->convertToStudlyCaps($controller);
        $this->params['controller'] = $controller;
        $controller = $this->getNamespace() . $controller;
        $action = !empty($request[self::ACTION_URI_INDEX]) ? $request[self::ACTION_URI_INDEX] : \App\Config::DEFAULT_ACTION;
        $action = $this->convertToCamelCase($action);
        $this->params['action'] = $action;
        if ($id = $request[self::ID_URI_INDEX] ?? null) {
            if (array_key_exists('id', $queryParams)) {
                $queryParams['id'] = $id;
            } else {
                $queryParams = ['id' => $id] + $queryParams;
            }
        }
        $this->params['id'] = $id;
        $this->params['queryParams'] = $queryParams;

        if (class_exists($controller)) {
            $controller_object = new $controller($this->params);

            if (preg_match('/action$/i', $action) == 0) {
                $controller_object->$action($queryParams);
            } else {
                throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
            }
        } else {
            throw new \Exception("Controller class $controller not found");
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
