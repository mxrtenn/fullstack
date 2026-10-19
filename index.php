<?php
session_start();
require('dbConnectie.php');

$naamErr = $wachtwoordErr = $loginErr = "";
$naam = "";

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["naam"])) {
    $naamErr = "Naam is verplicht";
  } else {
    $naam = test_input($_POST["naam"]);
  }
  
  if (empty($_POST["wachtwoord"])) {
    $wachtwoordErr = "Wachtwoord is verplicht";
  } else {
    $wachtwoord = test_input($_POST["wachtwoord"]);
  }

  
  if (empty($naamErr) && empty($wachtwoordErr)) {
    
    $sql = "SELECT id, Username, Email, WW FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $naam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      
      
      if (password_verify($wachtwoord, $user['WW'])) {
      
        $_SESSION['id'] = $user['id'];
        $_SESSION['Username'] = $user['Username'];
        $_SESSION['email'] = $user['Email'];
        header("Location: dashboard.php");
        exit;
      } else {
        $loginErr = "Wachtwoord is onjuist";
      }
    } else {
      $loginErr = "Gebruiker niet gevonden";
    }
    $stmt->close();
  }
}
?>

<html>
  <link rel="stylesheet" href="index.css">
  <title>Login</title>
<body>

<div class="login-logo">
  <img src="logo1.png">
</div>

<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="compact-form">
Username: <input type="text" name="naam" value="<?php echo htmlspecialchars($naam); ?>" required>
<?php if (!empty($naamErr)) { ?>
  <span class="error">* <?php echo $naamErr;?></span>
<?php } ?>

<br><br>

Wachtwoord: <input type="password" name="wachtwoord" required>
<?php if (!empty($wachtwoordErr)) { ?>
  <span class="error">* <?php echo $wachtwoordErr;?></span>
<?php } ?>

<?php if (!empty($loginErr)) { ?>
  <br><span class="error">* <?php echo $loginErr;?></span>
<?php } ?>

<div class="form-actions compact-actions">
  <input type="submit" value="Verzenden">
  <a href="account_aanmaken.php" style="text-decoration:none;"><button type="button" class="account-btn">Account aanmaken</button></a>
</div>
</form>
</body>
</html>
