<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require('dbConnectie.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
  $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
  if ($delete_id) {
    $conn->begin_transaction();
    $deleteLines = $conn->prepare("DELETE FROM BestellingProducten WHERE idBestellingen = ?");
    $deleteLines->bind_param("i", $delete_id);
    $deleteLines->execute();
    $deleteLines->close();

    $deleteOrder = $conn->prepare("DELETE FROM Bestellingen WHERE idBestellingen = ?");
    $deleteOrder->bind_param("i", $delete_id);
    $deleteOrder->execute();
    $deleteOrder->close();
    $conn->commit();
    header("Location: bestellingen.php");
    exit();
  }
}

$sql = "SELECT b.idBestellingen, b.Datum, b.Klant, b.Totaal, b.Status,
               COALESCE(
                 GROUP_CONCAT(DISTINCT CONCAT(v.Product, ' (ID ', v.idVoorraad, ')') ORDER BY v.Product SEPARATOR ', '),
                 CONCAT('Product ID ', b.idVoorraad)
               ) AS Producten
        FROM Bestellingen b
        LEFT JOIN BestellingProducten bp ON bp.idBestellingen = b.idBestellingen
        LEFT JOIN Voorraad v ON v.idVoorraad = bp.idVoorraad
        GROUP BY b.idBestellingen, b.Datum, b.Klant, b.Totaal, b.Status, b.idVoorraad";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="overzichten.css">
  <title>Bestellingen</title>
</head>
<body>
  <a href="uitloggen.php" class="logout-link"><button class="logout-btn">Uitloggen</button></a>
  <a href="dashboard.php" class="button">Home</a>
  <div class="container">
    <h1>Bestellingen</h1>
    
    <p><a href="bestellingtoevoegen.php" class="button">Bestelling toevoegen</a></p>
    <?php
    if ($result->num_rows > 0) {
      echo "<table>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Datum</th>
                <th>Klant</th>
                <th>Status</th>
                <th>Totaal</th>
                <th>Actie</th>
              </tr>";
      
      while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["idBestellingen"]."</td>
                <td>".htmlspecialchars($row["Producten"])."</td>
                <td>".htmlspecialchars($row["Datum"])."</td>
                <td>".htmlspecialchars($row["Klant"])."</td>
                <td>".htmlspecialchars($row["Status"])."</td>
                <td>€ " . number_format((float)$row["Totaal"], 2, ',', '.') . "</td>
                <td>
                  <a href='bestellingaanpassen.php?ID=" . $row["idBestellingen"] . "' class='button'>Aanpassen</a>
                  <form method='POST' style='display:inline;' onsubmit=\"return confirm('Weet je zeker dat je deze bestelling wilt verwijderen?');\">
                    <input type='hidden' name='delete_id' value='" . $row["idBestellingen"] . "'>
                    <button type='submit' class='delete-button'>Verwijderen</button>
                  </form>
                </td>
              </tr>";
      }
      echo "</table>";
    } else {
      echo "<div class='no-results'>Geen bestellingen gevonden.</div>";
    }
    
    $conn->close();


    ?>
  </div>
</body>
</html>