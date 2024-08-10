<?php

namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Core\ResponseData;

class AuthController{

    
    public function index(Request $request,Response $response){
        if($request->isLogin()){
            return $response->redirect('/');
        }
        $response->render('auth/login');
    }

    public function login(Request $request,Response $response){
       
        $email = $request->email;
        $password = $request->password;

        if($email=="xantosh121@gmail.com" && $password=="123456"){
            $request->setUser(['email'=>'xantosh121@gmail.com','name'=>'Santosh Kurmi']);
            $response->redirect('/');
            return;
        }
        

        $data = [
        'error'=>'Invalid Credential',
        // 'data'=>['Santosh Kurmi','Rahul','Mantu don']
       ];
        $response->render('auth/login',$data);
    }

    public function logout(Request $request){
        $request->logout();
        
    }

}