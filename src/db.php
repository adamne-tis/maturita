<?php
function connect_db() {
    // TODO: move the variables somewhere else and maybe use environment variables
    $server = "db";
    $user = "root";
    $password = "example123";
    $database = "maturita";

    // $conn = mysqli_connect($server, $user, $password, $database);
    $conn = new mysqli($server, $user, $password, $database);

    if (!$conn) {
        // die("Cannot connect to the database: ". mysqli_connect_error());
        return mysqli_connect_error();
    }

    return $conn;
}

function check_study_set_ownership($user_id, $study_set_id) {
    $conn = connect_db();

    $sql = "SELECT id FROM study_sets WHERE id=? AND user_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $study_set_id, $user_id);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return false;
    } else {
        return true;
    }
}

function check_card_ownership($user_id, $card_id) {
    $conn = connect_db();

    // $sql = "SELECT s.user_id 
    //         FROM cards c LEFT JOIN study_sets s
    //         ON s.id = c.study_set_id
	//         WHERE c.id=?";

    $sql = "SELECT s.id
            FROM cards c LEFT JOIN study_sets s
            ON s.id = c.study_set_id
	        WHERE c.id=? AND s.user_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $card_id, $user_id);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return false;
    } else {
        return true;
    }
}

function get_study_set($study_set_id) {
    $conn = connect_db();

    $sql = "SELECT id, title, description FROM study_sets WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $study_set_id);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row;
}

function get_user($user_id) {
    $conn = connect_db();

    $sql = "SELECT id, username FROM users WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row;
}

?>