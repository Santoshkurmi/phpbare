<?php

namespace App\Core;

use App\Controllers\AuthController;
use Exception;

class Router
{
    private static $routes = [
        'GET' => [],
        'POST' => []
    ];


    private static function compilePattern($pattern)
    {
        // Escape slashes and replace :param with a regex for params
          $pattern = preg_replace('/\//', '\/', $pattern); // Escape slashes
        $pattern = preg_replace('/\{(\w+)\?}/', '(?P<$1>\w*)?', $pattern); // Optional parameters
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $pattern); // Required parameters
        return '#^' . $pattern . '$#u'; // Anchors to the beginning and end of the string
        // return '#^' . preg_replace('/{(\w+)}/', '(?P<$1>\w+)', $pattern) . '$#u';
    }

    public static function get($path, $controllerAction)
    {
        $class = $controllerAction[0];
        Router::$routes['GET'][self::compilePattern($path)] = $controllerAction;
    }

    public static function post($path, $controllerAction)
    {
        Router::$routes['POST'][$path] = $controllerAction;
    }

    public static function matchPattern($method, $uri)
    {
        foreach (Router::$routes[$method] as $pattern => $action) {
            if (preg_match($pattern, $uri, $matches)) {
                return $pattern;
            }
        }
        return null;
    }

    private static function extractParams($pattern, $uri)
    {
        preg_match($pattern, $uri, $matches);

        return new ExtraParams(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
    }

    public static function startRouting()
    {
        $request = Request::getInstance();
        $response = Response::getInstance();
        $method = $request->method();
        $path = $request->path();

        if ($request->isGet()) {
            $pattern = self::matchPattern($method, $path);
            if ($pattern) {
                $actions = self::$routes[$method][$pattern];
                $controller = $actions[0];
                $action = $actions[1];

                // return $this->callAction($action, $this->extractParams($pattern, $uri));
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controllerInstance = new $controller();
                    return call_user_func_array([$controllerInstance, $action], [$request, $response, self::extractParams($pattern, $path)]);
                } else {
                    echo "Controller '$controller' or method '$action' does not exist.";
                }
            } else {
                echo "No route found for path '$path'";
            }

            // Handle 404 Not Found
            return;


        }//if get

        if (isset(Router::$routes[$method][$path])) {
            $controllerAction = Router::$routes[$method][$path];

            if (is_array($controllerAction) && isset($controllerAction[0]) && isset($controllerAction[1])) {
                $controller = $controllerAction[0];
                $action = $controllerAction[1];
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controllerInstance = new $controller();
                    return call_user_func_array([$controllerInstance, $action], [$request, $response]);
                } else {
                    echo "Controller '$controller' or method '$action' does not exist.";
                }
            } else {
                echo "Route handler is not in the expected format.";
            }
        } else {
            echo "No route defined for path '$path' this URL.";
        }
    }
}

class ExtraParams
{
    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
}