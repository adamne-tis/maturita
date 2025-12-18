<?php
include_once "./layout/header.php";
?>

<h1>Nazev</h1>
<p>Popis</p>

<a href="practice.php?id=1">Procvičovat</a>

<a href="import.php?id=1">Importovat</a>
<a href="export.php?id=1">Exportovat</a>
<a href="#">Vygenerovat test</a>

<table>
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
        <tr>
            <td>
                <input type="checkbox" name="" id="">
            </td>
            <td>1</td>
            <td>Hallo</td>
            <td>Ahoj</td>
            <td>
                <button onclick="alert(1)">Upravit</button>
            </td>
        </tr>
    </tbody>
</table>

<dialog>
    <form method="post">
        <input type="hidden" name="id" value="1">
        <p>
            <label for="front_text">Přední text:</label>
            <input type="text" name="front_text" id="front_text">
        </p>
        <p>
            <label for="back_text">Zadní text:</label>
            <input type="text" name="back_text" id="back_text">
        </p>
        <button>Uložit</button>
        <button>Odstranit</button>
    </form>
</dialog>

<?php
include_once "./layout/footer.php";
?>