<?php
require_once '../Classes/Teams.php';

// verbinding met de database (aanpassen naar jullie eigen verbinding)
$conn = new PDO('mysql:host=localhost;dbname=voetbaldagen_horst', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$teams = new Teams($conn);
$melding = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actie = $_POST['actie'];

    try {
        if ($actie == 'verwijderen') {
            $teams->verwijderen($_POST['basisteam_id']);
        } elseif ($actie == 'aanpassen') {
            $teams->aanpassen($_POST['basisteam_id'], $_POST);
        } else {
            $teams->toevoegen($_POST);
        }

        // pagina opnieuw laden zodat hij niet dubbel opslaat bij verversen
        header('Location: Teams.php');
        exit;
    } catch (PDOException $e) {
        if ($actie == 'verwijderen') {
            $melding = "Dit team kan niet verwijderd worden, er hangt nog iets aan vast.";
        } else {
            $melding = "Opslaan mislukt, bestaat er al een team met deze naam in deze editie?";
        }
    }
}

$lijst = $teams->ophalen();
$edities = $teams->ophalenEdities();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Teams</title>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
</head>
<body>

<?php include '../Includes/Header.html'; ?>

<h1>Teams</h1>

<?php if ($melding != ""): ?>
    <p class="melding"><?= htmlspecialchars($melding) ?></p>
<?php endif; ?>

<p class="midden">
    <button type="button" id="openToevoegen">Team toevoegen</button>
</p>

<!-- klik op een rij om het team aan te passen of te verwijderen -->
<table>
    <tr>
        <th>Naam</th>
        <th>Editie</th>
        <th>Doel aantal deelnemers</th>
        <th>Kleedlokaal</th>
    </tr>
    <?php foreach ($lijst as $t): ?>
        <tr class="teamrij"
            data-id="<?= $t['basisteam_id'] ?>"
            data-editie="<?= $t['editie_id'] ?>"
            data-naam="<?= htmlspecialchars($t['naam']) ?>"
            data-doel="<?= $t['doel_aantal_deelnemers'] ?>"
            data-kleedlokaal="<?= htmlspecialchars($t['kleedlokaal'] ?? '') ?>">
            <td><?= htmlspecialchars($t['naam']) ?></td>
            <td><?= htmlspecialchars($t['editie_naam']) ?></td>
            <td><?= htmlspecialchars($t['doel_aantal_deelnemers']) ?></td>
            <td><?= htmlspecialchars($t['kleedlokaal'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- schermpje: team toevoegen -->
<div class="modal" id="toevoegModal">
    <div class="modal-inhoud">
        <h2>Team toevoegen</h2>

        <form method="post">
            <input type="hidden" name="actie" value="toevoegen">

            <label>Editie *</label>
            <select name="editie_id" required>
                <?php foreach ($edities as $e): ?>
                    <option value="<?= $e['editie_id'] ?>"><?= htmlspecialchars($e['naam']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Naam *</label>
            <input type="text" name="naam" required>

            <label>Doel aantal deelnemers *</label>
            <input type="number" name="doel_aantal_deelnemers" min="1" value="16" required>

            <label>Kleedlokaal</label>
            <input type="text" name="kleedlokaal">

            <button type="submit">Opslaan</button>
            <button type="button" class="sluit">Annuleren</button>
        </form>
    </div>
</div>

<!-- schermpje: team aanpassen of verwijderen -->
<div class="modal" id="bewerkModal">
    <div class="modal-inhoud">
        <h2>Team aanpassen</h2>

        <form method="post">
            <input type="hidden" name="basisteam_id" id="bewerkId">

            <label>Editie *</label>
            <select name="editie_id" id="bewerkEditie" required>
                <?php foreach ($edities as $e): ?>
                    <option value="<?= $e['editie_id'] ?>"><?= htmlspecialchars($e['naam']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Naam *</label>
            <input type="text" name="naam" id="bewerkNaam" required>

            <label>Doel aantal deelnemers *</label>
            <input type="number" name="doel_aantal_deelnemers" id="bewerkDoel" min="1" required>

            <label>Kleedlokaal</label>
            <input type="text" name="kleedlokaal" id="bewerkKleedlokaal">

            <button type="submit" name="actie" value="aanpassen">Opslaan</button>
            <button type="submit" name="actie" value="verwijderen" formnovalidate
                    onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">Verwijderen</button>
            <button type="button" class="sluit">Annuleren</button>
        </form>
    </div>
</div>

<script>
    // knop: nieuw team toevoegen
    document.getElementById('openToevoegen').onclick = function () {
        document.getElementById('toevoegModal').classList.add('open');
    };

    // klik op een rij: gegevens invullen en schermpje openen
    var rijen = document.querySelectorAll('.teamrij');
    for (var i = 0; i < rijen.length; i++) {
        rijen[i].onclick = function () {
            document.getElementById('bewerkId').value = this.dataset.id;
            document.getElementById('bewerkEditie').value = this.dataset.editie;
            document.getElementById('bewerkNaam').value = this.dataset.naam;
            document.getElementById('bewerkDoel').value = this.dataset.doel;
            document.getElementById('bewerkKleedlokaal').value = this.dataset.kleedlokaal;
            document.getElementById('bewerkModal').classList.add('open');
        };
    }

    // annuleren-knoppen sluiten het schermpje
    var sluitKnoppen = document.querySelectorAll('.sluit');
    for (var j = 0; j < sluitKnoppen.length; j++) {
        sluitKnoppen[j].onclick = function () {
            this.closest('.modal').classList.remove('open');
        };
    }
</script>

</body>
</html>