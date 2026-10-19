<?php
session_start();

$naam = isset($_SESSION['Username']) ? htmlspecialchars($_SESSION['Username']) : '';
$email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';


?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <a href="uitloggen.php" class="logout-link"><button class="logout-btn">Uitloggen</button></a>
  <div class="container">
    <div class="welcome-section">
      <h1>Dashboard</h1>
      <div class="info-box">
        <p><span class="info-label">Welkom:</span> <?php echo $naam ?: 'N/A'; ?></p>
      </div>
      <div class="info-box">
        <p><span class="info-label">E-mail:</span> <?php echo $email ?: 'N/A'; ?></p>
      </div>
    </div>
    
    <div class="button-section">
      <a href="usersoverzicht.php"><button>Gebruikers</button></a>
      <a href="voorraadbeheer.php"><button>Voorraad</button></a>
      <a href="bestellingen.php"><button>Bestellingen</button></a>
    </div>
  </div>
</body>
</html>