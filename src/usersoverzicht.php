<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require('dbConnectie.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
  $delete_id = $_POST['delete_id'];
  $delete_sql = "DELETE FROM Users WHERE ID = $delete_id";
  
  if ($conn->query($delete_sql) === TRUE) {
    
    header("Location: usersoverzicht.php");
    exit();
  } else {
    $error_message = "Fout bij het verwijderen: " . $conn->error;
  }
}

$sql = "SELECT ID, Username, Naam, Email FROM Users";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="overzichten.css">
  <title>Gebruikersoverzicht</title>
</head>
<body>
  <a href="uitloggen.php" class="logout-link"><button class="logout-btn">Uitloggen</button></a>
  <a href="dashboard.php" class="button">Home</a>
  <div class="container">
    <h1>Gebruikersoverzicht</h1>
    <p><a href="account_aanmaken.php" class="button">Gebruiker toevoegen</a></p>
    
    <?php
    if ($result->num_rows > 0) {
      echo "<table>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Naam</th>
                <th>Email</th>
                <th>Actie</th>
              </tr>";
      
      while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["ID"]."</td>
                <td>".$row["Username"]."</td>
                <td>".$row["Naam"]."</td>
                <td>".$row["Email"]."</td>
                <td>
                  <a href='usersaanpassen.php?ID=" . $row["ID"] . "' class='button'>Aanpassen</a>
                  <form method='POST' style='display:inline;' onsubmit=\"return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?');\">
                    <input type='hidden' name='delete_id' value='" . $row["ID"] . "'>
                    <button type='submit' class='delete-button'>Verwijderen</button>
                  </form>
                </td>
              </tr>";
      }
      echo "</table>";
    } else {
      echo "<div class='no-results'>Geen gebruikers gevonden.</div>";
    }
    
    $conn->close();
    ?>
  </div>
</body>
</html>