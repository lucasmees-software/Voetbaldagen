<?php
require_once __DIR__ . '/../Classes/Deelnemers.php';

// TODO: vervang dit door jullie eigen database-verbinding
$db = new PDO(
    'mysql:host=localhost;dbname=voetbaldagen_horst;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$deelnemers = new Deelnemers($db);
$melding = '';

// Formulier verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verplichte velden checken
    if (trim($_POST['roepnaam']) === '' || trim($_POST['achternaam']) === '') {
        $melding = 'Roepnaam en achternaam zijn verplicht.';
    } elseif ($deelnemers->toevoegen($_POST)) {
        $melding = 'Deelnemer is toegevoegd!';
    } else {
        $melding = 'Er ging iets mis bij het opslaan.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deelnemer toevoegen</title>
    <!-- Pad naar de stylesheet, pas aan als jullie het anders includen -->
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>

<?php // include __DIR__ . '/../Includes/Header.html'; ?>

<h1>Deelnemer toevoegen</h1>

<?php if ($melding): ?>
    <div class="melding"><?= htmlspecialchars($melding) ?></div>
<?php endif; ?>

<form method="post" class="formulier">
    <label for="roepnaam">Roepnaam *</label>
    <input type="text" id="roepnaam" name="roepnaam" maxlength="100" required>

    <label for="voorvoegsel">Voorvoegsel</label>
    <input type="text" id="voorvoegsel" name="voorvoegsel" maxlength="40">

    <label for="achternaam">Achternaam *</label>
    <input type="text" id="achternaam" name="achternaam" maxlength="120" required>

    <label for="geboortedatum">Geboortedatum</label>
    <input type="date" id="geboortedatum" name="geboortedatum">

    <label for="woonplaats">Woonplaats</label>
    <input type="text" id="woonplaats" name="woonplaats" maxlength="120">

    <label for="telefoon">Telefoon</label>
    <input type="tel" id="telefoon" name="telefoon" maxlength="35">

    <button type="submit">Opslaan</button>
</form>

</body>
</html>