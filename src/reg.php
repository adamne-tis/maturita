<?php
session_start();
include_once "utils.php";

if (is_logged_in()) {
    header("Location: sets.php");
    exit();
}
?>

<?php
$page_title = "Zaregistrovat se | Flashcards";
include_once "./layout/header.php";
?>

<?php
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    include "./db.php";
    $conn = connect_db();

    $sql = "INSERT INTO users (username, password)
            VALUES (?, md5(?))";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    $result = $stmt->execute();

    if ($result != 1) {
        echo "Nepodařilo se vytvořit účet";
        exit();
    }

    $id = $conn->insert_id;  // get id of the newly created user

    $_SESSION["user_id"] = $id;

    // redirect to sets.php
    echo '<script>window.location.href="./sets.php";</script>';
    exit();
}
?>

<h1>Zaregistrovat se</h1>

<form method="post" id="reg-form">
    <p>
        <label for="username">Uživatelské jméno:</label>
        <input type="text" name="username" id="username" required>
    </p>
    <p>
        <label for="password">Heslo:</label>
        <input type="password" name="password" id="password" required>
    </p>
    <p>
        <label for="password">Heslo znovu:</label>
        <input type="password" id="password-confirmation" required>
    </p>
    <p>
        <button type="submit">Zaregistrovat se</button>
    </p>
</form>

<script>
    const regForm = document.querySelector("form#reg-form");
    const passwordInput = document.querySelector("input#password");
    const passwordConfirmInput = document.querySelector("input#password-confirmation");

    regForm.addEventListener("input", (event) => {
        if (passwordInput.value != passwordConfirmInput.value) {
            passwordConfirmInput.setCustomValidity("Hesla se neshodují");
            event.preventDefault();
        } else {
            passwordConfirmInput.setCustomValidity("");
        }
    });
</script>

<?php
include_once "./layout/footer.php";
?>