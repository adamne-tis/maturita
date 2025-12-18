<?php
include_once "./layout/header.php";
?>

<?php
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    include "./db.php";
    $conn = connect_db();

    $sql = "SELECT id FROM users WHERE username=? AND password=md5(?)";
    // $stmt = mysqli_prepare($conn, $sql);
    $stmt = $conn->prepare($sql);
    // mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    $stmt->bind_param("ss", $username, $password);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo "Invalid login";
    } else {
        echo "Login successful";
    }

    // if (mysqli_stmt_execute($stmt)) {
    //     echo "yes";
    // } else {
    //     echo "no";
    // }
}
?>

<h1>Přihlášení</h1>

<form method="post">
    <p>
        <label for="username">Uživatelské jméno:</label>
        <input type="text" name="username" id="username">
    </p>
    <p>
        <label for="password">Heslo:</label>
        <input type="password" name="password" id="password">
    </p>
    <p>
        <button type="submit">Přihlásit se</button>
    </p>
</form>

<?php
include_once "./layout/footer.php";
?>