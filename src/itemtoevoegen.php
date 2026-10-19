<?php
session_start();
require('dbConnectie.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {


$product = mysqli_real_escape_string($conn, $_POST['product']);
$kleur = mysqli_real_escape_string($conn, $_POST['kleur']);
$gewicht = mysqli_real_escape_string($conn, $_POST['gewicht']);
$maat = mysqli_real_escape_string($conn, $_POST['maat']);
$looiing = mysqli_real_escape_string($conn, $_POST['looiing']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$prijs = mysqli_real_escape_string($conn, $_POST['prijs']);

if (!in_array($maat, ['Klein', 'Middel', 'Groot'], true) || !in_array($looiing, ['Natuurlijk', 'Chemisch'], true)) {
  die('Kies een geldige maat en looiing.');
}


$sql = "INSERT INTO Voorraad (Product, Kleur, Gewicht, Maat, Looiing, Status, Prijs) 
        VALUES ('$product', '$kleur', '$gewicht', '$maat', '$looiing', '$status', '$prijs')";
if ($conn->query($sql) === TRUE) 
    echo "Item succesvol toegevoegd!";
}

?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="forms.css?v=3">
</head>
<body>
  <div class="account-page">
    
    
    <h2>Voeg een item toe aan de voorraad</h2>
    
    
    
    
    <form action="itemtoevoegen.php" method="post" class="compact-form">
      <label for="product">Product</label><br>
      <input id="product" name="product" type="text" class="form-input" required>
      

      <label for="kleur">Kleur</label><br>
      <input id="kleur" name="kleur" type="text" class="form-input" required>
      

      <label for="gewicht">Gewicht (KG)</label><br>
      <input id="gewicht" name="gewicht" type="text" class="form-input" required>
      

      <label for="maat">Maat</label><br>
      <select id="maat" name="maat" class="form-input" required>
        <option value="">Kies een maat</option>
        <option value="Klein">Klein</option>
        <option value="Middel">Middel</option>
        <option value="Groot">Groot</option>
      </select>
      

      <label for="looiing">Looiing</label><br>
      <select id="looiing" name="looiing" class="form-input" required>
        <option value="">Kies een looiing</option>
        <option value="Natuurlijk">Natuurlijk</option>
        <option value="Chemisch">Chemisch</option>
      </select>
      

      <label for="status">Status</label><br>
      <input id="status" name="status" type="text" class="form-input" required>
      

      <label for="prijs">Prijs</label>
      <div class="price-input">
        <span aria-hidden="true">€</span>
        <input id="prijs" name="prijs" type="number" step="0.01" min="0" class="form-input" required>
      </div>
      

      <div class="form-actions compact-actions">
        <input type="submit" value="Toevoegen">
        <a href="voorraadbeheer.php"><button type="button">Annuleren</button></a>
      </div>
    </form>
  </div>
        

            
</body>
</html>
