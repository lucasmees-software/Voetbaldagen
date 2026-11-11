<?php
require_once __DIR__ . '/../Includes/Beveiliging.php';
require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Classes/Deelnemers.php';

$deelnemers = new Deelnemers($pdo);
$melding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    controleerCsrf();
    vereistInloggen();

    $actie = tekstVeld($_POST, 'actie');
    $id = positiefGetal($_POST['deelnemer_id'] ?? null);

    if ($actie === 'verwijderen' && $id > 0) {
        try {
            $deelnemers->verwijderen($id);
            header('Location: Deelnemers.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $melding = 'Deze deelnemer kan niet worden verwijderd.';
        }
    } elseif (in_array($actie, ['toevoegen', 'aanpassen'], true)
        && tekstVeld($_POST, 'roepnaam') !== ''
        && tekstVeld($_POST, 'achternaam') !== ''
        && ($actie === 'toevoegen' || $id > 0)
    ) {
        $geboortedatum = tekstVeld($_POST, 'geboortedatum');
        $datum = $geboortedatum === '' ? null : DateTimeImmutable::createFromFormat('!Y-m-d', $geboortedatum);

        if ($geboortedatum !== '' && (!$datum || $datum->format('Y-m-d') !== $geboortedatum)) {
            $melding = 'Vul een geldige geboortedatum in.';
        } else {
            $gegevens = [
                'roepnaam' => tekstVeld($_POST, 'roepnaam'),
                'voorvoegsel' => tekstVeld($_POST, 'voorvoegsel'),
                'achternaam' => tekstVeld($_POST, 'achternaam'),
                'geboortedatum' => $geboortedatum,
                'woonplaats' => tekstVeld($_POST, 'woonplaats'),
                'telefoon' => tekstVeld($_POST, 'telefoon'),
            ];

            try {
                if ($actie === 'aanpassen') {
                    $deelnemers->aanpassen($id, $gegevens);
                } else {
                    $deelnemers->toevoegen($gegevens);
                }

                header('Location: Deelnemers.php');
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $melding = 'Opslaan van de deelnemer is niet gelukt.';
            }
        }
    } else {
        $melding = 'Vul minimaal een roepnaam en achternaam in.';
    }
}

$bewerkId = filter_var($_GET['bewerk'] ?? null, FILTER_VALIDATE_INT);
$bewerk = $bewerkId > 0 ? $deelnemers->ophalenEen($bewerkId) : false;
$lijst = $deelnemers->ophalen();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Deelnemers</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>
<?php include __DIR__ . '/../Includes/Navigatie.php'; ?>

<main>
    <h1><?= $bewerk ? 'Deelnemer aanpassen' : 'Deelnemers' ?></h1>

    <?php if ($melding !== ''): ?>
        <p class="melding"><?= h($melding) ?></p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['account_id'])): ?>
        <form method="post" class="formulier">
            <?php csrfVeld(); ?>
            <input type="hidden" name="actie" value="<?= $bewerk ? 'aanpassen' : 'toevoegen' ?>">
            <input type="hidden" name="deelnemer_id" value="<?= h($bewerk['deelnemer_id'] ?? '') ?>">

            <label>Roepnaam *</label>
            <input type="text" name="roepnaam" maxlength="100" value="<?= h($bewerk['roepnaam'] ?? '') ?>" required>

            <label>Voorvoegsel</label>
            <input type="text" name="voorvoegsel" maxlength="40" value="<?= h($bewerk['voorvoegsel'] ?? '') ?>">

            <label>Achternaam *</label>
            <input type="text" name="achternaam" maxlength="120" value="<?= h($bewerk['achternaam'] ?? '') ?>" required>

            <label>Geboortedatum</label>
            <input type="date" name="geboortedatum" value="<?= h($bewerk['geboortedatum'] ?? '') ?>">

            <label>Woonplaats</label>
            <input type="text" name="woonplaats" maxlength="120" value="<?= h($bewerk['woonplaats'] ?? '') ?>">

            <label>Telefoon</label>
            <input type="text" name="telefoon" maxlength="35" value="<?= h($bewerk['telefoon'] ?? '') ?>">

            <button type="submit"><?= $bewerk ? 'Wijzigingen opslaan' : 'Opslaan' ?></button>
            <?php if ($bewerk): ?>
                <a href="Deelnemers.php">Annuleren</a>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <p>Log in om deelnemers toe te voegen, aan te passen of te verwijderen.</p>
    <?php endif; ?>

    <h2>Alle deelnemers</h2>
    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Geboortedatum</th>
                <th>Woonplaats</th>
                <th>Telefoon</th>
                <?php if (!empty($_SESSION['account_id'])): ?><th></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lijst as $deelnemer): ?>
            <tr>
                <td><?= h(trim($deelnemer['roepnaam'] . ' ' . ($deelnemer['voorvoegsel'] ?? '') . ' ' . $deelnemer['achternaam'])) ?></td>
                <td><?= h($deelnemer['geboortedatum'] ?? '') ?></td>
                <td><?= h($deelnemer['woonplaats'] ?? '') ?></td>
                <td><?= h($deelnemer['telefoon'] ?? '') ?></td>
                <?php if (!empty($_SESSION['account_id'])): ?>
                    <td>
                        <a href="Deelnemers.php?bewerk=<?= (int) $deelnemer['deelnemer_id'] ?>">Aanpassen</a>
                        <form method="post" class="verwijder">
                            <?php csrfVeld(); ?>
                            <input type="hidden" name="actie" value="verwijderen">
                            <input type="hidden" name="deelnemer_id" value="<?= (int) $deelnemer['deelnemer_id'] ?>">
                            <button type="submit" onclick="return confirm('Weet je zeker dat je deze deelnemer wilt verwijderen?')">Verwijderen</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
