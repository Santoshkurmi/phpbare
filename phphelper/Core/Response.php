<?php
namespace Phphelper\Core;

class Response {
    private $statusCode = 200;
    private static $instance = null;
    private $headers = [];
    private $data = null;
    
    private $viewPath = null;
    private $renderLayouts = true;
    // private $config;
    private $footer=null,$header=null;


    public function __construct(){
        // $this->config = new Config();
        $this->renderLayouts  = filter_var(getenv('RENDER_LAYOUTS_BY_DEFAULT'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        // echo "Hey".getenv('DB_HOST');
    }

    public function withFooter($footerPath){
        $this->footer = $footerPath;
        return $this;
    }
    public function withHeader($headerPath){
        $this->header = $headerPath;
        return $this;
    }
    // Set the HTTP status code
    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Add or set a header
    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    // Set JSON data
    public function json($data, $statusCode = 200) {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->setHeader('Content-Type', 'application/json');
        $this->send();
        // return $this;
    }

    public function disableLayouts($isRender){
        $this->renderLayouts = !$isRender;
        return $this;
    }

    // Set view and data for rendering
    public function render($view, $data = []) {
        $this->viewPath = $view;
        $this->data = $data;
        $this->send();
    }

     public function redirect($url =null,$data = [] ,$statusCode=302) {
        if($url==null)
            $url = getallheaders()['Referer'];
        $_SESSION['data'] = $data;
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit(); // Stop further script execution
     }

    // Send the response
    private function send() {
        // echo __DIR__;
        // Set HTTP status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send response data
        // echo $this->renderLayouts;
        if ($this->viewPath) {
            // Extract data to variables
            extract($this->data);
            if( isset($_SESSION['data']) )
                extract($_SESSION['data']);

                unset($_SESSION['data']);
            // $data = $this->data;
            $req = Request::getInstance();

            $auth = $req->isLogin();
            $user = $req->getUser();
            $viewDirectory = getenv('VIEWS_DIR');


            if($this->renderLayouts){
            if($this->header)
                require_once $viewDirectory.$this->header.".php";     
            else
                require_once $viewDirectory.getenv('HEADER_LAYOUT_PATH').".php";
           
            require_once $viewDirectory. $this->viewPath . '.php';
            
            if($this->footer)
                require_once $viewDirectory.$this->footer.".php";
            else
                require_once $viewDirectory.getenv('FOOTER_LAYOUT_PATH').".php";
            return;
            }
            
            if($this->header)
                require_once $viewDirectory.$this->header.".php";     
           
            require_once $viewDirectory . $this->viewPath . '.php';
            
            if($this->footer)
                require_once $viewDirectory.$this->footer.".php";
            
            // Include the view file
            // require_once __DIR__ . '/../views/' . $this->viewPath . '.php';
        } elseif ($this->data !== null) {
            echo json_encode($this->data);
        }

        exit();
    }
}


class ResponseData{
    private $data;

    public function __construct($data){
        $this->data =$data;
    }

    public function __get($name){
        return $this->data[$name] ?? null;
    }
}