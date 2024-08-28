<?php


namespace App\Controllers;
use Phphelper\Core\Response;
use Phphelper\Core\Route;


$auth = function ($req,Response $response){
    return $response->redirect('/login');
};

class HomeController{

    #[Route(path:'/dog',method:'GET',middleware:['auth'])]
    public function index(){
        echo "Hello";
    }//home


    #[Route(path:'/cat',method:'GET',middleware:['auth','hello'])]
    public function man(){
        echo "man";
    }//home


}