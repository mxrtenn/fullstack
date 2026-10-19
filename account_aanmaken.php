<?php
session_start();
require('dbConnectie.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {


$username = mysqli_real_escape_string($conn, $_POST['username']);
$naam = mysqli_real_escape_string($conn, $_POST['naam']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$wachtwoord = mysqli_real_escape_string($conn, $_POST['wachtwoord']);



$hashed_password = password_hash($wachtwoord, PASSWORD_DEFAULT);


$sql = "INSERT INTO Users (Username, Naam, Email, WW) 
        VALUES ('$username', '$naam', '$email', '$hashed_password')";
if ($conn->query($sql) === TRUE) 
    echo "Account succesvol aangemaakt!";
}

?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="forms.css?v=3">
</head>
<body>
  <div class="account-page">
    
    
    <h2>Maak je account aan</h2>
    
    
    
    <form action="account_aanmaken.php" method="post" class="compact-form">
      <label for="username">Username</label><br>
      <input id="username" name="username" type="text" required>
      

      <label for="naam">Naam</label><br>
      <input id="naam" name="naam" type="text" required>
      

      <label for="email">E-mail</label><br>
      <input id="email" name="email" type="email" required>
      

      <label for="wachtwoord">Wachtwoord</label><br>
      <input id="wachtwoord" name="wachtwoord" type="password" required>
      
      <div class="form-actions compact-actions">
        <input type="submit" value="Aanmaken">
        <a href="index.php"><button type="button">Annuleren</button></a>
      </div>
    </form>
  </div>
        

            
</body>
</html>
