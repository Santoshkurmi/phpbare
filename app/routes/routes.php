<?php
namespace App\Routes;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Core\Router;


Router::get('/',[HomeController::class,'index']);
Router::get('/about',[HomeController::class,'about']);

Router::get('/login',[AuthController::class,'index']);
Router::post('/login',[AuthController::class,'login']);
Router::get('/logout',[AuthController::class,'logout']);
// Router::get('/login/{id}',[AuthController::class,'login']);
// Router::get('/login',[AuthController::class,'login']);