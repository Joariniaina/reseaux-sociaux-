<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $comment_id = $_POST['comment_id'];
    $reaction_type = $_POST['reaction_type'];
    $user_id = $_SESSION['user_id'];

    // Vérification si l'utilisateur a déjà réagi à ce commentaire
    $sql_check = "SELECT * FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ii', $comment_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        // Si l'utilisateur n'a pas encore réagi, insérer la réaction
        $sql = "INSERT INTO comment_reactions (reaction_type, comment_id, user_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $reaction_type, $comment_id, $user_id);
        $stmt->execute();
    } else {
        // Si l'utilisateur a déjà réagi, mettre à jour la réaction
        $sql = "UPDATE comment_reactions SET reaction_type = ? WHERE comment_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $reaction_type, $comment_id, $user_id);
        $stmt->execute();
    }

    header('Location: index.php');
}
?>
