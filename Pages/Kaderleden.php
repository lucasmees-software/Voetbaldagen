<?php
require_once __DIR__ . '/../Includes/Beveiliging.php';
require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Classes/Kaderleden.php';

$kaderleden = (new Kaderleden())->getKaderleden($pdo);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Kaderleden</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>
<?php include __DIR__ . '/../Includes/Navigatie.php'; ?>

<main>
    <h1>Kaderleden</h1>
    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>E-mail</th>
                <th>Telefoon</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($kaderleden as $kaderlid): ?>
            <tr>
                <td><?= h($kaderlid['voornaam'] . ' ' . $kaderlid['achternaam']) ?></td>
                <td><?= h($kaderlid['email'] ?? '') ?></td>
                <td><?= h($kaderlid['telefoon'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
