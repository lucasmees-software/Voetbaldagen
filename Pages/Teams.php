<?php
require_once '../Classes/Teams.php';

// verbinding met de database (aanpassen naar jullie eigen verbinding)
$conn = new PDO('mysql:host=localhost;dbname=voetbaldagen_horst', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$teams = new Teams($conn);
$lijst = $teams->ophalen();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Teams</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>

<?php include '../Includes/Header.html'; ?>

<h1>Teams</h1>

<table>
    <tr>
        <th>Naam</th>
        <th>Editie</th>
        <th>Doel aantal deelnemers</th>
        <th>Kleedlokaal</th>
    </tr>
    <?php foreach ($lijst as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['naam']) ?></td>
            <td><?= htmlspecialchars($t['editie_id']) ?></td>
            <td><?= htmlspecialchars($t['doel_aantal_deelnemers']) ?></td>
            <td><?= htmlspecialchars($t['kleedlokaal'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>