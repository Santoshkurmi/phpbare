<?php

namespace Phphelper\Core;


use Dotenv\Dotenv;


class Router
{
    private static $routes = [
        'GET' => [],
        'POST' => []
    ];
    private static $env = null;

  



    private static function compilePattern($pattern)
    {
        // Escape slashes and replace :param with a regex for params
          $pattern = preg_replace('/\//', '\/', $pattern); // Escape slashes
        $pattern = preg_replace('/\{(\w+)\?}/', '(?P<$1>\w*)?', $pattern); // Optional parameters
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $pattern); // Required parameters
        return '#^' . $pattern . '$#u'; // Anchors to the beginning and end of the string
        // return '#^' . preg_replace('/{(\w+)}/', '(?P<$1>\w+)', $pattern) . '$#u';
    }

    public static function get($path, $controllerAction,$isAuth=false,$ifNotAuthRedirectTo='default_from_.env')
    {
        self::hanldeDotEnv();
        if($ifNotAuthRedirectTo=="default_from_.env")
            $defaultRouteToIfNotLogin = getenv('DEFAULT_REDIRECT_IF_NOT_AUTH');
        else 
            $defaultRouteToIfNotLogin = $ifNotAuthRedirectTo;

        $class = $controllerAction[0];
        Router::$routes['GET'][self::compilePattern($path)] = [$controllerAction,$isAuth,$defaultRouteToIfNotLogin];
    }

    public static function post($path, $controllerAction,$isAuth=false,$ifNotAuthRedirectTo='default_from_.env')
    {
        self::hanldeDotEnv();
        if($ifNotAuthRedirectTo=="default_from_.env")
        $defaultRouteToIfNotLogin = getenv('DEFAULT_REDIRECT_IF_NOT_AUTH');
    else 
        $defaultRouteToIfNotLogin = $ifNotAuthRedirectTo;
        


        Router::$routes['POST'][$path] = [$controllerAction,$isAuth,$defaultRouteToIfNotLogin];
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

    private static function hanldeDotEnv(){
        if(self::$env) return;
        $dotenv = Dotenv::createUnsafeImmutable('./');
        self::$env = $dotenv;

        $dotenv->load();
        // print_r($out);
    
        $dotenv->required(['DB_HOST','DEFAULT_REDIRECT_IF_NOT_AUTH','DB_DATABASE','DB_USERNAME','DB_PASSWORD','RENDER_LAYOUTS_BY_DEFAULT','HEADER_LAYOUT_PATH','FOOTER_LAYOUT_PATH','ALLOWED_IMAGE_EXTENSIONS','IMAGE_UPLOAD_DIRECTORY']);
        // print_r(apache_getenv("DB_HOST"));
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
                $controller = $actions[0][0];
                $action = $actions[0][1];
                if( $actions[1] )  if(!$request->isLogin() ) return $response->redirect($actions[2]);

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

            if (is_array($controllerAction) && isset($controllerAction[0][0]) && isset($controllerAction[0][1])) {
                if($controllerAction[1]){

                    if(!$request->isLogin() )

                    return $response->redirect($controllerAction[2]);

                } 

                $controller = $controllerAction[0][0];
                $action = $controllerAction[0][1];

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