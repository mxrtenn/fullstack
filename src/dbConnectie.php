<?php

    $servername = "mysql";
    $username = "root";
    $password = "password";
    $dbname = "fullstack"; 

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>