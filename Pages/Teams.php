<?php
session_start();

require_once __DIR__ . '/../Database/TeamsConnection.php';
require_once __DIR__ . '/../Classes/Teams.php';

$teamsClass = new Teams($pdo);
$_SESSION['teams_csrf_token'] ??= bin2hex(random_bytes(32));
$csrfToken = $_SESSION['teams_csrf_token'];
$message = '';
$teamNaam = '';
$aantalPersonen = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $message = 'Ongeldige aanvraag. Vernieuw de pagina en probeer opnieuw.';
    } elseif ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $message = 'Ongeldig team.';
        } else {
            try {
                $teamsClass->deleteTeam($id);
                header('Location: Teams.php?melding=verwijderd');
                exit;
            } catch (PDOException $exception) {
                $message = 'Team verwijderen is niet gelukt.';
            }
        }
    } elseif ($action === 'add' || $action === 'update') {
        $teamNaam = is_string($_POST['naam'] ?? null) ? trim($_POST['naam']) : '';
        $postedCount = filter_var($_POST['aantal_personen'] ?? null, FILTER_VALIDATE_INT);
        $aantalPersonen = $postedCount === false ? 0 : $postedCount;
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if ($teamNaam === '' || $postedCount === false || $postedCount < 0) {
            $message = 'Vul een teamnaam en een geldig aantal personen in.';
        } elseif ($action === 'update' && (!$id || $id < 1)) {
            $message = 'Ongeldig team.';
        } else {
            try {
                $teamsClass->saveTeam(
                        $action === 'update' ? $id : null,
                        $teamNaam,
                        $aantalPersonen
                );
                header('Location: Teams.php?melding=' . ($action === 'add' ? 'toegevoegd' : 'bijgewerkt'));
                exit;
            } catch (PDOException $exception) {
                $message = 'Team opslaan is niet gelukt.';
            }
        }
    } else {
        $message = 'Onbekende actie.';
    }
}

$meldingen = [
        'toegevoegd' => 'Team toegevoegd.',
        'bijgewerkt' => 'Team bijgewerkt.',
        'verwijderd' => 'Team verwijderd.'
];
$melding = $_GET['melding'] ?? '';
if (is_string($melding) && isset($meldingen[$melding])) {
    $message = $meldingen[$melding];
}

$editIdValue = $_GET['bewerken'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $editIdValue = $_POST['id'] ?? null;
}
$editId = filter_var($editIdValue, FILTER_VALIDATE_INT);
$editTeam = $editId && $editId > 0 ? $teamsClass->getTeam($editId) : null;

if ($editTeam !== null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $teamNaam = $editTeam['naam'];
    $aantalPersonen = (int) $editTeam['aantal_personen'];
}

$teams = $teamsClass->getTeams();

function veilig($waarde): string
{
    return htmlspecialchars((string) $waarde, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>Teams</title>
</head>
<body>
<header class="site-header">
    <nav class="navbar">
        <a href="../Index.php" class="nav-logo">Voetbaldagen Horst</a>
        <div class="nav-links">
            <a href="../Index.php">Home</a>
            <a href="Deelnemers.php">Deelnemers</a>
            <a href="Teams.php" class="active">Teams</a>
            <a href="Wedstrijden.php">Wedstrijden</a>
        </div>
    </nav>
</header>

<main>
    <h1>Teams</h1>

    <?php if ($message !== ''): ?>
        <p role="status"><?= veilig($message) ?></p>
    <?php endif; ?>

    <h2><?= $editTeam ? 'Team bewerken' : 'Team toevoegen' ?></h2>
    <form method="post" action="Teams.php">
        <input type="hidden" name="csrf_token" value="<?= veilig($csrfToken) ?>">
        <input type="hidden" name="action" value="<?= $editTeam ? 'update' : 'add' ?>">
        <?php if ($editTeam): ?>
            <input type="hidden" name="id" value="<?= (int) $editTeam['id'] ?>">
        <?php endif; ?>
        <label for="naam">Teamnaam</label>
        <input id="naam" name="naam" type="text" value="<?= veilig($teamNaam) ?>" required>
        <label for="aantal_personen">Aantal personen</label>
        <input id="aantal_personen" name="aantal_personen" type="number" min="0" value="<?= (int) $aantalPersonen ?>" required>
        <button type="submit"><?= $editTeam ? 'Opslaan' : 'Toevoegen' ?></button>
        <?php if ($editTeam): ?>
            <a href="Teams.php">Annuleren</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Teamnaam</th>
            <th>Aantal personen</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($teams): ?>
            <?php foreach ($teams as $team): ?>
                <tr>
                    <td><?= (int) $team['id'] ?></td>
                    <td><?= veilig($team['naam']) ?></td>
                    <td><?= (int) $team['aantal_personen'] ?></td>
                    <td>
                        <a href="?bewerken=<?= (int) $team['id'] ?>">Bewerken</a>
                        <form method="post" action="Teams.php" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= veilig($csrfToken) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $team['id'] ?>">
                            <button type="submit" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">Verwijderen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Er zijn nog geen teams.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
