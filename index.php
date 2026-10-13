<?php
require_once __DIR__ . '/classes/teams.php';

$teamsClass = new Teams($pdo);
$teams = $teamsClass->getTeams();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Teams</title>
</head>

<body>

<h1>Teams</h1>
<table border="1">
    <tr>
        <th>ID</th>
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

