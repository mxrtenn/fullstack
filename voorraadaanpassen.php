<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require('dbConnectie.php');

$id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
$row = array();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kleur = mysqli_real_escape_string($conn, $_POST['kleur'] ?? '');
    $gewicht = mysqli_real_escape_string($conn, $_POST['gewicht'] ?? '');
    $maat = mysqli_real_escape_string($conn, $_POST['maat'] ?? '');
    $looiing = mysqli_real_escape_string($conn, $_POST['looiing'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? ''); 
    $prijs = mysqli_real_escape_string($conn, $_POST['prijs'] ?? '');

    if (!in_array($maat, ['Klein', 'Middel', 'Groot'], true) || !in_array($looiing, ['Natuurlijk', 'Chemisch'], true)) {
      $error = 'Kies een geldige maat en looiing.';
        $row = [
            'Kleur' => $kleur,
            'Gewicht' => $gewicht,
            'Maat' => $maat,
            'Looiing' => $looiing,
            'Status' => $status,
            'Prijs' => $prijs,
        ];
        } else {
          $stmt = $conn->prepare("UPDATE Voorraad SET Kleur = ?, Gewicht = ?, Maat = ?, Looiing = ?, Status = ?, Prijs = ? WHERE idVoorraad = ?");
          $stmt->bind_param("ssssssi", $kleur, $gewicht, $maat, $looiing, $status, $prijs, $id);

          if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: voorraadbeheer.php");
            exit;
          } else {
            $error = "Update mislukt: " . $conn->error;
            $stmt->close();
            $row = [
              'Kleur' => $kleur,
              'Gewicht' => $gewicht,
              'Maat' => $maat,
              'Looiing' => $looiing,
              'Status' => $status,
              'Prijs' => $prijs,
            ];
          }
    }
}

if (!$row && $id > 0) {
    $stmt = $conn->prepare("SELECT idVoorraad, Kleur, Gewicht, Maat, Looiing, `Status`, Prijs FROM Voorraad WHERE idVoorraad = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();

?>

<html>
<head>
  <link rel="stylesheet" href="forms.css">
  <style>
    .account-page {
      width: 100%;
      max-width: 500px;
      text-align: center;
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    }
    
    .account-page h2 {
      text-align: center;
      margin-top: 0;
    }
  </style>
</head>
<body>
  <div class="account-page">
    
    
    <h2>Voorraad aanpassen</h2>

    <?php if ($error !== ''): ?>
      <p role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    
    
    <form action="voorraadaanpassen.php?ID=<?php echo htmlspecialchars($id); ?>" method="post" class="compact-form">
      <input type="hidden" name="ID" value="<?php echo htmlspecialchars($id); ?>">
      <label for="kleur">Kleur</label><br>
      <input id="kleur" name="kleur" value="<?php echo $row["Kleur"]; ?>" type="text">

      <label for="gewicht">Gewicht (KG)</label><br>
      <input id="gewicht" name="gewicht" value="<?php echo $row["Gewicht"]; ?>" type="text">
      

      <label for="maat">Maat</label><br>
      <select id="maat" name="maat" class="form-input" required>
        <?php $maatOpties = ['Klein', 'Middel', 'Groot']; ?>
        <?php if (!in_array($row['Maat'] ?? '', $maatOpties, true)): ?>
          <option value="<?php echo htmlspecialchars($row['Maat'] ?? ''); ?>" selected><?php echo htmlspecialchars($row['Maat'] ?? 'Onbekend'); ?></option>
        <?php endif; ?>
        <?php foreach ($maatOpties as $optie): ?>
          <option value="<?php echo $optie; ?>" <?php echo ($row['Maat'] ?? '') === $optie ? 'selected' : ''; ?>><?php echo $optie; ?></option>
        <?php endforeach; ?>
      </select>

      <label for="looiing">Looiing</label><br>
      <select id="looiing" name="looiing" class="form-input" required>
        <?php $looiingOpties = ['Natuurlijk', 'Chemisch']; ?>
        <?php if (!in_array($row['Looiing'] ?? '', $looiingOpties, true)): ?>
          <option value="<?php echo htmlspecialchars($row['Looiing'] ?? ''); ?>" selected><?php echo htmlspecialchars($row['Looiing'] ?? 'Onbekend'); ?></option>
        <?php endif; ?>
        <?php foreach ($looiingOpties as $optie): ?>
          <option value="<?php echo $optie; ?>" <?php echo ($row['Looiing'] ?? '') === $optie ? 'selected' : ''; ?>><?php echo $optie; ?></option>
        <?php endforeach; ?>
      </select>

      <label for="status">Status</label><br>
      <input id="status" name="status" value="<?php echo $row["Status"]; ?>" type="text">

      <label for="prijs">Prijs</label>
      <div class="price-input">
        <span aria-hidden="true">€</span>
        <input id="prijs" name="prijs" value="<?php echo $row["Prijs"]; ?>" type="number" step="0.01" min="0">
      </div>

      <div class="form-actions compact-actions">
        <input type="submit" value="Opslaan">
        <a href="voorraadbeheer.php"><button type="button">Annuleren</button></a>
      </div>
    </form>
  </div>
        

            
</body>
</html>