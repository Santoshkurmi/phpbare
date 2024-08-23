<?php
use App\Controllers\HomeController;
use Dotenv\Dotenv;

error_reporting(1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
// require_once 'app/routes/routes.php';


use App\Core\Router;


Router::get('/',[HomeController::class,'index']);


Router::startRouting();

