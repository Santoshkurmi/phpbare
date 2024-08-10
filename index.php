<?php
require_once './vendor/autoload.php';
require_once './app/routes/routes.php';

use App\Controllers\HomeController;
// use App\Core\Request;
use App\Core\Router;

$m = new HomeController();
// $m->index();


try {
    Router::startRouting();
} catch (Exception $e) {
    echo $e->getMessage();
}
