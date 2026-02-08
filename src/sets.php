<?php
session_start();

include_once "utils.php";

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}
?>

<?php
$page_title = "Balíčky | Flashcards";
include_once "./layout/header.php";
?>

<h1>Balíčky</h1>

<a href="./new_set.php" class="btn">Vytvořit nový</a>

<table>
    <thead>
        <tr>
            <th scope="col">Název</th>
            <th scope="col">Počet karet</th>
        </tr>
    </thead>
    <tbody>
        <?php
            include_once "db.php";
            $conn = connect_db();

            $sql = "SELECT s.id, s.title, COUNT(*) AS card_count
                    FROM study_sets s LEFT JOIN cards c
                    ON c.study_set_id = s.id
	                WHERE s.user_id=? GROUP BY s.id";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION["user_id"]);

            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $id = $row["id"];
                $title = $row["title"];
                $card_count = $row["card_count"];

                echo "<tr>";
                echo '<td><a href="./set.php?id='. $id. '">'. htmlspecialchars($title). "</a></td>";
                echo "<td>$card_count</td>";
                echo "</tr>";
            }
        ?>
    </tbody>
</table>

<?php
include_once "./layout/footer.php";
?>
