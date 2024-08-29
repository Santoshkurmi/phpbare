<?php


namespace App\Controllers;
use Phphelper\Core\Response;
use Phphelper\Core\Router;


$auth = function ($req,Response $response){
    return $response->redirect('/login');
};

class HomeController{

    #[Router(path:'/dog',method:'GET',middleware:['auth'])]
    public function index(){
        echo "Hello";
    }//home


    #[Router(path:'/cat',method:'GET',middleware:['auth','hello'])]
    public function man(){
        echo "man";
    }//home


}