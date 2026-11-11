<?php
require_once __DIR__ . '/Includes/Beveiliging.php';
require_once __DIR__ . '/Database/Connection.php';

$teams = $pdo->query('SELECT ploeg_id, naam FROM ploeg ORDER BY naam')->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <link rel="stylesheet" href="/voetbaldagen/Css/Stylesheet.css">
    <meta charset="UTF-8">
    <title>Teams</title>
</head>

<body>
<?php include __DIR__ . '/Includes/Navigatie.php'; ?>

<main>
    <h1>Teams</h1>
    <table>
        <thead>
            <tr><th>ID</th><th>Teamnaam</th></tr>
        </thead>
        <tbody>
        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= (int) $team['ploeg_id'] ?></td>
                <td><?= h($team['naam']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>

</html>
