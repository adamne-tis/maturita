<?php
session_start();

include_once "utils.php";

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

include "./db.php";
$conn = connect_db();

$user_id = $_SESSION["user_id"];

$user = get_user($user_id);
$username = $user["username"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["action"] == "change_password") {
        if (isset($_POST["current_password"]) && isset($_POST["new_password"])) {
            $current_password = $_POST["current_password"];
            $new_password = $_POST["new_password"];

            $sql = "UPDATE users SET password=md5(?) WHERE id=? AND password=md5(?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $new_password, $user_id, $current_password);

            $result = $stmt->execute();

            if ($conn->affected_rows) {
                echo "Heslo bylo úspěšně změněno";
            } else {
                echo "Nepodařilo se změnit heslo";
            }
        }
    }

    if ($_POST["action"] == "delete_account") {
        // delete all user's cards and study sets and finally the user

        // delete user's cards
        $sql1 = "DELETE c
                FROM cards c INNER JOIN study_sets s
                ON s.id = c.study_set_id
                WHERE s.user_id=?";

        $stmt = $conn->prepare($sql1);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();


        // delete user's study sets
        $sql2 = "DELETE FROM study_sets WHERE user_id=?";

        $stmt = $conn->prepare($sql2);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();


        // delete user
        $sql3 = "DELETE FROM users WHERE id=?";

        $stmt = $conn->prepare($sql3);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();

        if ($result == 1) {
            header("Location: logout.php");
            exit();
        }
    }
}
?>

<?php
$page_title = "Profil";
include_once "./layout/header.php";
?>

<h1>Profil</h1>

<p>Uživatelské jméno: <?php echo htmlspecialchars($username); ?></p>

<form method="post">
    <fieldset>
        <legend>Změna hesla</legend>

        <input type="hidden" name="action" value="change_password">

        <p>
            <label for="current-password">Aktuální heslo:</label>
            <input type="password" name="current_password" id="current-password">
        </p>
        <p>
            <label for="new-password">Nové heslo:</label>
            <input type="password" name="new_password" id="new-password">
        </p>
    
        <input type="submit" value="Změnit heslo">
    </fieldset>
</form>

<form method="post" id="delete-account-form">
    <fieldset>
        <legend>Odstranění účtu</legend>

        <input type="hidden" name="action" value="delete_account">
        <input type="submit" value="Odstranit účet">
    </fieldset>
</form>

<script>
    const deleteAccountForm = document.querySelector("form#delete-account-form");

    deleteAccountForm.addEventListener("submit", (event) => {
        if (!confirm("Opravdu chcete odstranit váš účet?")) {
            event.preventDefault();
        }
    });
</script>

<?php
include_once "./layout/footer.php";
?>
