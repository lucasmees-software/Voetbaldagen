<?php
require_once __DIR__ . '/../Includes/Beveiliging.php';
require_once __DIR__ . '/../Database/Connection.php';

$wedstrijden = $pdo->query(
    "SELECT editie, toernooivorm, thuisteam, uitteam, veld, gepland_vanaf,
            status, score_thuis, score_uit
     FROM v_wedstrijd_overzicht
     ORDER BY gepland_vanaf, wedstrijd_id"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Wedstrijden</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>
<?php include __DIR__ . '/../Includes/Navigatie.php'; ?>

<main>
    <h1>Wedstrijden</h1>
    <?php if (!$wedstrijden): ?>
        <p>Er zijn nog geen wedstrijden gepland.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Editie</th>
                    <th>Toernooi</th>
                    <th>Thuisteam</th>
                    <th>Uitteam</th>
                    <th>Veld</th>
                    <th>Start</th>
                    <th>Status</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($wedstrijden as $wedstrijd): ?>
                <tr>
                    <td><?= h($wedstrijd['editie']) ?></td>
                    <td><?= h($wedstrijd['toernooivorm']) ?></td>
                    <td><?= h($wedstrijd['thuisteam']) ?></td>
                    <td><?= h($wedstrijd['uitteam']) ?></td>
                    <td><?= h($wedstrijd['veld'] ?? '') ?></td>
                    <td><?= h($wedstrijd['gepland_vanaf'] ?? '') ?></td>
                    <td><?= h($wedstrijd['status']) ?></td>
                    <td>
                        <?= $wedstrijd['score_thuis'] === null
                            ? '—'
                            : (int) $wedstrijd['score_thuis'] . ' - ' . (int) $wedstrijd['score_uit'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
