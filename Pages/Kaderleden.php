
<?php
require_once __DIR__ . '/../Classes/Kaderleden.php';

$pdo = new PDO(
    'mysql:host=localhost;dbname=voetbaldagen_horst;charset=utf8mb4',
    'root',
    ''
);

$kaderledenClass = new Kaderleden($pdo);
$zoeken = trim($_GET['zoeken'] ?? '');
$kaderleden = $kaderledenClass->getKaderleden($zoeken);

function veilig($waarde) {
    return htmlspecialchars((string) ($waarde ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaderleden</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
        
    <style>
        main {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }

        input, button {
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            cursor: pointer;
            background: #eee;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../Includes/Header.html'; ?>

<main>
    <h1>Kaderleden</h1>

    <form method="GET">
        <input
            type="text"
            name="zoeken"
            placeholder="Zoek een kaderlid..."
            value="<?= veilig($zoeken) ?>"
        >
        <button type="submit">Zoeken</button>
        <a href="Kaderleden.php">Wissen</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Voornaam</th>
                <th>Achternaam</th>
                <th>Telefoon</th>
                <th>E-mail</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($kaderleden) > 0): ?>
                <?php foreach ($kaderleden as $kaderlid): ?>
                    <tr>
                        <td><?= veilig($kaderlid['voornaam']) ?></td>
                        <td><?= veilig($kaderlid['achternaam']) ?></td>
                        <td><?= veilig($kaderlid['telefoon']) ?></td>
                        <td><?= veilig($kaderlid['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Geen kaderleden gevonden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

</body>
</html>
