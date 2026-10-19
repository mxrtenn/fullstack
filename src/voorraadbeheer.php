<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require('dbConnectie.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
  $delete_id = $_POST['delete_id'];
  $delete_sql = "DELETE FROM Voorraad WHERE idVoorraad = $delete_id";
  
  if ($conn->query($delete_sql) === TRUE) {
    
    header("Location: voorraadbeheer.php");
    exit();
  } else {
    $error_message = "Fout bij het verwijderen: " . $conn->error;
  }
}

$sql = "SELECT idVoorraad, Product, Kleur, Gewicht, Maat, Looiing, Status, Prijs FROM Voorraad";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="overzichten.css">
  <title>Voorraadbeheer</title>
</head>
<body>
  <a href="uitloggen.php" class="logout-link"><button class="logout-btn">Uitloggen</button></a>
  <a href="dashboard.php" class="button">Home</a>
  <div class="container">
    <h1>Voorraadbeheer</h1>
    
    <p><a href="itemtoevoegen.php" class="button">Item toevoegen</a></p>
    <?php
    if ($result->num_rows > 0) {
      echo "<div class='table-scroll'><table class='inventory-table'>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Kleur</th>
                <th>Gewicht (KG)</th>
                <th>Maat</th>
                <th>Looiing</th>
                <th>Status</th>
                <th>Prijs</th>
                <th>Actie</th>
              </tr>";
      
      while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["idVoorraad"]."</td>
                <td>".$row["Product"]."</td>
                <td>".$row["Kleur"]."</td>
                <td>".$row["Gewicht"]."</td>
                <td>".$row["Maat"]."</td>
                <td>".$row["Looiing"]."</td>
                <td>".$row["Status"]."</td>
                <td class='price-cell'>€ " . number_format((float)$row["Prijs"], 2, ',', '.') . "</td>
                <td class='actions-cell'>
                  <a href='voorraadaanpassen.php?ID=" . $row["idVoorraad"] . "' class='button'>Aanpassen</a>
                  <form method='POST' style='display:inline;' onsubmit=\"return confirm('Weet je zeker dat je dit item wilt verwijderen?');\">
                    <input type='hidden' name='delete_id' value='" . $row["idVoorraad"] . "'>
                    <button type='submit' class='delete-button'>Verwijderen</button>
                  </form>
                </td>
              </tr>";
      }
      echo "</table></div>";
    } else {
      echo "<div class='no-results'>Geen items gevonden.</div>";
    }
    
    $conn->close();
    ?>
  </div>
</body>
</html>