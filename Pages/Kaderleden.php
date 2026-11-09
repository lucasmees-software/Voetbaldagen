<?php
require_once '../Classes/Deelnemers.php';

// conn
$conn = new PDO('mysql:host=localhost;dbname=voetbaldagen_horst', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$deelnemers = new Deelnemers($conn);

// formulier verwerken en daarna de pagina opnieuw laden
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deelnemers->toevoegen($_POST);
    header('Location: Deelnemers.php');
    exit;
}

$lijst = $deelnemers->ophalen();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Deelnemers</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>

<h1>Deelnemers</h1>

<p class="midden">
    <button type="button" onclick="document.getElementById('popup').showModal()">Deelnemer toevoegen</button>
</p>

<!-- lijst met deelnemers -->
<table>
    <tr>
        <th>Naam</th>
        <th>Geboortedatum</th>
        <th>Woonplaats</th>
        <th>Telefoon</th>
    </tr>
    <?php foreach ($lijst as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['roepnaam'] . ' ' . $d['voorvoegsel'] . ' ' . $d['achternaam']) ?></td>
            <td><?= htmlspecialchars($d['geboortedatum'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['woonplaats'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['telefoon'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- het schermpje -->
<dialog id="popup">
    <h2>Deelnemer toevoegen</h2>

    <form method="post">
        <label>Roepnaam *</label>
        <input type="text" name="roepnaam" required>

        <label>Voorvoegsel</label>
        <input type="text" name="voorvoegsel">

        <label>Achternaam *</label>
        <input type="text" name="achternaam" required>

        <label>Geboortedatum</label>
        <input type="date" name="geboortedatum">

        <label>Woonplaats</label>
        <input type="text" name="woonplaats">

        <label>Telefoon</label>
        <input type="text" name="telefoon">

        <button type="submit">Opslaan</button>
        <button type="button" onclick="document.getElementById('popup').close()">Annuleren</button>
    </form>
</dialog>

</body>
</html>