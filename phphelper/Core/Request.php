<?php

namespace Phphelper\Core;

use Phphelper\Core\Database;
class Request {
    private $data;
    private static $instance = null;
    private $files;
    // private $config;

    private function __construct() {
        session_start(); // Start the session
        // $this->config = new Config();

        // Merge GET, POST, and other request data into a single array
        $this->data = array_merge($_GET, $_POST);
        // $this->files = $_FILES;
    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get(){
        return self::getInstance();
    }


    public function getDatabase(){
        $database = new Database();
        return $database;
    }

    // Magic method to handle dynamic property access
    public function __get($name) {
        return $this->data[$name] ?? null; // Return the value if it exists, otherwise return null
    }

    // Method to check if a request parameter exists
    public function has($name) {
        return isset($this->data[$name]);
    }

    // Method to get all request data
    public function all() {
        return $this->data;
    }

    // Method to check if the request method is POST
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // Method to check if the request method is GET
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    // Method to handle file uploads
    public function getFile($name) {
        return $_FILES[$name] ?? null; // Return the file if it exists, otherwise return null
    }

    // Method to check if a file was uploaded
    public function hasFile($name) {
        return isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK;
    }

    // Method to retrieve file data (name, tmp_name, size, etc.)
    public function getTempPathOfFile($name) {
        return $this->getFile($name)['tmp_name'];
    }

    public function getFileExtension($name){
        return strtolower( pathinfo($this->getFile($name)['name'],PATHINFO_EXTENSION) );
    }
    private function isImage($name){
        return getimagesize($this->getTempPathOfFile($name));
    }

    public function isImageSupported($name){
        if(!$this->isImage($name)) return false;
        $imageAllowedExtensions = explode(',',getenv('ALLOWED_IMAGE_EXTENSIONS'));
        // print_r($imageAllowedExtensions);
        return in_array($this->getFileExtension($name),$imageAllowedExtensions);
    }

    public function setupImageUploadDirectory(){
        $imageUploadDirectory = getenv('IMAGE_UPLOAD_DIRECTORY');
        // echo $imageUploadDirectory;
        if (!is_dir($imageUploadDirectory)) {
            // Create the directory with proper permissions (e.g., 0755)
            if (!mkdir($imageUploadDirectory, 0755, true)) {
                die("Failed to create directory.");
            }
        }
    }

    public function uploadImage($name){
        $this->setupImageUploadDirectory();

        $sourcePath = $this->getTempPathOfFile($name);

        $imageName = $this->getFile($name)['name'].microtime(true);
        $hashedName = hash('sha256',$imageName).".".$this->getFileExtension($name);

        $destLocation = getenv('IMAGE_UPLOAD_DIRECTORY').$hashedName;
        $isUploaded =  move_uploaded_file($sourcePath,$destLocation);
        if($isUploaded) return $hashedName;
        else return null;
    }

    

    // Method to set a session variable
    public function setSession($key, $value) {
        if(is_array($value)){
        $_SESSION[$key] = $value;
        return;
        }

        $_SESSION[$key] = get_object_vars($value);
    }

    // Method to get a session variable
    public function getSession($key) {
        return $_SESSION[$key] ?? null;
    }

    public function setUser($user) {
        $this->setSession('user',$user);

    }

    public function method(){
        return $_SERVER['REQUEST_METHOD'];
    }

    public function path(){
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    // Method to get a session variable
    public function getUser() {
        if( $this->hasSession('user') ){
            return new DummyUser($_SESSION['user']);
        }
        return null;
    }



    // Method to check if a session variable exists
    public function hasSession($key) {
        return isset($_SESSION[$key]);
    }
    function isLogin() {
    return $this->hasSession('user');
}

    // Method to remove a session variable
    public function removeSession($key) {
        unset($_SESSION[$key]);
    }

    // Method to destroy the session
    public function destroySession() {
        session_unset();
        session_destroy();
    }

    public function logout($redirectTo='/'){
        $this->destroySession();
        http_response_code(302);
        header('Location: '.$redirectTo);
    }


}

class DummyUser{
        private $user;
        public function __construct($user){
            $this->user = $user;
        }

        public function __get($name){
            return $this->user[$name] ??null;
        }
        public function __set($key,$value){
            $this->user[$key]=$value;
        }

    }

