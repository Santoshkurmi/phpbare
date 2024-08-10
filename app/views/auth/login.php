
<?php
use App\Core\Request;

$request = Request::get();


?>
    <form action="login" method="post">
        <span>
        <?php
        if(isset($error)) echo $error
        ?>
        </span><br/>
        <input type="email" name="email" value="xantosh121@gmail.com" placeholder="Enter your email"> <br/>
    
        <input type="password" name="password" placeholder="Enter your password"> <br/>
        <input type="submit" value="Login"/>
    </form>