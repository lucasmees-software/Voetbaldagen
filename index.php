
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
    <meta charset="UTF-8">
    <title>Database tabellen</title>
</head>
<body>

    <h1>Tabellen in database voetbal</h1>

    <?php if (empty($tabellen)): ?>
        <p>Er staan geen tabellen in deze database.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tabellen as $tabel): ?>
                <li><?= htmlspecialchars($tabel) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</body>
</html>
