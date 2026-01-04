<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once "utils.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if (isset($page_title)) {
            echo $page_title;
        } else {
            echo "Flashcards";
        }
        ?>
    </title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <header>
       <nav class="navbar">
            <ul>
                <li><a href="/">Domů</a></li>
                <?php
                if (is_logged_in()) {
                    echo '<li><a href="/sets.php">Balíčky</a></li>';
                }
                ?>
                <div class="navbar-separator"></div>
                
                <?php
                if (is_logged_in()) {
                    echo '<li><a href="/profile.php">Profil</a></li>';
                    echo '<li><a href="/logout.php">Odhlásit se</a></li>';
                } else {
                    echo '<li><a href="/reg.php">Zaregistrovat se</a></li>';
                    echo '<li><a href="/login.php">Přihlásit se</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>
    

    <main>
    