<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
</head>
<body>
    <h2>Inscription</h2>
    <form action="register.php" method="POST">
        <input type="text" name="name" placeholder="Nom d'utilisateur" required><br>
        <input type="text" name="username" placeholder="Prenom" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertion dans la base de données
    $sql = "INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $name, $username, $email, $password);
    if ($stmt->execute()) {
        echo "Inscription réussie. <a href='login.php'>Connexion</a>";
    } else {
        echo "Erreur: " . $conn->error;
    }
}
?>
