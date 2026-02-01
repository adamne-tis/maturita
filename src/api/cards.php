<?php
include_once "../utils.php";

session_start();

if (!isset($_SESSION["user_id"])) {
    send_json_message("Please log in");
    exit();
}

$user_id = $_SESSION["user_id"];

include_once "../db.php";
$conn = connect_db();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET["study_set_id"])) {
        send_json_message("Missing parameter study_set_id");
        exit();
    }

    $study_set_id = $_GET["study_set_id"];

    // validate that user owns this study set
    if (!check_study_set_ownership($user_id, $study_set_id)) {
        send_json_message("You do not own this study set");
        exit();
    }


    $sql = "SELECT id, front_text, back_text FROM cards WHERE study_set_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $study_set_id);

    $stmt->execute();
    $result = $stmt->get_result();

    $results_array = array();

    while ($row = $result->fetch_assoc()) {
        $results_array[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($results_array);

}
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $study_set_id = $data["study_set_id"];
    $front_text = $data["front_text"];
    $back_text = $data["back_text"];

    // validate that user owns this study set
    if (!check_study_set_ownership($user_id, $study_set_id)) {
        send_json_message("You do not own this study set");
        exit();
    }


    $sql = "INSERT INTO cards (study_set_id, front_text, back_text)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $study_set_id, $front_text, $back_text);

    $result = $stmt->execute();

    if ($result != 1) {
        send_json_message("Error adding entry");
        exit();
    }

    $id = $conn->insert_id;  // get id of the newly created card

    $response_data = array(
        "message" => "OK",
        "id" => $id
    );

    header("Content-Type: application/json");
    echo json_encode($response_data);
}
elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $card_id = $data["id"];
    $front_text = $data["front_text"];
    $back_text = $data["back_text"];

    // validate that the user owns this card
    if (!check_card_ownership($user_id, $card_id)) {
        send_json_message("You do not own this card");
        exit();
    }


    $sql = "UPDATE cards SET front_text=?, back_text=? WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $front_text, $back_text, $card_id);

    $result = $stmt->execute();

    if ($result != 1) {
        send_json_message("Error updating entry");
        exit();
    }

    send_json_message("OK");
}
elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data["id"])) {  // delete single card
        $card_id = $data["id"];
    
        // validate that the user owns this card
        if (!check_card_ownership($user_id, $card_id)) {
            send_json_message("You do not own this card");
            exit();
        }
    
    
        $sql = "DELETE FROM cards WHERE id=?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $card_id);
    
        $result = $stmt->execute();
    
        if ($result != 1) {
            send_json_message("Error deleting entry");
            exit();
        }
    
        send_json_message("OK");
    }
    else if (isset($data["ids"])) {  // delete multiple cards
        $card_ids = $data["ids"];

        $parameters = str_repeat('?,', count($card_ids) - 1) . '?';
        $sql = "DELETE c
                FROM cards c INNER JOIN study_sets s
                ON s.id = c.study_set_id
	            WHERE s.user_id=? AND c.id IN ($parameters)";

        $result = $conn->execute_query($sql, array_merge([$user_id], $card_ids));
    
        if ($result != 1) {
            send_json_message("Error deleting entries");
            exit();
        }
    
        send_json_message("OK");
    }
}
?>