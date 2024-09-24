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

// Supprimer la publication si elle appartient à l'utilisateur connecté
$sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $post_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    header('Location: index.php');
    exit();
} else {
    echo "Erreur lors de la suppression du post.";
}
?>
