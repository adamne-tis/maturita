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
?>