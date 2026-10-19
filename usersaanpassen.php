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
    $id = isset($_POST['ID']) ? intval($_POST['ID']) : $id;
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $naam = mysqli_real_escape_string($conn, $_POST['naam'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $wachtwoord = isset($_POST['wachtwoord']) ? password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT) : null;

    $stmt = $conn->prepare("UPDATE Users SET Username = ?, Naam = ?, Email = ?, WW = ? WHERE ID = ?");
    $stmt->bind_param("ssssi", $username, $naam, $email, $wachtwoord, $id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: usersoverzicht.php");
        exit;
    } else {
        $error = "Update mislukt: " . $conn->error;
        $stmt->close();
        $row = [
            'Username' => $username,
            'Naam' => $naam,
            'Email' => $email,
        ];
    }
}

if (!$row && $id > 0) {
    $stmt = $conn->prepare("SELECT ID, Username, Naam, Email, WW FROM Users WHERE ID = ?");
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
  <link rel="stylesheet" href="phpcss.css">
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
    
    
    <h2>Gebruiker aanpassen</h2>
    
    
    
    <form action="usersaanpassen.php?ID=<?php echo htmlspecialchars($id); ?>" method="post" class="compact-form">
      <input type="hidden" name="ID" value="<?php echo htmlspecialchars($id); ?>">
      <label for="username">Username</label><br>
      <input id="username" name="username" value="<?php echo $row["Username"]; ?>" type="text">

      <label for="naam">Naam</label><br>
      <input id="naam" name="naam" value="<?php echo $row["Naam"]; ?>" type="text">
      

      <label for="email">E-mail</label><br>
      <input id="email" name="email" value="<?php echo $row["Email"]; ?>" type="email">
      
      <label for="wachtwoord">Wachtwoord</label><br>
      <input id="wachtwoord" name="wachtwoord" type="password">
    
      <div class="form-actions compact-actions">
        <input type="submit" value="Opslaan">
        <a href="usersoverzicht.php"><button type="button">Annuleren</button></a>
      </div>
    </form>
  </div>
        

            
</body>
</html>