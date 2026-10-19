<?php
$servername = "mysql";
$username = ini_get(option: 'mysqli.default_user'); //from php.ini
$password = ini_get(option: "mysqli.default_pw"); //from php.ini

try {
    $conn = new mysqli($servername, $username, $password, "games");
    if ($conn->connect_error) {
        error_log($conn->connect_error);
        exit("Connection DB failed");
    }
} catch (Exception $e) {
    error_log($e);
    exit("Connection DB failed");
}

return $conn;
