<?php

namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;

class HomeController{

    public function index(Request $request,Response $response){
        if( $request->isLogin() ){
            $db = $request->getDatabase();

            $id = $db->insert('users',['name'=>'Santosh Kurmi','email'=>'xantosh121@gmail.com','isAdmin'=>true,'age'=>34]);

            $user = $db->fetch('select * from users where email= ? and isAdmin = ? ',['xantosh121@gmail.com',true]);
            if($user)
                echo $user['name'];

            $row = $db->query('delete from users where id = ? ',[5]);
            echo $row->affected_rows;




            // $db->insert('users',);
            $response->render('home/homepage');
        }//if login
        else{
            $response->redirect('/login');
        }
    }

    public function about(Request $request,Response $response){
        if($request->isLogin()){
            $response->render('about/about');
        }
        else{
            $response->redirect('/login');
        }
    }


}