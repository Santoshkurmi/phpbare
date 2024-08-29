<?php

namespace Phphelper\Core;


use Closure;
use Dotenv\Dotenv;
use ReflectionClass;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Router
{

    public function __construct(
        public string $path,
        public string $method = 'GET',
        public  $middleware = null
    ) {}
    
    private static $routes = [
        'GET' => [],
        'POST' => []
    ];
    private static $env = null;
    private static $middlewares = [];


    public static function addMiddleWare($name,$middleware){
        if(!is_callable($middleware)) die("Middleware '$name' set in routing is invalid");
        self::$middlewares[$name] = $middleware;
    }


    private static function compilePattern($pattern)
    {
        // Escape slashes and replace :param with a regex for params
        $pattern = preg_replace('/\//', '\/', $pattern); // Escape slashes
        $pattern = preg_replace('/\{(\w+)\?}/', '(?P<$1>\w*)?', $pattern); // Optional parameters
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $pattern); // Required parameters
        return '#^' . $pattern . '$#u'; // Anchors to the beginning and end of the string
        // return '#^' . preg_replace('/{(\w+)}/', '(?P<$1>\w+)', $pattern) . '$#u';
    }



private static function getAllControllerClasses(string $directory): array
{
    $controllerClasses = [];

    // Assuming all your controllers are in a directory, e.g., `app/Controllers`
    foreach (glob($directory . '*.php') as $filename) {
        // Include the file to load the class
        require_once $filename;

        // Extract the class name from the file path
        $className = basename($filename, '.php');

        // Add the fully qualified class name to the list
        $controllerClasses[] = 'App\\Controllers\\' . $className;
    }

    return $controllerClasses;
}


private static function handleAttributeRouting()
{
    $controllers = self::getAllControllerClasses(getenv('CONTROLLER_PATH'));
   
    foreach ($controllers as $controllerClass) {
        $reflectionClass = new ReflectionClass($controllerClass);

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes(Router::class);
          
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $methodReq = $route->method;
                $path = $route->path;
                if( strtolower($methodReq) == "post" ){

                    Router::post($path,[$controllerClass,$method->name],$route->middleware);
                }else{

                    // print_r($route->middleware);
                    
                    Router::get($path,[$controllerClass,$method->name],$route->middleware);

                }


                
            }//attributes
        }//attributes
    }//loo

    // echo "404 Not Found";
}


    private static function setMiddlewares($middleWaresKeyValue){
        self::hanldeDotEnv();
        $request =  Request::getInstance();
        foreach($middleWaresKeyValue as $name=>$callback){
            $request->setMiddleWare($name,$callback);
        }//
    }



    public static function get($path, $controllerAction, $middlewareName = null)
    {

        self::hanldeDotEnv();
        
       $defaultRouteToIfNotLogin = null;
        $authMiddleware = null;
        // print_r('hello');
        // print_r($middlewareName);
       if(!$middlewareName ==null){
            if(is_string($middlewareName)){
                if(!isset(self::$middlewares[$middlewareName])) die("Middleware name `$middlewareName` in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                $actual = self::$middlewares[$middlewareName];
                if( !is_callable($actual) ) die("Middleware name `$middlewareName` is invalid in in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                $authMiddleware = $actual;
            }//if string
            else if (is_array($middlewareName)){
                $authMiddleware = [];
                foreach($middlewareName as $middle ){
                    if(!isset(self::$middlewares[$middle])) die("Middleware name `$middle` in class `$controllerAction[0] in method `$controllerAction[1]` not found");

                    $actual = self::$middlewares[$middle];
                    if( !is_callable($actual) ) die("Middleware name `$middle` is invalid in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                    $authMiddleware[] = $actual;
                }//
            }//if array
            else{
                die("Middleware `$middlewareName` is invalid in routing");
            }
       }//if not null

    //    print_R($authMiddleware);

        Router::$routes['GET'][self::compilePattern($path)] = [$controllerAction, $authMiddleware, $defaultRouteToIfNotLogin];
    }

    public static function post($path, $controllerAction, $middlewareName = null)
    {
        self::hanldeDotEnv();
      

       $defaultRouteToIfNotLogin = null;
       $authMiddleware = [];
       if(!$middlewareName ==null){
            if(is_string($middlewareName)){
                if(!isset(self::$middlewares[$middlewareName])) die("Middleware name `$middlewareName` is not found in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                $actual = self::$middlewares[$middlewareName];
                if( !is_callable($actual) ) die("Middleware name `$middlewareName` is invalid in in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                $authMiddleware = $actual;
            }//if string
            else if (is_array($middlewareName)){
                $authMiddleware = [];
                foreach($middlewareName as $middle ){
                    if(!isset(self::$middlewares[$middle])) die("Middleware name `$middle` is not found in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                    $actual = self::$middlewares[$middle];
                    if( !is_callable($actual) ) die("Middleware name `$middle` is invalid in in class `$controllerAction[0] in method `$controllerAction[1]` not found");
                    $authMiddleware[] = $actual;
                }//
            }//if array
            else{
                die("Middleware `$middlewareName` is invalid in routing");
            }
       }//if not null


        Router::$routes['POST'][$path] = [$controllerAction, $authMiddleware, $defaultRouteToIfNotLogin];
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

    private static function hanldeDotEnv()
    {
        if (self::$env)
            return;
        $dotenv = Dotenv::createUnsafeImmutable('./');
        self::$env = $dotenv;

        $dotenv->load();
        // print_r($out);

        $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'RENDER_LAYOUTS_BY_DEFAULT', 'HEADER_LAYOUT_PATH', 'FOOTER_LAYOUT_PATH', 'ALLOWED_IMAGE_EXTENSIONS', 'IMAGE_UPLOAD_DIRECTORY']);
        // print_r(apache_getenv("DB_HOST"));
    }

    public static function startRouting()
    {

        $isAttributeRouting  = filter_var(getenv('ENABLE_ATTRIBUTE_ROUTING'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if($isAttributeRouting)
            self::handleAttributeRouting();
        $request = Request::getInstance();

        $response = Response::getInstance();
        $method = $request->method();
        $path = $request->path();

        if ($request->isGet()) {
            $pattern = self::matchPattern($method, $path);
            if ($pattern) {

                $actions = self::$routes[$method][$pattern];

                $authMiddleware = $actions[1];
                // $pathToRedirect = $actions[2];

                if (is_array($authMiddleware)) {
                    foreach ($authMiddleware as $eachMiddleWare) {
                        if (!is_callable($eachMiddleWare)) {
                            die("'$eachMiddleWare' is not a proper middeware for GET Router '$path'. Exiting the program");
                        }
                        call_user_func_array($eachMiddleWare, [$request,$response]);
                            // return $response->redirect($pathToRedirect);
                    }//looping each middleware
                }//if middleware are in array
                else if (is_callable($authMiddleware)) {
                    call_user_func_array($authMiddleware, [$request,$response]);
                        //  $response->redirect($pathToRedirect);
                }//if callable
                // else if(is_string($authMiddleware))
                else if ($authMiddleware != null) {
                    die("'$authMiddleware' is not a proper middeware for GET Router '$path'. Exiting the program");
                }


                $callable = $actions[0];
                if (!is_array($callable)) {
                  
                    if(is_string($callable)){
                        $full_path = getenv('VIEWS_DIR').$callable.'.php';
                        if (file_exists($full_path) ){
                            return $response->render($callable);
                            // exit();
                        }//if file exists then
                        else {
                            die("'$callable' view in routing does not exists for GET Router '$path'.Please check the path at $full_path");
                        }
                    }//if string

                    else if (is_callable($callable)) {
                        return call_user_func_array($callable, [$request, $response, self::extractParams($pattern, $path)]);
                    }
                    else{
                        die("'$callable' function cannot be called. Please check the function");
                    }
                }//if not array
                $controller = $actions[0][0];
                $action = $actions[0][1];
                // if($actions[1]=="!auth") echo $actions[1];
                // if($request->isLogin()) echo "Yes";

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

            // if (is_array($controllerAction) && isset($controllerAction[0][0]) && isset($controllerAction[0][1])) {

            $authMiddleware = $controllerAction[1];
            $pathToRedirect = $controllerAction[2];

            if (is_array($authMiddleware)) {
                foreach ($authMiddleware as $eachMiddleWare) {
                    if (!is_callable($eachMiddleWare)) {
                        die("'$eachMiddleWare' is not a proper middeware. Exiting the program");
                    }
                    call_user_func_array($eachMiddleWare, [$request,$response]);
                        // return $response->redirect($pathToRedirect);
                }//looping each middleware
            }//if middleware are in array
            else if (is_callable($authMiddleware)) {
                call_user_func_array($authMiddleware, [$request,$response]);
                    // return $response->redirect($pathToRedirect);
            }//if callable
            else if ($authMiddleware != null) {
                die("'$authMiddleware' is not a proper middeware. Exiting the program");
            }//


            $callable = $controllerAction[0];
            if (!is_array($callable)) {
                if (!is_callable($callable)) {
                    echo "$callable function cannot be called. Please check the function";
                    return;
                }
                return call_user_func_array($callable, [$request, $response]);
            }//if not array

            $controller = $controllerAction[0][0];
            $action = $controllerAction[0][1];

            if (class_exists($controller) && method_exists($controller, $action)) {
                $controllerInstance = new $controller();
                return call_user_func_array([$controllerInstance, $action], [$request, $response]);
            } else {
                echo "Controller '$controller' or method '$action' does not exist.";
            }
            // } else {
            //     echo "Route handler is not in the expected format.";
            // }
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