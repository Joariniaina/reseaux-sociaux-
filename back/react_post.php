<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $post_id = $_POST['post_id'];
    $reaction_type = $_POST['reaction_type'];  // Assure-toi que cette valeur est correcte
    $user_id = $_SESSION['user_id'];

    // Debugging: vérifier la valeur envoyée
    var_dump($reaction_type);  // à retirer en production

    // Vérification si l'utilisateur a déjà réagi à ce post
    $sql_check = "SELECT * FROM post_reactions WHERE post_id = ? AND user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ii', $post_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        // Si l'utilisateur n'a pas encore réagi, insérer la réaction
        $sql = "INSERT INTO post_reactions (reaction_type, post_id, user_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Erreur de préparation : ' . $conn->error);
        }
        $stmt->bind_param('sii', $reaction_type, $post_id, $user_id);
        if (!$stmt->execute()) {
            die('Erreur d\'exécution : ' . $stmt->error);
        }
    } else {
        // Si l'utilisateur a déjà réagi, mettre à jour la réaction
        $sql = "UPDATE post_reactions SET reaction_type = ? WHERE post_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Erreur de préparation : ' . $conn->error);
        }
        $stmt->bind_param('sii', $reaction_type, $post_id, $user_id);
        if (!$stmt->execute()) {
            die('Erreur d\'exécution : ' . $stmt->error);
        }
    }

    // Redirection après insertion ou mise à jour
    header('Location: index.php');
}
?>
