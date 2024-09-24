<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID du post depuis l'URL
$post_id = $_GET['post_id'];

// Récupérer les informations du post à modifier
$sql = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $post_id, $_SESSION['user_id']);
$stmt->execute();
$post_result = $stmt->get_result();
$post = $post_result->fetch_assoc();

// Si le post n'existe pas ou n'appartient pas à l'utilisateur, rediriger
if (!$post) {
    header('Location: index.php');
    exit();
}

// Mise à jour du post après soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    
    $update_sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sii', $content, $post_id, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        header('Location: index.php');
        exit();
    } else {
        echo "Erreur lors de la mise à jour du post.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la publication</title>
</head>
<body>
    <h3>Modifier la publication</h3>
    <form action="edit_post.php?post_id=<?php echo $post_id; ?>" method="POST">
        <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea><br>
        <button type="submit">Mettre à jour</button>
    </form>
    <a href="index.php">Annuler</a>
</body>
</html>
