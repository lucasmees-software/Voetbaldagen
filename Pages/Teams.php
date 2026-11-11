<?php
require_once __DIR__ . '/../Includes/Beveiliging.php';
require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Classes/Teams.php';

$teams = new Teams($pdo);
$melding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    controleerCsrf();
    vereistInloggen();

    $actie = tekstVeld($_POST, 'actie');
    $id = positiefGetal($_POST['basisteam_id'] ?? null);

    try {
        if ($actie === 'verwijderen' && $id > 0) {
            $teams->verwijderen($id);
        } elseif (in_array($actie, ['toevoegen', 'aanpassen'], true)) {
            $editieId = positiefGetal($_POST['editie_id'] ?? null);
            $doel = positiefGetal($_POST['doel_aantal_deelnemers'] ?? null);
            $naam = tekstVeld($_POST, 'naam');

            if ($editieId === false || $doel === false || $naam === ''
                || ($actie === 'aanpassen' && $id === false)
            ) {
                $melding = 'Vul alle verplichte teamgegevens correct in.';
            } else {
                $gegevens = [
                    'editie_id' => $editieId,
                    'naam' => $naam,
                    'doel_aantal_deelnemers' => $doel,
                    'kleedlokaal' => tekstVeld($_POST, 'kleedlokaal'),
                ];

                if ($actie === 'aanpassen') {
                    $teams->aanpassen($id, $gegevens);
                } else {
                    $teams->toevoegen($gegevens);
                }
            }
        } else {
            $melding = 'Ongeldige actie.';
        }

        if ($melding === '') {
            header('Location: Teams.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $melding = $actie === 'verwijderen'
            ? 'Dit team kan niet worden verwijderd, omdat er nog gegevens aan gekoppeld zijn.'
            : 'Opslaan is niet gelukt. Controleer of dit team al bestaat voor deze editie.';
    }
}

$lijst = $teams->ophalen();
$edities = $teams->ophalenEdities();
$ingelogd = !empty($_SESSION['account_id']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Teams</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>
<?php include __DIR__ . '/../Includes/Navigatie.php'; ?>

<main>
    <h1>Teams</h1>

    <?php if ($melding !== ''): ?>
        <p class="melding"><?= h($melding) ?></p>
    <?php endif; ?>

    <?php if ($ingelogd): ?>
        <p class="midden">
            <button type="button" onclick="openModal('toevoegModal')">Team toevoegen</button>
        </p>
    <?php else: ?>
        <p>Log in om teams toe te voegen, aan te passen of te verwijderen.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Editie</th>
                <th>Doel aantal deelnemers</th>
                <th>Kleedlokaal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lijst as $team): ?>
            <tr <?php if ($ingelogd): ?>class="teamrij" onclick="openBewerk(this)"<?php endif; ?>
                data-id="<?= (int) $team['basisteam_id'] ?>"
                data-editie="<?= (int) $team['editie_id'] ?>"
                data-naam="<?= h($team['naam']) ?>"
                data-doel="<?= (int) $team['doel_aantal_deelnemers'] ?>"
                data-kleedlokaal="<?= h($team['kleedlokaal'] ?? '') ?>">
                <td><?= h($team['naam']) ?></td>
                <td><?= h($team['editie_naam']) ?></td>
                <td><?= (int) $team['doel_aantal_deelnemers'] ?></td>
                <td><?= h($team['kleedlokaal'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($ingelogd): ?>
        <div class="modal" id="toevoegModal" style="display:none">
            <div class="modal-inhoud">
                <h2>Team toevoegen</h2>
                <form method="post">
                    <?php csrfVeld(); ?>
                    <input type="hidden" name="actie" value="toevoegen">

                    <label>Editie *</label>
                    <select name="editie_id" required>
                        <?php foreach ($edities as $editie): ?>
                            <option value="<?= (int) $editie['editie_id'] ?>"><?= h($editie['naam']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Naam *</label>
                    <input type="text" name="naam" maxlength="100" required>

                    <label>Doel aantal deelnemers *</label>
                    <input type="number" name="doel_aantal_deelnemers" min="1" value="16" required>

                    <label>Kleedlokaal</label>
                    <input type="text" name="kleedlokaal" maxlength="80">

                    <button type="submit">Opslaan</button>
                    <button type="button" onclick="closeModal(this)">Annuleren</button>
                </form>
            </div>
        </div>

        <div class="modal" id="bewerkModal" style="display:none">
            <div class="modal-inhoud">
                <h2>Team aanpassen</h2>
                <form method="post">
                    <?php csrfVeld(); ?>
                    <input type="hidden" name="basisteam_id" id="bewerkId">

                    <label>Editie *</label>
                    <select name="editie_id" id="bewerkEditie" required>
                        <?php foreach ($edities as $editie): ?>
                            <option value="<?= (int) $editie['editie_id'] ?>"><?= h($editie['naam']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Naam *</label>
                    <input type="text" name="naam" id="bewerkNaam" maxlength="100" required>

                    <label>Doel aantal deelnemers *</label>
                    <input type="number" name="doel_aantal_deelnemers" id="bewerkDoel" min="1" required>

                    <label>Kleedlokaal</label>
                    <input type="text" name="kleedlokaal" id="bewerkKleedlokaal" maxlength="80">

                    <button type="submit" name="actie" value="aanpassen">Opslaan</button>
                    <button type="submit" name="actie" value="verwijderen" formnovalidate
                        onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">Verwijderen</button>
                    <button type="button" onclick="closeModal(this)">Annuleren</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(button) {
        button.closest('.modal').style.display = 'none';
    }

    function openBewerk(row) {
        document.getElementById('bewerkId').value = row.dataset.id;
        document.getElementById('bewerkEditie').value = row.dataset.editie;
        document.getElementById('bewerkNaam').value = row.dataset.naam;
        document.getElementById('bewerkDoel').value = row.dataset.doel;
        document.getElementById('bewerkKleedlokaal').value = row.dataset.kleedlokaal;
        openModal('bewerkModal');
    }
</script>
</body>
</html>
