<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
</head>
<body>
    <h2>Mot de passe oublié</h2>
    <form action="forgot_password.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Entrez votre e-mail" required><br><br>
        <input type="submit" value="Envoyer le lien de réinitialisation">
    </form>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        require '../back/db.php';
        // Vérification de l'utilisateur
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            date_default_timezone_set('Africa/Nairobi');//definir le fuseau horaire
            $token = bin2hex(random_bytes(50)); // Générer un token unique
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Le token expire après 1 heure
            echo $expiry;

            // Stocker le token et la date d'expiration
            $sql = "UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $token, $expiry, $email);

            if ($stmt->execute()) {

                // Récupération de l'hôte
                $host = $_SERVER['HTTP_HOST']; 
                            
                // Extraction du port (si présent)
                $port = $_SERVER['SERVER_PORT'];
                            
                // Envoyer l'email de réinitialisation
                $reset_link = "http://localhost:$port/mdp/reset_password.php?token=" . $token;
                $subject = "Réinitialisation de votre mot de passe";
                $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $reset_link;
                $headers = "From: no-reply@yourwebsite.com";

                if (mail($email, $subject, $message, $headers)) {
                    echo "Un lien de réinitialisation a été envoyé à votre adresse e-mail.";
                } else {
                    echo "Erreur lors de l'envoi de l'e-mail.";
                }
            } else {
                echo "Erreur lors de la création du token : " . $stmt->error;
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet e-mail.";
        }

        $stmt->close();
        $conn->close();
    }
    ?>
</body>
</html>
