<?php
require_once __DIR__ . '/classes/teams.php';

$teamsClass = new Teams($pdo);
$teams = $teamsClass->getTeams();
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <link rel="stylesheet" href="../css/stylesheet.css">
    <meta charset="UTF-8">
    <title>Teams</title>
</head>

<body>
    
    <h1>Teams</h1>
    <table border="1">
        <tr>
            <th>ID</t5h>
            <th>Teamnaam</th>
        </tr>

        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= $team['id'] ?></td>
                <td><?= htmlspecialchars($team['naam']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>