<?php
function is_logged_in() {
    if (isset($_SESSION["user_id"])) {
        return true;
    } else {
        return false;
    }
}

function send_json_message($msg) {
    $response_data = array(
        "message" => $msg
    );

    header("Content-Type: application/json");
    echo json_encode($response_data);
}
?>