<html>
 <head>
  <title>PHP-Test</title>
 </head>
 <body>
   <?php
     $cookie_name = "username";
     if(!isset($_COOKIE[$cookie_name])) {
       echo "Cookie named '" . $cookie_name . "' is not present!";
     } else {
       echo "Cookie '" . $cookie_name . "' is present!<br>";
       echo "Value is: " . $_COOKIE[$cookie_name];
     }
   ?>
 </body>
</html>
