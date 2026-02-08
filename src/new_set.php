<?php
session_start();

include_once "utils.php";

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}
?>

<?php
if (isset($_POST["title"]) && isset($_POST["description"])) {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $user_id = $_SESSION["user_id"];

    include_once "db.php";
    $conn = connect_db();

    $sql = "INSERT INTO study_sets (user_id, title, description)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $description);

    $result = $stmt->execute();

    if ($result != 1) {
        echo "Nepodařilo se vytvořit balíček";
    } else {
        $id = $conn->insert_id;  // get id of the newly created study set
    
        // redirect to newly created study set
        js_redirect("./set.php?id=$id");
        exit();
    }
}
?>

<?php
$page_title = "Vytvořit nový balíček | Flashcards";
include_once "./layout/header.php";
?>

<h1>Vytvořit nový balíček</h1>

<form method="post">
    <p>
        <label for="title">Název:</label>
        <input type="text" name="title" id="title">
    </p>
    <p>
        <label for="description">Popis:</label>
        <textarea name="description" id="description"></textarea>
    </p>
    <button type="submit">Vytvořit</button>
</form>

<?php
include_once "./layout/footer.php";
?>
