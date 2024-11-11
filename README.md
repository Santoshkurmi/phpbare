# This is a php library  as a lightweight tool to create web application

This library is available in composer that can be installed using composer.
```bash
composer require phphelper/phphelper
```
This library name is `phphelper`

- This can be used for creating web application having routing,controller,middleware,mysql database,request,response handling,authenciation in better way.

- It is created only for learning purpose only.
It is useful for people who don't want to use library like laravel as it is complex for beginner.

- It has only 4 to 5 helper classes that help in performing routing,controller,request,response,session and cooking handling.

So only use it if you want to create web application having backend php but don't want to use heavy library and also don't want to use core php only as it has lot of boiler code.

# Installation
```bash
composer require phphelper/phphelper
```

# Guide
- To use this library, you need to setup autoloader using composer. Make sure you have composer and php installed in your system. 
- Also for routing, you have to setup your backend server to route all the request to a specific file let say index.php

In this tutorial, I am going to use xampp server for doing that.

## Routing setup in xampp
To make sure routing to work, you have to command the apache server of xampp to redirect all the request to a specific php file. Let say the entry point to be 'index.php'

Also, allow the public/ folder to access the resource directly as they may contian css,js,images static assets. 

To do that, you can use this code and paste it in the root directory of xampp i.e htdocs/here

- Crete a file in htdocs/.htaccess and paste the following in it.
```
<IfModule mod_rewrite.c>
    RewriteEngine On

    # If the requested file or directory exists, serve it directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

      # If the request is for the public directory, serve it directly
    RewriteCond %{REQUEST_URI} !^/public/

    # Otherwise, forward the request to index.php
    RewriteRule ^(.*)$ index.php [L,QSA]

</IfModule>

```
This will make xampp server i.e apache to redirect all requests to index.php except from the /public/ folder.

# Autoloader setup
To setup autoloder, type the following command.

```bash
composer dump-autoload
```
You can first create composer project using composer init or something. Watch youtube for how to create composer project and autoloader.

# Index.php file
Install the phphelper from the above guide
Add following content in index.php.

```php
<?php
use Phphelper\Core\Request;
use Phphelper\Core\Response;
use Phphelper\Core\Router;


error_reporting(1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './vendor/autoload.php';

require_once './app/routes/routes.php';



Router::startRouting();
```
Here, we have imported autoloader, and routes from /app/routes/routes.php which you have to create yourself.It doesn't matter where you place routes.php,just import it correctly in index.php thats all.


# Composer.json
It should look following similar

```json
{
    "name": "sunil/roomsewa",
    "description": "This is my project about roomsewa",
    "type": "project",
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "authors": [
        {
            "name": "Sunil Bhattarai",
            "email": "sunilbhattarai131@gmail.com"
        }
    ],
    "require": {
        // "phpmailer/phpmailer": "^6.9",
        "phphelper/phphelper": "^3.0"
    }
}

```
In autoload psr-4, make sure it is "App\\":"app/", here app/ is the entry point of your files like controllers,views etc. you can replace it to have like "Src\\":"src/" to make the code in src folder.


# .env file in root directory
You have to create .env file to configure the library to use certain things.
```yaml

#Database config file
DB_HOST = "localhost"
DB_DATABASE = "roomsewa"
DB_USERNAME = "root"
DB_PASSWORD = ""


#enable attribute routing and controller path for tha

ENABLE_ATTRIBUTE_ROUTING = true
CONTROLLER_PATH = "app/Controllers/"


#wheter to render header/footer by default
RENDER_LAYOUTS_BY_DEFAULT = true

#header,footer default path.
HEADER_LAYOUT_PATH = "layouts/header"
FOOTER_LAYOUT_PATH = "layouts/footer"


#views folder location for render
VIEWS_DIR = "app/views/"

#Image type supported for uploading images
ALLOWED_IMAGE_EXTENSIONS = "jpg,jpeg,png,webp"

#folder to save the image
IMAGE_UPLOAD_DIRECTORY = "public/uploads/"

# DEFAULT_REDIRECT_IF_NOT_AUTH = "/login"
```
Here, if you use mysql database, configure it in the .env variable


# Routing
Routing is done in many ways.
Simple way is to use Router class. It support GET/POST with callback of function or controller method, views etc.

Example of how router.php might look
```php
Router::get('/contact','home/contact' );
Router::get('/about','home/about' );
//home

//Here get take first parameter the path, second parameter is string here. Which means it a view(html,.php file having html)

// home/contact means app/views/home/contact.php file to serve.

Router::get("/contact",functon(Request $req,Response $res){
    echo "Hello world";
};);

// In this , this function is called if request is get from /contact

//Also you can use controller methods too. Cretae controller in app/controllers/YourController.php class having same filename

Router::get("/home",[YourController::class,"methodName"]);

// This both way of routing is supported in laravel too

```

# Controller
Using this librar to hanlde auth look like this.
```php
<?php

namespace App\Controllers;
use App\Helpers\MailSender;
use App\Helpers\Validation;
use Phphelper\Core\Request;
use Phphelper\Core\Response;
use Phphelper\Core\Router;

class AuthController{

    #[Router(path:'/login/{type?}', method:'GET')]
    public function loginPage(Request $request,Response $response,$params){
        $type = $params->type;
        return $response->render('auth/login',['type'=>$type]);       
    }//loginPage


    #[Router(path:'/register/{type?}', method:'GET')]
    public function registerPage(Request $request,Response $response,$params){
        $type = $params->type;
        return $response->render('auth/register',['type'=>$type]);       
    }//loginPage

 


    #[Router(path:'/register', method:'POST')]
    public function register(Request $request,Response $response){

        $db = $request->getDatabase();
        [$data,$errors] = Validation::validateRegister($request,$db);
        $role = $data['role'];

        if( !empty($errors)  ) return $response->redirect(null,['errors'=>$errors,'type'=>$role,'data'=>$data,'c_password'=>$request->c_password ] );


        $path = $request->uploadImage('id_photo');
        
        if(!$path)  return $response->redirect(null,['errors'=>$errors,'type'=>$role,'data'=>$data ] );

        $data['id_photo'] = $path;
        $data['password'] = hash('sha256', $data['password']);

        //email sending process
        $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        $subject = 'Email verification';
        $body = '<p>Your verification code is: <b style="font-size: 30px;">' . $verification_code . '</b></p>';


        $is_development_mode = getenv('IS_DEVELOPMENT_MODE');
        if($is_development_mode=="no"){
            
            $isMailSent = MailSender::send( $data['email'], $data['full_name'], $subject, $body );

            if(!$isMailSent) {
                $data['password'] = $request->password;
                $errors['email'] = 'Email server is not working to send otp. So registration is cancelled for now';
                return $response->redirect(null,['errors'=>$errors,'type'=>$role,'data'=>$data ] );
            }

            $data['verification_code'] = $verification_code;

        }//if development mode is no, then send the actual email
        else{
            $data['verification_code'] = getenv('DUMMY_DEFAULT_OTP');
        }//dont send the actual email.. Default verification code from .env
       

        $isInserted = $db->insert('users',$data);
        if(!$isInserted){
            echo "Something went wrong with the database";
        }//

        return $response->redirect("/login/$role");
        
        
    }//loginPage

    



    #[Router(path:'/login', method:'POST')]
    public function login(Request $request,Response $response){
        $email = $request->email;
        $password = $request->password;
        $hasedPassword = hash('sha256',$password);
        $db = $request->getDatabase();
       
        $user = $db->fetchOne('users',['email'=>$email,'password'=>$hasedPassword]);

        if(!$user) return $response->redirect(null,['email'=>$email,'password'=>$password,'error'=>'Invalid Credentials','type'=>'tenant']);

        if($user['email_verified_at'] == null){
            return $response->redirect("/verify_email/".$user['id']);
        }

        $request->setUser($user);
        return $response->redirect('/');


    }//if login



    #[Router(path:'/verify_email/{id}', method:'GET')]
    public function getVerifyEmail(Request $request,Response $response,$params){
        $id = $params->id;
        $expiryMinutes = getenv('OTP_EXPIRY_DURATION_MINUTES');
        
        return $response->render('auth/email_verify',['id'=>$id,'expire'=>$expiryMinutes]);

    }//if login



    #[Router(path:'/verify_email', method:'POST')]
    public function verifyEmail(Request $request,Response $response){
        $id = $request->id;
        $verification_code = $request->verification_code;

        $date = date("Y-m-d H:i:s");
        // $sql = "UPDATE tenant SET email_verified_at = NOW() WHERE email = '" . $email . "' AND verification_code = '" . $verification_code . "' AND otp_created_at > NOW() - INTERVAL 2 MINUTE";
        
        $db = $request->getDatabase();

        $user = $db->fetchOne('users',['id'=>$id]);
        if(!$user){
            return $response->redirect(null,['id'=>$id,'error'=>"This user is not found"]);
        }
        if($user['email_verified_at'] != null){
            return $response->redirect(null,['id'=>$id,'error'=>"User is already verified. You may proceed to login"]);
        }

        $otp = $user['verification_code'];

        if($otp != $verification_code){
            return $response->redirect(null,['id'=>$id,'error'=>"OTP is wrong"]);
        }

        $expiryMinutes = getenv('OTP_EXPIRY_DURATION_MINUTES');
        $result = $db->query("UPDATE users set email_verified_at = NOW() where id = ? and verification_code = ?  and otp_created_at > NOW() - INTERVAL $expiryMinutes MINUTE",[$id,$verification_code]);
        
        print_r($result);
        if($result->affected_rows>0){
            return $response->redirect('/login/'.$user['role']);
        }
        else{
            return $response->redirect(null,['id'=>$id,'error'=>"OTP is expired"]);
        }



    }//verify email


    
    #[Router(path:'/resend_otp', method:'POST')]
    public function resendOtp(Request $request,Response $response){
        $user_id = $request->id;
        $db = $request->getDatabase();

        $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        $subject = 'Email verification';
        $body = '<p>Your verification code is: <b style="font-size: 30px;">' . $verification_code . '</b></p>';


        $is_development_mode = getenv('IS_DEVELOPMENT_MODE');
        if($is_development_mode=="no"){

            $user = $db->fetchOne('users',['id'=>$user_id]);

            
            $isMailSent = MailSender::send( $user['email'], $user['full_name'], $subject, $body );

            if(!$isMailSent) {
                return $response->redirect(null,['id'=>$user_id,'error'=>"OTP cannot be sent due to some errors"]);
                
            }

        }//if development mode is no, then send the actual email
        else{
            $verification_code = (int)(getenv('DUMMY_DEFAULT_RESEND_OTP'));
        }//dont send the actual email.. Default verification code from .env
       

        $isUpdate = $db->query('UPDATE users set verification_code = ? , otp_created_at = NOW() where id = ?',[$verification_code,$user_id]);
        if($isUpdate->affected_rows==0){
            return $response->redirect(null,['id'=>$user_id,'error'=>"OTP cannot be sent due to some errors"]);
        }

        return $response->redirect(null,['msg'=>"Otp is sent successfully"]);
 
     }//resend otp



    #[Router(path:'/logout', method:'GET')]
    public function logout(Request $request,Response $response){
       $request->logout();
       return $response->redirect('/');

    }//logout



}


```

# Helper classes
- Router: To handle routing of get,post. It support pattern matching of path, callback as function or controller,views, also it support middleware as third paramter. It also support writing directly by placing routing above the method of controller without writing it in routes.php.

- Request: It contains all the query, post data, user login data,session and cookie and authenciation and logout mechanism.Also it support image uploading too.

- Response: It contains logic for redirection, rendering html files, error redirection and so on

- Database: It has all helper methods to query mysql database as simpler as possible.


I would have created the documentation for all the methods but it is my personal project and no one is going to use it so.

# Project created using this library.
I have projected created by my friend using this library. If you want to learn how to use it, checkout the repo. Also this library has just four classes, which you can read yourself to understand how it is working.

https://github.com/Santoshkurmi/roomsewa

This project completely use this library only to create fully functional web application having routing, controller,middleware,authorization.

Just see the following files and folder only int the project.

- index.php
- .htaccess
- .env
- public/
- app/
- compser.json and .lock


Anything else just ignore them as they are not part of the project.
After cloning, copy the project in root directory of xampp htdocs folder.