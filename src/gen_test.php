<?php
include_once "utils.php";
include_once "db.php";

session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: sets.php");
    exit();
}

$study_set_id = $_GET["id"];

if (!check_study_set_ownership($user_id, $study_set_id)) {
    header("Location: sets.php");
    exit();
}


// get card count
$conn = connect_db();

$sql = "SELECT COUNT(*) FROM cards WHERE study_set_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $study_set_id);

$stmt->execute();
$result = $stmt->get_result();

$card_count = $result->fetch_array()[0];
?>

<?php
$page_title = "Vygenerovat test | Flashcards";
include_once "./layout/header.php";
?>

<h1>Vygenerovat test</h1>

<form action="test.php" method="get">
    <input type="hidden" name="id" value="<?php echo $study_set_id; ?>">

    <p>
        <label for="question-count">Počet otázek:</label>
        <input type="number" name="question_count" id="question-count" min="1" max="<?php echo $card_count; ?>" value="<?php echo min(10, $card_count); ?>">
    </p>

    <p>
        <label for="test-type">Typ testu:</label>
        <select name="test_type" id="test-type">
            <option value="front">Přední text - Zadní text</option>
            <option value="back">Zadní text - Přední text</option>
            <!-- <option value="mix">Kombinovaný</option> -->
        </select>
    </p>

    <input type="submit" value="Vygenerovat test">
</form>

<?php
include_once "./layout/footer.php";
?>