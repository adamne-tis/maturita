<?php
session_start();

include_once "utils.php";
include_once "db.php";

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

$question_count = intval($_GET["question_count"]);
$test_type = $_GET["test_type"];

$conn = connect_db();
$sql = "SELECT id, front_text, back_text FROM cards WHERE study_set_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $study_set_id);

$stmt->execute();
$result = $stmt->get_result();

$cards = array();

while ($row = $result->fetch_assoc()) {
    $cards[] = $row;
}

shuffle($cards);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test | Flashcards</title>
    <style>
        .answer {
            border: 0;
            border-bottom: 1px solid black;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="print()">Vytisknout</button>

    <ol>
        <?php
            $i = 0;
            foreach ($cards as $card) {
                if ($i >= $question_count) {
                    break;
                }

                $question = "";
                $answer = "";
                if ($test_type == "front") {
                    $question = $card["front_text"];
                    $answer = $card["back_text"];
                } elseif ($test_type == "back") {
                    $question = $card["back_text"];
                    $answer = $card["front_text"];
                }

                echo "<li>";

                echo "<p>";
                echo htmlspecialchars($question). " - ";
                echo '<input type="text" class="answer">';
                echo "</p>";

                echo "</li>";
                $i++;
            }
        ?>
        <!-- <li>
            <p>otazka - <input type="text" class="answer"></p>
        </li> -->
    </ol>
</body>
</html>
