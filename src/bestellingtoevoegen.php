<?php
session_start();
require('dbConnectie.php');

$error = '';
$selectedProducts = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

      $stmt = $conn->prepare("INSERT INTO Bestellingen (idVoorraad, Datum, Klant, Totaal) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("isss", $firstProductId, $datum, $klant, $totaal);
      $stmt->execute();
      $orderId = $conn->insert_id;
      $stmt->close();

      $line = $conn->prepare("INSERT INTO BestellingProducten (idBestellingen, idVoorraad, Prijs) VALUES (?, ?, ?)");
      foreach ($products as $product) {
        $productId = (int)$product['idVoorraad'];
        $price = (string)$product['Prijs'];
        $line->bind_param("iis", $orderId, $productId, $price);
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
        : 'Bestelling toevoegen mislukt. Controleer of de koppeltabel BestellingProducten is aangemaakt.';
    }
  }
}

$producten = $conn->query("SELECT idVoorraad, Product, Prijs FROM Voorraad ORDER BY Product");
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="forms.css?v=3">
</head>
<body>
  <div class="account-page">
    
    
    <h2>Voeg een bestelling toe</h2>

    <?php if ($error !== ''): ?>
      <p role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    
    
    
    <form action="bestellingtoevoegen.php" method="post" class="compact-form">
      <label for="product-picker">Producten</label>
      <details id="product-picker" class="product-picker">
        <summary data-empty-label="Kies een of meer producten"><?php echo count($selectedProducts) > 0 ? count($selectedProducts) . ' product(en) geselecteerd' : 'Kies een of meer producten'; ?></summary>
        <div class="product-picker-options">
        <?php if ($producten && $producten->num_rows > 0): ?>
          <?php while ($product = $producten->fetch_assoc()): ?>
            <label>
              <input type="checkbox" name="products[]" value="<?php echo (int)$product['idVoorraad']; ?>" <?php echo in_array((int)$product['idVoorraad'], $selectedProducts, true) ? 'checked' : ''; ?>>
              <?php echo htmlspecialchars($product['Product'] . ' (ID ' . $product['idVoorraad'] . ') - € ' . number_format((float)$product['Prijs'], 2, ',', '.')); ?>
            </label><br>
          <?php endwhile; ?>
        <?php else: ?>
          <p>Er zijn nog geen producten in de voorraad.</p>
        <?php endif; ?>
        </div>
      </details>
      

      <label for="datum">Datum</label><br>
      <input id="datum" name="datum" type="date" class="form-input" required>
      

      <label for="klant">Klant</label><br>
      <input id="klant" name="klant" type="text" class="form-input" required>
      

      <div class="form-actions compact-actions">
        <input type="submit" value="Toevoegen">
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
