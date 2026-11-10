<?php
require_once __DIR__ . '/Database/Connection.php';
require_once __DIR__ . '/Classes/Teams.php';

$teamsClass = new Teams($pdo);
$teams = $teamsClass->getTeams();
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <link rel="stylesheet" href="/Css/Stylesheet.css">
    <meta charset="UTF-8">
    <title>Teams</title>
</head>

<body>
<?php include __DIR__ . "/Includes/Navigatie.php"; ?>

    <h1>Teams</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Teamnaam</th>
        </tr>

        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= $team['ploeg_id'] ?></td>
                <td><?= htmlspecialchars($team['naam']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>
