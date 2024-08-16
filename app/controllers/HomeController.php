<?php

namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;

class HomeController{

    public function index(Request $request,Response $response){
        if( $request->isLogin() ){
            $db = $request->getDatabase();

            // $id = $db->insert('users',['name'=>'Santosh Kurmi','email'=>'xantosh1212@gmail.com','isAdmin'=>true,'age'=>34]);
            // print_r($id);
            // $user = $db->fetchOne('users',['isAdmin'=>true]);

            // $user = $db->fetchAll('users',['isAdmin'=>true]);
            // if($user)
            //     print_r($user);

            // $user = $db->fetchAllSql('select * from users where isAdmin = ?',[true]);
            // echo $user[1]['name'];

            // $rows = $db->update('users',['name'=>'hello'],['isAdmin'=>true]);
            // print_r($rows);

            // $rows = $db->delete('users',['id'=>10]);
            // print_r($rows);
            // $row = $db->query('delete from users where id = ? ',[5]);
            // echo $row->affected_rows;
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