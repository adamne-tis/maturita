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
    $conn = connect_db();

    if ($_POST["action"] == "delete") {
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
    } elseif ($_POST["action"] == "edit_info") {
        $title = $_POST["title"];
        $description = $_POST["description"];

        $sql = "UPDATE study_sets SET title=?, description=? WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $study_set_id);

        $result = $stmt->execute();
    }
}

$study_set = get_study_set($study_set_id);
$title = htmlspecialchars($study_set["title"]);
$description = htmlspecialchars($study_set["description"]);
?>

<?php
include_once "./layout/header.php";
?>

<h1 id="title"><?php echo $title; ?></h1>
<p id="description"><?php echo nl2br($description); ?></p>

<div class="actions-container">
    <a href="./practice.php?id=<?php echo $study_set_id; ?>" class="btn">Procvičovat</a>
    <a href="./gen_test.php?id=<?php echo $study_set_id; ?>" class="btn">Vygenerovat test</a>
    <button onclick="infoDialog.showModal()">Upravit</button>
    
    <form method="post" onsubmit="return confirm('Opravdu chcete odstranit tento balíček?');">
        <input type="hidden" name="action" value="delete">
        <input type="submit" value="Odstranit balíček" class="btn-red">
    </form>
</div>

<div class="actions-container" id="selection-actions" style="display: none">
    <button class="btn-red" onclick="deleteSelected()">Odstranit vybrané</button>
</div>

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

<dialog id="info-dialog">
    <form method="post">
        <input type="hidden" name="action" value="edit_info">
        <p>
            <label for="info-title">Název:</label>
            <input type="text" name="title" id="info-title" value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="info-description">Popis:</label>
            <textarea name="description" id="info-description"><?php echo $description; ?></textarea>
        </p>

        <input type="submit" value="Uložit">
        <button type="button" onclick="infoDialog.close()">Zavřít</button>
    </form>
</dialog>

<dialog id="card-dialog">
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
        <button type="button" onclick="cardDialog.close()">Zavřít</button>
    </form>
</dialog>

<script>
    let cards = [];

    let selectedRows = [];

    const table = document.querySelector("table#cards-table");
    
    const selectionActions = document.querySelector("#selection-actions");

    const cardDialog = document.querySelector("dialog#card-dialog");
    const cardDialogForm = cardDialog.querySelector("form");
    const cardDialogNum = cardDialogForm.querySelector("#num");
    const cardDialogId = cardDialogForm.querySelector("#id");
    const cardDialogFrontText = cardDialogForm.querySelector("#front-text");;
    const cardDialogBackText = cardDialogForm.querySelector("#back-text");

    const infoDialog = document.querySelector("dialog#info-dialog");
    const infoDialogTitle = infoDialog.querySelector('input[name="title"]')
    const infoDialogDesc = infoDialog.querySelector('textarea[name="description"]')

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
        let cardId = parseInt(cardDialogId.value);
        let cardNum = parseInt(cardDialogNum.value);

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

            cardDialog.close();
        } catch (e) {
            console.error(e);
        }
    }

    async function updateEntry() {
        let cardId = parseInt(cardDialogId.value);
        let cardNum = parseInt(cardDialogNum.value);
        let card = cards[cardNum];

        let new_card = card;
        new_card.front_text = cardDialogFrontText.value;
        new_card.back_text = cardDialogBackText.value;

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

            cardDialog.close();
        } catch (e) {
            console.error(e);
        }
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
        const selectionBoundHandler = updateSelection.bind(null, row);
        checkbox.onclick = selectionBoundHandler;

        const editButton = document.createElement("button");
        editButton.innerText = "Upravit";
        // const boundHandler = showCardDialog.bind(null, i);
        const dialogBoundHandler = showCardDialog.bind(null, row);
        editButton.onclick = dialogBoundHandler;

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

    function updateSelection(row) {
        checkbox = row.querySelector('input[type="checkbox"]');
        
        if (checkbox.checked) {
            console.log("row checked")
            selectedRows.push(row);
        } else {
            console.log("row unchecked")
            rowIndex = selectedRows.indexOf(row);
            selectedRows.splice(rowIndex, 1)  // remove the row from selection array based on its index
        }

        if (selectedRows.length > 0) {  // toggle the visibility of selection menu
            selectionActions.style = "";
        } else {
            selectionActions.style = "display: none";
        }
        // console.log(row);
    }

    async function deleteSelected() {
        // TODO: send request to remove rows from database
        jsonData = {
            "ids": selectedRows.map((row) => cards[row.rowIndex - 1].id)
        };

        console.log(jsonData);

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
        } catch (e) {
            console.error(e);
        }

        selectedRows.forEach(row => {
            cards.splice(row.rowIndex - 1, 1);

            // update HTML table
            row.remove()
            reorderRows();
        });

        selectedRows = [];  // clear selection
        selectionActions.style = "display: none";  // hide menu
    }

    function showCardDialog(row) {
        console.log("editing row:", row);
        let cardNum = parseInt(row.cells[1].innerText) - 1;

        let card = cards[cardNum];

        cardDialogNum.value = cardNum;
        cardDialogId.value = card.id;
        cardDialogFrontText.value = card.front_text;
        cardDialogBackText.value = card.back_text;

        cardDialog.showModal();
    }

    addForm.addEventListener("submit", (event) => {
        event.preventDefault();
        addEntry();
    });

    cardDialogForm.addEventListener("submit", (event) => {
        event.preventDefault();
        updateEntry();
    });

    loadRows();
</script>

<?php
include_once "./layout/footer.php";
?>