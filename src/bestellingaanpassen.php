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
$selectedProducts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submittedProducts = $_POST['products'] ?? [];
  if (is_array($submittedProducts)) {
    foreach ($submittedProducts as $submittedProduct) {
      $productId = filter_var($submittedProduct, FILTER_VALIDATE_INT);
      if ($productId && $productId > 0) {
        $selectedProducts[] = $productId;
      }
    }
  }
  $selectedProducts = array_values(array_unique($selectedProducts));
  $datum = $_POST['datum'] ?? '';
  $klant = trim($_POST['klant'] ?? '');
  $status = trim($_POST['status'] ?? '');
    $row = [
    'Datum' => $datum,
    'Klant' => $klant,
    'Status' => $status,
    ];

  if (!$selectedProducts || $datum === '' || $klant === '') {
    $error = 'Kies minimaal één product en vul de datum en klant in.';
    } else {
    $conn->begin_transaction();
    try {
      $placeholders = implode(',', array_fill(0, count($selectedProducts), '?'));
      $check = $conn->prepare("SELECT idVoorraad, Prijs FROM Voorraad WHERE idVoorraad IN ($placeholders) FOR UPDATE");
      $bindValues = array_merge([str_repeat('i', count($selectedProducts))], $selectedProducts);
      $bindParams = [];
      foreach ($bindValues as $key => &$value) {
        $bindParams[$key] = &$value;
      }
      call_user_func_array([$check, 'bind_param'], $bindParams);
      $check->execute();
      $productResult = $check->get_result();
      $products = $productResult ? $productResult->fetch_all(MYSQLI_ASSOC) : [];
      $check->close();

      if (count($products) !== count($selectedProducts)) {
        throw new RuntimeException('Een of meer gekozen producten bestaan niet meer in de voorraad.');
      }

      $totalCents = 0;
      foreach ($products as $product) {
        $totalCents += (int)round((float)$product['Prijs'] * 100);
      }
      $totaal = intdiv($totalCents, 100) . '.' . str_pad((string)($totalCents % 100), 2, '0', STR_PAD_LEFT);
      $firstProductId = (int)$products[0]['idVoorraad'];

      $stmt = $conn->prepare("UPDATE Bestellingen SET idVoorraad = ?, Datum = ?, Klant = ?, Status = ?, Totaal = ? WHERE idBestellingen = ?");
      $stmt->bind_param("issssi", $firstProductId, $datum, $klant, $status, $totaal, $id);
      $stmt->execute();
      $stmt->close();

      $deleteLines = $conn->prepare("DELETE FROM BestellingProducten WHERE idBestellingen = ?");
      $deleteLines->bind_param("i", $id);
      $deleteLines->execute();
      $deleteLines->close();

      $line = $conn->prepare("INSERT INTO BestellingProducten (idBestellingen, idVoorraad, Prijs) VALUES (?, ?, ?)");
      foreach ($products as $product) {
        $productId = (int)$product['idVoorraad'];
        $price = (string)$product['Prijs'];
        $line->bind_param("iis", $id, $productId, $price);
        $line->execute();
      }
      $line->close();

      $conn->commit();
      $conn->close();
      header("Location: bestellingen.php");
      exit;
    } catch (Throwable $exception) {
      $conn->rollback();
      $error = $exception instanceof RuntimeException
        ? $exception->getMessage()
        : 'Opslaan mislukt. Controleer of de koppeltabel BestellingProducten is aangemaakt.';
    }
    }
}

if (!$row && $id > 0) {
  $stmt = $conn->prepare("SELECT idBestellingen, idVoorraad, Datum, Klant, Status, Totaal FROM Bestellingen WHERE idBestellingen = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
      $lineStmt = $conn->prepare("SELECT idVoorraad FROM BestellingProducten WHERE idBestellingen = ?");
      $lineStmt->bind_param("i", $id);
      $lineStmt->execute();
      $lineResult = $lineStmt->get_result();
      while ($line = $lineResult->fetch_assoc()) {
        $selectedProducts[] = (int)$line['idVoorraad'];
      }
      $lineStmt->close();
      if (!$selectedProducts && !empty($row['idVoorraad'])) {
        $selectedProducts[] = (int)$row['idVoorraad'];
      }
    }
    $stmt->close();
}

  $producten = $conn->query("SELECT idVoorraad, Product, Prijs FROM Voorraad ORDER BY Product");
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
    
    
    <h2>Bestelling aanpassen</h2>

    <?php if ($error !== ''): ?>
      <p role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    
    
    <form action="bestellingaanpassen.php?ID=<?php echo htmlspecialchars($id); ?>" method="post" class="compact-form">
      <input type="hidden" name="ID" value="<?php echo htmlspecialchars($id); ?>">
      <label for="datum">Datum</label><br>
      <input id="datum" name="datum" value="<?php echo htmlspecialchars($row["Datum"] ?? ''); ?>" type="date" required>

      <label for="klant">Klant</label><br>
      <input id="klant" name="klant" value="<?php echo htmlspecialchars($row["Klant"] ?? ''); ?>" type="text" required>
      

      <label for="status">Status</label><br>
      <input id="status" name="status" value="<?php echo htmlspecialchars($row["Status"] ?? ''); ?>" type="text">

      <label for="product-picker">Producten</label>
      <details id="product-picker" class="product-picker">
        <summary data-empty-label="Kies een of meer producten"><?php echo count($selectedProducts) > 0 ? count($selectedProducts) . ' product(en) geselecteerd' : 'Kies een of meer producten'; ?></summary>
        <div class="product-picker-options">
        <?php if ($producten && $producten->num_rows > 0): ?>
          <?php while ($product = $producten->fetch_assoc()): ?>
            <label>
              <input type="checkbox" name="products[]" value="<?php echo (int)$product['idVoorraad']; ?>" <?php echo in_array((int)$product['idVoorraad'], $selectedProducts, true) ? 'checked' : ''; ?>>
              <?php echo htmlspecialchars($product['Product'] . ' (ID ' . $product['idVoorraad'] . ') - EUR ' . number_format((float)$product['Prijs'], 2, ',', '.')); ?>
            </label><br>
          <?php endwhile; ?>
        <?php else: ?>
          <p>Er zijn nog geen producten in de voorraad.</p>
        <?php endif; ?>
        </div>
      </details>

      <p>Totaal wordt automatisch berekend op basis van de geselecteerde producten. Huidig totaal: EUR <?php echo number_format((float)($row['Totaal'] ?? 0), 2, ',', '.'); ?></p>

      <div class="form-actions compact-actions">
        <input type="submit" value="Opslaan">
        <a href="bestellingen.php"><button type="button">Annuleren</button></a>
      </div>
    </form>
  </div>
        

            
</body>
<script>
document.querySelectorAll('.product-picker').forEach(function (picker) {
  var summary = picker.querySelector('summary');
  var checkboxes = picker.querySelectorAll('input[type="checkbox"]');
  var updateCount = function () {
    var selectedCount = picker.querySelectorAll('input[type="checkbox"]:checked').length;
    summary.textContent = selectedCount > 0
      ? selectedCount + ' product(en) geselecteerd'
      : summary.dataset.emptyLabel;
  };
  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener('change', updateCount);
  });
});
</script>
</html>