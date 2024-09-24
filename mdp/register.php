<!-- register.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="./front/style.css">
</head>
<body>
    <div id="verify">
        <h2>Inscription</h2>
        <form action="register.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Entrez votre e-mail" required><br><br>

            <label for="password">Mot de passe:</label><br><br>
                <div class = 'pass'>
                    <input type="password" name="password" id="password" class='passwd' placeholder="Entrez votre mot de passe" required>
                    <img src="../img/option-dinterface-a-oeil-ouvert-visible.png" class="icone" >
                </div><br>

            <input type="submit" value="S'inscrire">
        </form>
        </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupérer les données du formulaire
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Hachage du mot de passe pour plus de sécurité
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Connexion à la base de données
        require '../back/db.php';

        // Vérifier si l'utilisateur existe déjà
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Un utilisateur avec cet e-mail existe déjà.";
        } else {
            // Insertion des données dans la base de données
            $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: ./etudiant/index.php");
            } else {
                echo "Erreur lors de l'inscription : " . $stmt->error;
            }
        }

        // Fermeture des connexions
        $stmt->close();
        $conn->close();
    }
    ?>
    <script src="./front/script.js"></script>
</body>
</html>
