<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Connexion à la base de données
    require '../back/db.php';

    // Vérification du token
    $sql = "SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        ?>

        <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <label for="password">Nouveau mot de passe:</label>
            <input type="password" name="password" id="password" placeholder="Entrez votre nouveau mot de passe" required><br><br>
            
            <input type="submit" value="Réinitialiser le mot de passe">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['password'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $sql = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Erreur de préparation de la requête : " . $conn->error);
            }

            $stmt->bind_param('ss', $hashed_password, $token);

            if ($stmt->execute()) {
                echo "Votre mot de passe a été réinitialisé avec succès.";
            } else {
                echo "Erreur lors de la réinitialisation du mot de passe : " . $stmt->error;
            }
        }
    } else {
        echo "Lien invalide ou expiré.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Aucun token fourni.";
}
?>
