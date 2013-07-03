CurlLogin
================
Simple class to log in on webpage and send requests.


Example
----------------

```php
<?php

use \CurlLogin\CurlLogin;

require_once "./../curlLogin/src/CurlLogin.php";

$username = "some username";
$password = "some password";
$url = "http://www.example.com/login.php"; // usually action url in login form
$cookiesPath = "cookie.txt"; // path to save temporary cookies
$referer = "http://www.google.com"; // referer

$curlLogin = new CurlLogin($url, $cookiesPath);
$curlLogin->setReferer($referer); // set referer
$curlLogin->setUserAgent("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36"); // set user agent
$curlLogin->login($username, $password); // login

$url = 'http://www.example.com/auth/getContent.php';
echo $curlLogin->sendRequest($url); // returns content located on url


?>
```

Warning
----------------
Standard input name for login and password are email and password. If you want to change them, you can do it by calling setLoginInputName and setPasswordInputName.

License
----------------
MIT License
