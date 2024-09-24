<?php
require 'db.php';
session_start();

// Redirection vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération de tous les posts et leurs commentaires
$sql = "
    SELECT posts.id AS post_id, posts.content AS post_content, posts.created_at AS post_date, posts.user_id AS post_userId,
           users.username AS post_author 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC";
$posts_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réseau Social</title>
    <link rel="stylesheet" href="../front/style.css">
</head>
<body>
    <div id="container">
        <div id="head"> 
            <div class="navBar">
                <span><h4 id="nom"><?php echo $_SESSION['username']; ?></h4></span>
                <span><img src="../img/accueil.png" alt=""   class="icon"></span>
                <span><img src="../img/actualite.png" alt="" class="icon"></span>
                <span><img src="../img/personne.png" alt="" class="avatar"></span>
            </div>
        </div>
        <div id="head1"> 
           <h2>.</h2>
        </div>

        <!-- Formulaire de publication d'un post -->
        <div id="formulaire">
            <div id="form1">
                <div class="searchBar">
                    <input type="text" id="search">
                    <img src="../img/search.png" alt="" id="searchBouton">
                </div>    
                <div class="form1Icon">
                    <div class="Iconcontainer">
                        <img src="../img/users_7263418.png" alt="" class="icon">
                        <p>Amis</p>
                    </div>
                    <div class="Iconcontainer">
                        <img src="../img/communication.png" alt="" class="icon">
                        <p>Groupes</p>
                    </div>
                    <div class="Iconcontainer">
                        <img src="../img/business-report_7087423.png" alt="" class="icon">
                        <p>Evenements</p>
                    </div>
                    <div class="Iconcontainer">
                        <img src="../img/gift_12213812.png" alt="" class="icon">
                        <p>Souvenirs</p>
                    </div>
                    <div class="Iconcontainer">
                        <img src="../img/reglages.png" alt="" class="icon">
                        <p>Parametres</p>
                    </div>
                </div>
                <a href="logout.php" id="deconnecte">Déconnexion</a>    
            </div>

            <div id="form2">
                    <form action="create_post.php" method="POST">
                        <div class="partage">    
                            <textarea name="content" placeholder="Que voulez-vous partager ?" id="partageText" required></textarea><br>
                            <button type="submit">Publier</button>
                        </div>    
                    </form>
                <hr>

                <!-- Affichage des posts -->
                <h3>Publications récentes</h3>
                <?php while ($post = $posts_result->fetch_assoc()) : ?>
                    <div class="publication">
                        <div class="post">
                            <div class="pubHead">
                                <img src="../img/personne.png" alt="" class="avatar">
                                <div class="pubHeadContainer">    
                                    <small><?php echo htmlspecialchars($post['post_author']); ?></small>
                                    <small>Publié le <?php echo $post['post_date']; ?></small>
                                </div>
                            </div>
                            <div class="pubContent">
                                <div class="pubContentContainer">
                                    <p><?php echo htmlspecialchars($post['post_content']); ?></p>
                                </div>
                            </div>

                            <!-- Afficher les réactions sur ce post -->
                            <?php
                            $post_id = $post['post_id'];
                            $sql_reactions = "
                                SELECT reaction_type, COUNT(*) AS total 
                                FROM post_reactions 
                                WHERE post_id = ? 
                                GROUP BY reaction_type";
                            $stmt_reactions = $conn->prepare($sql_reactions);
                            $stmt_reactions->bind_param('i', $post_id);
                            $stmt_reactions->execute();
                            $result_reactions = $stmt_reactions->get_result();

                            echo "<div id=\"reaction_count_$post_id\" class=\"reactionAppend\">Réactions : ";
                            while ($reaction = $result_reactions->fetch_assoc()) {
                                echo htmlspecialchars($reaction['reaction_type']) . " (" . $reaction['total'] . ") ";
                            }
                            echo "</div>";

                               ?>

                            <!-- Formulaire pour ajouter une réaction -->
                            <div class="reactionSelect">
                                <form action="react_post.php" method="POST" class="reaction-form">
                                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                    <input type="hidden" name="reaction_type" id="reaction_type_<?php echo $post_id; ?>">
                                    <div class="reactionImage">
                                        <img src="../img/like_777123.png" alt="Aime" class="reaction-icon" onclick="submitReaction('<?php echo $post_id; ?>', 'aime')">
                                        <img src="../img/thumb-down_889220.png" alt="Haie" class="reaction-icon" onclick="submitReaction('<?php echo $post_id; ?>', 'haie')">
                                        <img src="../img/heart_656688.png" alt="Joie" class="reaction-icon" onclick="submitReaction('<?php echo $post_id; ?>', 'joie')">
                                        <img src="../img/disappointed_17204683.png" alt="Triste" class="reaction-icon" onclick="submitReaction('<?php echo $post_id; ?>', 'triste')">
                                    </div>    
                                </form>
                                 <button onclick="toggleComments('<?php echo $post_id; ?>')" class="btComt"><img src="../img/commentaire.png" alt="" class="reaction-icon"></button>
                                 <?php if ($_SESSION['user_id'] == $post['post_userId']) : ?>
                                    <div class="dropdown">
                                        <button onclick="toggleDropdown('<?php echo $post['post_id']; ?>')" class="dropdown-btn">⋮</button>
                                        <div id="dropdown-<?php echo $post['post_id']; ?>" class="dropdown-content" style="display: none;">
                                            <a href="edit_post.php?post_id=<?php echo $post['post_id']; ?>">Modifier</a>
                                            <a href="delete_post.php?post_id=<?php echo $post['post_id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">Supprimer</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Afficher les commentaires pour ce post (cachés par défaut) -->
                            <div id="comments_<?php echo $post_id; ?>" class="comments" style="display: none;">
                                <?php
                                $comments_sql = "
                                    SELECT comments.id AS comment_id, comments.content AS comment_content, comments.created_at AS comment_date, 
                                           users.username AS comment_author 
                                    FROM comments 
                                    JOIN users ON comments.user_id = users.id 
                                    WHERE comments.post_id = ? 
                                    ORDER BY comments.created_at ASC";
                                $comments_stmt = $conn->prepare($comments_sql);
                                $comments_stmt->bind_param('i', $post_id);
                                $comments_stmt->execute();
                                $comments_result = $comments_stmt->get_result();
                                ?>
                                <h5>Commentaires</h5>
                                <?php while ($comment = $comments_result->fetch_assoc()) : ?>
                                    <div class="comment">
                                        <strong><?php echo htmlspecialchars($comment['comment_author']); ?> :</strong>
                                        <p><?php echo htmlspecialchars($comment['comment_content']); ?></p>
                                        <small><?php echo $comment['comment_date']; ?></small>

                                        <!-- Réactions sur le commentaire -->
                                        <?php
                                        $comment_id = $comment['comment_id'];
                                        $sql_comment_reactions = "
                                            SELECT reaction_type, COUNT(*) AS total 
                                            FROM comment_reactions 
                                            WHERE comment_id = ? 
                                            GROUP BY reaction_type";
                                        $stmt_comment_reactions = $conn->prepare($sql_comment_reactions);
                                        $stmt_comment_reactions->bind_param('i', $comment_id);
                                        $stmt_comment_reactions->execute();
                                        $result_comment_reactions = $stmt_comment_reactions->get_result();

                                        echo "<div class=\"reactionAppend\">Réactions : ";
                                        while ($comment_reaction = $result_comment_reactions->fetch_assoc()) {
                                            echo htmlspecialchars($comment_reaction['reaction_type']) . " (" . $comment_reaction['total'] . ") ";
                                        }
                                        echo "</div>";
                                        ?>

                                        <!-- Formulaire pour ajouter une réaction à un commentaire -->
                                        <form action="react_comment.php" method="POST" class="reaction-form">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                            <input type="hidden" name="reaction_type" id="reaction_type_<?php echo $comment['comment_id']; ?>">
                                            <div class="reactionImage">
                                                <img src="../img/like_777123.png" alt="Aime" class="reaction-icon" onclick="submitReaction('<?php echo $comment['comment_id']; ?>', 'aime')">
                                                <img src="../img/thumb-down_889220.png" alt="Haie" class="reaction-icon" onclick="submitReaction('<?php echo $comment['comment_id']; ?>', 'haie')">
                                                <img src="../img/heart_656688.png" alt="Joie" class="reaction-icon" onclick="submitReaction('<?php echo $comment['comment_id']; ?>', 'joie')">
                                                <img src="../img/disappointed_17204683.png" alt="Triste" class="reaction-icon" onclick="submitReaction('<?php echo $comment['comment_id']; ?>', 'triste')">
                                            </div>
                                        </form>
                                    </div>
                                <?php endwhile; ?>

                                <!-- Formulaire pour ajouter un commentaire -->
                                <form action="comment.php" method="POST">
                                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                    <textarea name="content" placeholder="Ajouter un commentaire..." required></textarea>
                                    <button type="submit">Commenter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="form3">
                <h4>link</h4>
                <div  class="Iconcontainer">
                    <img src="../img/facebook_733547.png" alt="" class="icon">
                    <p>facebook</p>
                </div>
                <div  class="Iconcontainer">
                    <img src="../img/google_2504914.png" alt="" class="icon">
                    <p>google</p>
                </div>
                <div  class="Iconcontainer">
                    <img src="../img/whatsapp_3536445.png" alt="" class="icon">
                    <p>whatsapp</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleComments(post_id) {
            const commentsDiv = document.getElementById('comments_' + post_id);
            if (commentsDiv.style.display === 'none') {
                commentsDiv.style.display = 'block';
            } else {
                commentsDiv.style.display = 'none';
            }
        }

        function submitReaction(itemId, reactionType) {
            const reactionField = document.getElementById('reaction_type_' + itemId);
            reactionField.value = reactionType;
            reactionField.form.submit();
        }

        function toggleDropdown(postId) {
            const dropdown = document.getElementById('dropdown-' + postId);
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
        
    </script>
</body>
</html>
