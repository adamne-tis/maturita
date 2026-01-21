<?php
include_once "utils.php";

session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$study_set_id = $_GET["id"];

include_once "db.php";

if (!check_study_set_ownership($user_id, $study_set_id)) {
    header("Location: sets.php");
    exit();
}

if (isset($_POST["action"])) {
    if ($_POST["action"] == "delete") {
        $conn = connect_db();

        // delete cards associated with this study set
        $sql1 = "DELETE FROM cards WHERE study_set_id=?";

        $stmt = $conn->prepare($sql1);
        $stmt->bind_param("i", $study_set_id);
        $result = $stmt->execute();

        // delete study set
        $sql2 = "DELETE FROM study_sets WHERE id=?";

        $stmt = $conn->prepare($sql2);
        $stmt->bind_param("i", $study_set_id);
        $result = $stmt->execute();

        if ($result == 1) {
            header("Location: sets.php");
            exit();
        }
    }
}

$study_set = get_study_set($study_set_id);
$title = $study_set["title"];
$description = $study_set["description"];

?>

<?php
include_once "./layout/header.php";
?>

<h1><?php echo htmlspecialchars($title); ?></h1>
<p><?php echo htmlspecialchars($description); ?></p>

<a href="./practice.php?id=<?php echo $study_set_id; ?>">Procvičovat</a>

<a href="./import.php?id=<?php echo $study_set_id; ?>">Importovat</a>
<a href="./export.php?id=<?php echo $study_set_id; ?>">Exportovat</a>
<a href="./gen_test.php?id=<?php echo $study_set_id; ?>">Vygenerovat test</a>

<form method="post" onsubmit="return confirm('Opravdu chcete odstranit tento balíček?');">
    <input type="hidden" name="action" value="delete">
    <input type="submit" value="Odstranit balíček">
</form>

<table id="cards-table">
    <thead>
        <tr>
            <th scope="col">
                <input type="checkbox" name="" id="">
            </th>
            <th scope="col">#</th>
            <th scope="col">Přední text</th>
            <th scope="col">Zadní text</th>
            <th scope="col">Upravit</th>
        </tr>
    </thead>
    <tbody>
        <!-- data gets fetched from API -->
    </tbody>
</table>

<form id="add-form">
    <p>
        <label for="add-front-text">Přední text:</label>
        <input type="text" name="front-text" id="add-front-text">
    </p>
    <p>
        <label for="add-back-text">Zadní text:</label>
        <input type="text" name="back-text" id="add-back-text">
    </p>

    <button type="submit">Přidat</button>
</form>


<dialog>
    <form method="post" id="edit-form">
        <input type="hidden" name="num" value="0" id="num" disabled>
        <input type="hidden" name="id" value="0" id="id">
        <p>
            <label for="front-text">Přední text:</label>
            <input type="text" name="front-text" id="front-text">
        </p>
        <p>
            <label for="back-text">Zadní text:</label>
            <input type="text" name="back-text" id="back-text">
        </p>

        <input type="submit" value="Uložit">

        <button type="button" onclick="deleteEntry()">Odstranit</button>
        <button type="button" onclick="dialog.close()">Zavřít</button>
    </form>
</dialog>

<script>
    let cards = [];


    const table = document.getElementById("cards-table");
    
    // TODO: use better selectors
    const dialog = document.querySelector("dialog");
    const dialogForm = document.getElementById("edit-form");
    const dialogNum = document.getElementById("num");
    const dialogId = document.getElementById("id");
    const dialogFrontText = document.getElementById("front-text");
    const dialogBackText = document.getElementById("back-text");

    const addForm = document.querySelector("form#add-form");
    const frontTextInput = addForm.querySelector('input[name="front-text"]');
    const backTextInput = addForm.querySelector('input[name="back-text"]');

    async function addEntry() {
        let frontText = frontTextInput.value;
        let backText = backTextInput.value;
        
        if (!frontText || !backText) {
            return;
        }

        jsonData = {
            "study_set_id": <?php echo $study_set_id; ?>,
            "front_text": frontText,
            "back_text": backText,
        };

        try {
            const response = await fetch("./api/cards.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(jsonData)
            });

            let response_json = await response.json();
            if (response_json.message != "OK") {
                throw new Error("Error adding entry");
            }

            let card = {
                "id": response_json["id"],
                "front_text": frontText,
                "back_text": backText
            };
            cards.push(card);


            // update HTML table
            createRow(cards.length - 1, card);

            // bring focus on front text input
            frontTextInput.focus();
        } catch (e) {
            console.error(e);
        }

        frontTextInput.value = "";
        backTextInput.value = "";
    }

    async function deleteEntry() {
        let cardId = parseInt(dialogId.value);
        let cardNum = parseInt(dialogNum.value);

        jsonData = {
            "id": cardId
        };

        try {
            const response = await fetch("./api/cards.php", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(jsonData)
            });

            let response_json = await response.json();
            if (response_json.message != "OK") {
                throw new Error("Error deleting entry");
            }

            cards.splice(cardNum, 1);  // remove 1 element at "cardNum" position


            // update HTML table
            let row = table.rows[cardNum + 1];  // +1 to skip the table head
            console.log("row:", row);
            
            table.deleteRow(cardNum + 1);  // +1 to skip the table head

            reorderRows();

            dialog.close();
        } catch (e) {
            console.error(e);
        }
    }

    async function updateEntry() {
        let cardId = parseInt(dialogId.value);
        let cardNum = parseInt(dialogNum.value);
        let card = cards[cardNum];

        let new_card = card;
        new_card.front_text = dialogFrontText.value;
        new_card.back_text = dialogBackText.value;

        try {
            const response = await fetch("./api/cards.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(new_card)
            });

            let response_json = await response.json();
            if (response_json.message != "OK") {
                throw new Error("Error updating entry");
            }

            cards[cardNum] = new_card;  // update card after successful request


            // update HTML table
            let row = table.rows[cardNum + 1];  // +1 to skip the table head
            row.cells[2].innerText = new_card.front_text;
            row.cells[3].innerText = new_card.back_text;
            console.log("updating row:", row);

            dialog.close();
        } catch (e) {
            console.error(e);
        }
    }

    function showEditDialog(row) {
        console.log("editing row:", row);
        let cardNum = parseInt(row.cells[1].innerText) - 1;

        let card = cards[cardNum];

        dialogNum.value = cardNum;
        dialogId.value = card.id;
        dialogFrontText.value = card.front_text;
        dialogBackText.value = card.back_text;

        dialog.showModal();
    }

    async function loadRows() {
        try {
            const response = await fetch("./api/cards.php?study_set_id=<?php echo $study_set_id; ?>");
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            const result = await response.json();
            // console.log(result);
            cards = result;
            renderRows();
        } catch (error) {
            console.error(error.message);
        }
    }

    function reorderRows() {
        let i = 0;
        for (let row of table.rows) {
            if (i == 0) {  // skip the first row (table head)
                i++;
                continue;
            }

            row.cells[1].innerText = i;

            i++;
        }
    }

    function createRow(i, card) {
        let row = table.insertRow(-1);

        let checkboxCell = row.insertCell(0);
        let numCell = row.insertCell(1);
        let frontTextCell = row.insertCell(2);
        let backTextCell = row.insertCell(3);
        let editCell = row.insertCell(4);

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = ""; // TODO
        checkbox.id = ""; // TODO

        const editButton = document.createElement("button");
        editButton.innerText = "Upravit";
        // const boundHandler = showEditDialog.bind(null, i);
        const boundHandler = showEditDialog.bind(null, row);
        editButton.onclick = boundHandler;

        checkboxCell.appendChild(checkbox);
        numCell.innerText = i + 1;
        frontTextCell.innerText = card.front_text;
        backTextCell.innerText = card.back_text;
        editCell.appendChild(editButton);
    }

    function renderRows() {
        let i = 0;
        cards.forEach(card => {
            createRow(i, card);

            i++;
        });
    }

    addForm.addEventListener("submit", (event) => {
        event.preventDefault();
        addEntry();
    });

    dialogForm.addEventListener("submit", (event) => {
        event.preventDefault();
        updateEntry();
    });

    loadRows();
</script>

<?php
include_once "./layout/footer.php";
?>