<?php
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
<nav class="main-nav">
    <ul>
        <li><a href="/Index.php">Home</a></li>
        <li><a href="/Pages/Deelnemers.php">Deelnemers</a></li>
        <li><a href="/Pages/Teams.php">Teams</a></li>
        <li><a href="/Pages/Wedstrijden.php">Wedstrijden</a></li>
    </ul>
</nav>

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