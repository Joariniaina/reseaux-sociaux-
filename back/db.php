<?php
$host = 'localhost';
$user = 'userMysql';
$password = 'AZertyuiop123@@@!';
$dbname = 'reseaux_sociaux';

// Connexion à MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}
?>
