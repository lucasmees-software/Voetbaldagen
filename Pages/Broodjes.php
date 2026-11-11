<?php
global $pdo;
session_start();

require_once __DIR__ . '/../Classes/Broodjes.php';

try {
    require_once __DIR__ . '/../Database/BroodjesData.php';
    $broodjesClass = new Broodjes($pdo);
} catch (PDOException $exception) {
    $broodjesClass = null;
}

$_SESSION['broodjes_csrf_token'] ??= bin2hex(random_bytes(32));
$csrfToken = $_SESSION['broodjes_csrf_token'];
$melding = $broodjesClass === null
    ? 'De databaseverbinding is niet beschikbaar. Start MySQL en probeer opnieuw.'
    : '';
$naam = '';
$weekStart = date('Y-m-d', strtotime('monday this week'));
$keuzes = [];
$vrijdagKeuze = '';

function veilig($waarde): string
{
    return htmlspecialchars((string) $waarde, ENT_QUOTES, 'UTF-8');
}

function isMaandag(string $datum): bool
{
    $weekDatum = DateTimeImmutable::createFromFormat('!Y-m-d', $datum);

    return $weekDatum !== false
        && $weekDatum->format('Y-m-d') === $datum
        && $weekDatum->format('N') === '1';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $naam = is_string($_POST['naam'] ?? null) ? trim($_POST['naam']) : '';
    $postedWeek = $_POST['week_start'] ?? '';
    $weekStart = is_string($postedWeek) ? $postedWeek : '';
    $postedKeuzes = $_POST['broodjes'] ?? [];
    $keuzes = is_array($postedKeuzes) ? $postedKeuzes : [];
    $postedVrijdagKeuze = $_POST['vrijdag_keuze'] ?? '';
    $vrijdagKeuze = is_string($postedVrijdagKeuze) ? $postedVrijdagKeuze : '';

    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $melding = 'Ongeldige aanvraag. Vernieuw de pagina en probeer opnieuw.';
    } elseif ($naam === '' || strlen($naam) > 100) {
        $melding = 'Vul een naam in van maximaal 100 tekens.';
    } elseif (!isMaandag($weekStart)) {
        $melding = 'Kies de maandag waarop de week begint.';
        $weekStart = date('Y-m-d', strtotime('monday this week'));
    } else {
        foreach (Broodjes::DAGEN as $dag => $label) {
            if (!is_string($keuzes[$dag] ?? null) || !isset(Broodjes::KEUZES[$keuzes[$dag]])) {
                $melding = 'Kies voor iedere dag een geldig broodje.';
                break;
            }
        }

        if ($melding === '' && !isset(Broodjes::VRIJDAG_KEUZES[$vrijdagKeuze])) {
            $melding = 'Kies voor vrijdag een kroket of frikandel.';
        }
    }

    if ($melding === '') {
        try {
            $broodjesClass->slaWeekBestellingOp($naam, $weekStart, $keuzes, $vrijdagKeuze);
            header('Location: Broodjes.php?week=' . urlencode($weekStart) . '&melding=opgeslagen');
            exit;
        } catch (PDOException $exception) {
            $melding = 'De bestelling kon niet worden opgeslagen. Controleer of de broodjes-tabel is aangemaakt.';
        }
    }
} else {
    $postedWeek = $_GET['week'] ?? '';
    if (is_string($postedWeek) && $postedWeek !== '') {
        if (isMaandag($postedWeek)) {
            $weekStart = $postedWeek;
        } else {
            $melding = 'Kies de maandag waarop de week begint.';
        }
    }

    if ($melding === '' && ($_GET['melding'] ?? '') === 'opgeslagen') {
        $melding = 'Bestelling opgeslagen.';
    }
}

if ($broodjesClass === null) {
    $bestellingen = [];
} else {
    try {
        $bestellingen = $broodjesClass->getBestellingenVoorWeek($weekStart);
    } catch (PDOException $exception) {
        $bestellingen = [];
        $melding = 'De bestellingen konden niet worden geladen. Controleer of de broodjes-tabel is aangemaakt.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>Broodjes</title>
</head>
<body>
<?php include __DIR__ . '/../Includes/Navigatie.php'; ?>

<main>
    <h1>Broodjes bestellen</h1>

    <?php if ($melding !== ''): ?>
        <p role="status"><?= veilig($melding) ?></p>
    <?php endif; ?>

    <form method="get" action="Broodjes.php">
        <label for="week">Week vanaf maandag</label>
        <input id="week" name="week" type="date" value="<?= veilig($weekStart) ?>" required>
        <button type="submit">Week bekijken</button>
    </form>

    <h2>Bestelling invullen</h2>
    <form method="post" action="Broodjes.php">
        <input type="hidden" name="csrf_token" value="<?= veilig($csrfToken) ?>">
        <label for="naam">Naam</label>
        <input id="naam" name="naam" type="text" maxlength="100" value="<?= veilig($naam) ?>" required>
        <label for="week_start">Week vanaf maandag</label>
        <input id="week_start" name="week_start" type="date" value="<?= veilig($weekStart) ?>" required>

        <table>
            <thead>
                <tr>
                    <th>Dag</th>
                    <th>Broodje</th>
                    <th>Extra op vrijdag</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (Broodjes::DAGEN as $dag => $label): ?>
                    <tr>
                        <td><?= veilig($label) ?></td>
                        <td>
                            <select name="broodjes[<?= veilig($dag) ?>]" aria-label="Broodje voor <?= veilig($label) ?>" required>
                                <option value="">Kies een broodje</option>
                                <?php foreach (Broodjes::KEUZES as $waarde => $keuzeLabel): ?>
                                    <option value="<?= veilig($waarde) ?>"<?= ($keuzes[$dag] ?? '') === $waarde ? ' selected' : '' ?>>
                                        <?= veilig($keuzeLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <?php if ($dag === 'vrijdag'): ?>
                                <select name="vrijdag_keuze" aria-label="Kroket of frikandel op vrijdag" required>
                                    <option value="">Kies kroket of frikandel</option>
                                    <?php foreach (Broodjes::VRIJDAG_KEUZES as $waarde => $keuzeLabel): ?>
                                        <option value="<?= veilig($waarde) ?>"<?= $vrijdagKeuze === $waarde ? ' selected' : '' ?>>
                                            <?= veilig($keuzeLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Bestelling opslaan</button>
    </form>

    <h2>Bestellingen voor de week van <?= veilig($weekStart) ?></h2>
    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <?php foreach (Broodjes::DAGEN as $label): ?>
                    <th><?= veilig($label) ?></th>
                <?php endforeach; ?>
                <th>Vrijdag extra</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bestellingen): ?>
                <?php foreach ($bestellingen as $bestelNaam => $bestelling): ?>
                    <tr>
                        <td><?= veilig($bestelNaam) ?></td>
                        <?php foreach (Broodjes::DAGEN as $dag => $label): ?>
                            <td><?= veilig(Broodjes::KEUZES[$bestelling['broodjes'][$dag] ?? ''] ?? '-') ?></td>
                        <?php endforeach; ?>
                        <td><?= veilig(Broodjes::VRIJDAG_KEUZES[$bestelling['vrijdag_keuze'] ?? ''] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Er zijn nog geen bestellingen voor deze week.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>