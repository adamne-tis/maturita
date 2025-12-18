<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- TODO: change title -->
    <title>Document</title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <header>
       <nav class="navbar">
            <ul>
                <li><a href="/">Domů</a></li>
                <li><a href="/sets.php">Balíčky</a></li>
                <?php
                // if isset($_SESSION[""]) {
                    
                // }
                ?>

                <li><a href="/login.php">Přihlásit se</a></li>
                <li><a href="/profile.php">Profil</a></li>
                <li><a href="/logout.php">Odhlásit se</a></li>
            </ul>
        </nav>
    </header>
    

    <main>
    