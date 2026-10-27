<?php
require_once __DIR__ . '/database/conn.php';
require_once __DIR__ . '/classes/teams.php';

$teamsClass = new Teams($pdo);
$teams = $teamsClass->getTeams();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams</title>
</head>
<body>

<?php include('../voetbaldagen/includes/header.html') ?>

<div class="teams">
    <h1>Teams</h1>

    <table border="1">
        <thead>
            <tr>
                <th>Teamnaam</th>
                <th>Soort</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $team): ?>
                <tr>
                    <td><?= htmlspecialchars($team['naam']) ?></td>
                    <td><?= htmlspecialchars($team['soort']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
