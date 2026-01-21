<?php
session_start();
include_once "utils.php";

if (is_logged_in()) {
    header("Location: sets.php");
    exit();
}
?>

<?php
$page_title = "Přihlásit se | Flashcards";
include_once "./layout/header.php";
?>

<?php
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    include "./db.php";
    $conn = connect_db();

    $sql = "SELECT id FROM users WHERE username=? AND password=md5(?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo "Neplatné uživatelské jméno nebo heslo";
    } else {
        echo "Úspěšné přihlášení";
        $_SESSION["user_id"] = $row["id"];

        // redirect to sets.php
        echo '<script>window.location.href="./sets.php";</script>';
        exit();
    }
}
?>

<h1>Přihlášení</h1>

<form method="post">
    <p>
        <label for="username">Uživatelské jméno:</label>
        <input type="text" name="username" id="username" required>
    </p>
    <p>
        <label for="password">Heslo:</label>
        <input type="password" name="password" id="password" required>
    </p>
    <p>
        <button type="submit">Přihlásit se</button>
    </p>
</form>

<?php
include_once "./layout/footer.php";
?>