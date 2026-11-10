<?php
require_once '../Classes/Deelnemers.php';

// verbinding met de database (aanpassen naar jullie eigen verbinding)
$conn = new PDO('mysql:host=localhost;dbname=voetbaldagen_horst', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$deelnemers = new Deelnemers($conn);
$melding = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actie = $_POST['actie'];

    if ($actie == 'verwijderen') {
        $deelnemers->verwijderen($_POST['deelnemer_id']);
        header('Location: Deelnemers.php');
        exit;
    }

    if (trim($_POST['roepnaam']) == "" || trim($_POST['achternaam']) == "") {
        $melding = "Vul minimaal roepnaam en achternaam in.";
    } else {
        if ($actie == 'aanpassen') {
            $deelnemers->aanpassen($_POST['deelnemer_id'], $_POST);
        } else {
            $deelnemers->toevoegen($_POST);
        }
        // pagina opnieuw laden zodat hij niet dubbel opslaat bij verversen
        header('Location: Deelnemers.php');
        exit;
    }
}

// als er ?bewerk=ID in de url staat, laden we die deelnemer in het formulier
$bewerk = null;
if (isset($_GET['bewerk'])) {
    $bewerk = $deelnemers->ophalenEen($_GET['bewerk']);
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
<?php include __DIR__ . "/../Includes/Navigatie.php"; ?>

<?php include '../Includes/Header.html'; ?>

<h1><?= $bewerk ? 'Deelnemer aanpassen' : 'Deelnemer toevoegen' ?></h1>

<?php if ($melding != ""): ?>
    <p class="melding"><?= htmlspecialchars($melding) ?></p>
<?php endif; ?>

<form method="post" class="formulier">
    <input type="hidden" name="actie" value="<?= $bewerk ? 'aanpassen' : 'toevoegen' ?>">
    <input type="hidden" name="deelnemer_id" value="<?= $bewerk['deelnemer_id'] ?? '' ?>">

    <label>Roepnaam *</label>
    <input type="text" name="roepnaam" value="<?= htmlspecialchars($bewerk['roepnaam'] ?? '') ?>" required>

    <label>Voorvoegsel</label>
    <input type="text" name="voorvoegsel" value="<?= htmlspecialchars($bewerk['voorvoegsel'] ?? '') ?>">

    <label>Achternaam *</label>
    <input type="text" name="achternaam" value="<?= htmlspecialchars($bewerk['achternaam'] ?? '') ?>" required>

    <label>Geboortedatum</label>
    <input type="date" name="geboortedatum" value="<?= htmlspecialchars($bewerk['geboortedatum'] ?? '') ?>">

    <label>Woonplaats</label>
    <input type="text" name="woonplaats" value="<?= htmlspecialchars($bewerk['woonplaats'] ?? '') ?>">

    <label>Telefoon</label>
    <input type="text" name="telefoon" value="<?= htmlspecialchars($bewerk['telefoon'] ?? '') ?>">

    <button type="submit"><?= $bewerk ? 'Wijzigingen opslaan' : 'Opslaan' ?></button>
    <?php if ($bewerk): ?>
        <a href="Deelnemers.php">Annuleren</a>
    <?php endif; ?>
</form>

<h2>Alle deelnemers</h2>

<table>
    <tr>
        <th>Naam</th>
        <th>Geboortedatum</th>
        <th>Woonplaats</th>
        <th>Telefoon</th>
        <th></th>
    </tr>
    <?php foreach ($lijst as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['roepnaam'] . ' ' . $d['voorvoegsel'] . ' ' . $d['achternaam']) ?></td>
            <td><?= htmlspecialchars($d['geboortedatum'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['woonplaats'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['telefoon'] ?? '') ?></td>
            <td>
                <a href="Deelnemers.php?bewerk=<?= $d['deelnemer_id'] ?>">Aanpassen</a>

                <form method="post" class="verwijder">
                    <input type="hidden" name="actie" value="verwijderen">
                    <input type="hidden" name="deelnemer_id" value="<?= $d['deelnemer_id'] ?>">
                    <button type="submit">Verwijderen</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>     