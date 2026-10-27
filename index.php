
<?php
require_once __DIR__ . '/database/conn.php';

try {
    $query = $pdo->query("SHOW TABLES");
    $tabellen = $query->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Fout bij ophalen tabellen: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <link rel="stylesheet" href="../css/stylesheet.css">
    <meta charset="UTF-8">
    <title>Database tabellen</title>
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
