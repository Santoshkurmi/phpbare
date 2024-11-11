# This is a php library  as a lightweight tool to create web application

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