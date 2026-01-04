<?php
$page_title = "Balíčky | Flashcards";
include_once "./layout/header.php";
?>

<h1>Balíčky</h1>

<a href="new_set.php">Vytvořit nový</a>

<ul>
    <?php
        // TODO: check difference between "include" and "include_once" and unify all of the functions
        include "db.php";
        $conn = connect_db();

        $sql = "SELECT id, title FROM study_sets WHERE user_id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION["user_id"]);

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $title = $row["title"];

            echo "<li>";
            echo '<a href="set.php?id='. $id. '">'. htmlspecialchars($title). "</a>";
            echo "</li>";
        }
    ?>

    <!-- <li><a href="set.php?id=1">balicek 1</a></li>
    <li><a href="set.php?id=2">balicek 2</a></li>
    <li><a href="set.php?id=3">balicek 3</a></li> -->
</ul>

<?php
include_once "./layout/footer.php";
?>