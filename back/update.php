<?php
    // Connexion à la base de données
    require 'db.php';

    // Vérifier si l'ID est défini
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $content = $_POST['content'];

        // Mettre à jour les données de l'utilisateur
        $update_sql = "UPDATE posts SET content = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi",$content, $id);
        
        if ($update_stmt->execute()) {
            // Rediriger vers index.php après la mise à jour
            header("Location: index.php");
            exit();
        } else {
            echo "Erreur : " . $conn->error;
        }
        
        $update_stmt->close();
    }

    // Fermer la connexion
    $conn->close();
?>
