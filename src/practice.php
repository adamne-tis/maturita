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
?>

<?php
$page_title = "Procvičování | Flashcards";
include_once "./layout/header.php";
?>

<div class="practice-container">
    <div class="card">
        <p id="front-text">V balíčku nejsou žádné karty</p>
        <p id="back-text" style="display: none;"></p>
    </div>

    <p id="progress" onclick="showJumpDialog()">0/0</p>

    <div>
        <button type="button" id="flip-btn" onclick="flipCard()">Otočit</button>
        <button type="button" id="prev-btn" onclick="previousCard()">&lt</button>
        <button type="button" id="next-btn" onclick="nextCard()">&gt</button>
    </div>
</div>

<dialog>
    <label for="card-num">Číslo karty:</label>
    <input type="number" name="card-num" id="card-num" min="1">
    <input type="button" value="Zobrazit" onclick="jumpToCard()">
</dialog>

<script>
    const frontText = document.querySelector("#front-text");
    const backText = document.querySelector("#back-text");

    const progress = document.querySelector("#progress");

    const jumpDialog = document.querySelector("dialog");
    const jumpCardNum = jumpDialog.querySelector("#card-num");

    let cards = [];
    let cardNum = 0;
    let flipped = false;

    function updateProgress() {
        progress.innerText = `${cardNum+1}/${cards.length}`;
    }

    function previousCard() {
        if (cardNum == 0) {
            return;
        }

        cardNum -= 1;
        flipped = false;
        displayCard();
    }

    function nextCard() {
        if (cards.length == 0 || cardNum == cards.length - 1) {
            return;
        }

        cardNum += 1;
        flipped = false;
        displayCard();
    }

    function flipCard() {
        flipped = !flipped;
        displayCard();
    }

    function displayCard() {
        if (cards.length == 0) {
            return;
        }

        let card = cards[cardNum];

        if (flipped) {
            frontText.style.display = "none";
            backText.style.display = "";
        } else {
            frontText.style.display = "";
            backText.style.display = "none";
        }

        frontText.innerText = card.front_text;
        backText.innerText = card.back_text;

        updateProgress();
    }

    async function fetchCards() {
        try {
            const response = await fetch("./api/cards.php?study_set_id=<?php echo $study_set_id; ?>");
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            const result = await response.json();
            cards = result;

            displayCard();
        } catch (error) {
            console.error(error.message);
        }
    }

    function showJumpDialog() {
        jumpCardNum.value = cardNum + 1;
        jumpCardNum.max = cards.length;
        jumpDialog.showModal();
    }

    function jumpToCard() {
        cardNum = parseInt(jumpCardNum.value) - 1;
        flipped = false;
        displayCard();
        jumpDialog.close();
    }

    document.addEventListener("keydown", function (event) {
        if (event.key == "ArrowLeft") {
            previousCard();
        } else if (event.key == "ArrowRight") {
            nextCard();
        } else if (event.key == " ") {
            event.preventDefault();
            flipCard();
        } else {
            return true;
        }
    });

    fetchCards();
</script>

<?php
include_once "./layout/footer.php";
?>