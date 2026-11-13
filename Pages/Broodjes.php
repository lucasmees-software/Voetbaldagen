<?php
// Login/beveiliging (laat weg als deze regel een fout geeft)
require_once __DIR__ . '/../Includes/Beveiliging.php';

// Database-verbinding, alleen om de deelnemers op te halen ($pdo)
require_once __DIR__ . '/../Database/Connection.php';

// De 3 bestelmomenten en de toegestane broodjes
$momenten = [
    'maandag_dinsdag' => 'Maandag + dinsdag',
    'woensdag_donderdag' => 'Woensdag + donderdag',
    'vrijdag' => 'Vrijdag',
];
$broodjes = ['hagelslag', 'kaas', 'vlees'];

// Haal alle deelnemers op uit de database en maak een lijstje: id => naam
$deelnemers = [];
$resultaat = $pdo->query("SELECT deelnemer_id, roepnaam, voorvoegsel, achternaam FROM deelnemer ORDER BY roepnaam");
foreach ($resultaat as $d) {
    // array_filter laat een leeg voorvoegsel (NULL) weg
    $deelnemers[$d['deelnemer_id']] = implode(' ', array_filter([$d['roepnaam'], $d['voorvoegsel'], $d['achternaam']]));
}

$melding = '';
$gekozen = $_POST['deelnemer_id'] ?? ''; // zodat de keuze blijft staan na het opslaan

// Is het formulier verstuurd?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nieuweBestellingen = [];
    $fout = '';

    // Bestaat deze deelnemer echt in de database?
    if (!isset($deelnemers[$gekozen])) {
        $fout = 'Kies een deelnemer.';
    }

    // Loop langs de 3 momenten en controleer elk moment
    foreach ($momenten as $moment => $naam) {
        $broodje1 = $_POST['broodje1'][$moment] ?? '';
        $broodje2 = $_POST['broodje2'][$moment] ?? '';
        $snack = $_POST['snack'][$moment] ?? 'geen';
        $krentenbol = isset($_POST['krentenbol'][$moment]) ? 1 : 0;

        if (!in_array($broodje1, $broodjes)) {
            $fout = 'Kies een geldig broodje bij ' . $naam . '.';
        } elseif ($moment != 'vrijdag' && !in_array($broodje2, $broodjes)) {
            $fout = 'Kies een geldig tweede broodje bij ' . $naam . '.';
        } elseif ($moment == 'vrijdag' && $snack != 'kroket' && $snack != 'frikandel') {
            $fout = 'Kies op vrijdag een kroket of frikandel.';
        }

        if ($moment == 'vrijdag') {
            // Vrijdag: geen broodje 2 en geen krentenbol
            $broodje2 = '';
            $krentenbol = 0;
        } else {
            // Andere dagen: geen snack
            $snack = 'geen';
        }

        $nieuweBestellingen[] = [$gekozen, $deelnemers[$gekozen] ?? '', $moment, $broodje1, $broodje2, $krentenbol, $snack];
    }

    // Alleen opslaan als alles klopt
    if ($fout == '') {
        $map = __DIR__ . '/../data';
        if (!is_dir($map)) {
            mkdir($map);
        }
        $bestand = $map . '/bestellingen.csv';

        // Lees de bestellingen van andere deelnemers (die willen we bewaren)
        $bewaren = [];
        if (file_exists($bestand)) {
            $f = fopen($bestand, 'r');
            fgetcsv($f); // kopregel overslaan
            while (($rij = fgetcsv($f)) !== false) {
                if ($rij[0] != $gekozen) {
                    $bewaren[] = $rij;
                }
            }
            fclose($f);
        }

        // Schrijf alles opnieuw weg: kopregel, de anderen, en de nieuwe bestelling
        $f = fopen($bestand, 'w');
        fputcsv($f, ['deelnemer_id', 'naam', 'moment', 'broodje1', 'broodje2', 'krentenbol', 'snack']);
        foreach ($bewaren as $rij) {
            fputcsv($f, $rij);
        }
        foreach ($nieuweBestellingen as $rij) {
            fputcsv($f, $rij);
        }
        fclose($f);

        $melding = 'Bestelling opgeslagen voor ' . $deelnemers[$gekozen] . '!';
    } else {
        $melding = $fout;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>broodjes</title>
</head>
<?php include('../Includes/Navigatie.php') ?>
<h1>Broodjes bestellen</h1>

<?php if ($melding != '') { ?>
    <!-- htmlspecialchars zorgt dat er geen gekke code in de pagina kan komen -->
    <p><?= htmlspecialchars($melding) ?></p>
<?php } ?>

<form method="post">

    <label>Deelnemer</label>
    <select name="deelnemer_id" class="form-select mb-3">
        <option value="">-- Kies een deelnemer --</option>
        <?php foreach ($deelnemers as $id => $naam) { ?>
            <option value="<?= $id ?>" <?= ($gekozen == $id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($naam) ?>
            </option>
        <?php } ?>
    </select>

    <table class="table table-bordered align-middle">
        <tr>
            <th>Moment</th>
            <th>Broodje 1</th>
            <th>Broodje 2</th>
            <th>Krentenbol</th>
            <th>Snack</th>
        </tr>

        <?php foreach ($momenten as $moment => $naam) { ?>
        <tr>
            <td><?= $naam ?></td>

            <td>
                <select name="broodje1[<?= $moment ?>]" class="form-select">
                    <?php foreach ($broodjes as $b) { ?>
                        <option value="<?= $b ?>"><?= ucfirst($b) ?></option>
                    <?php } ?>
                </select>
            </td>

            <?php if ($moment != 'vrijdag') { ?>
                <!-- Maandag en woensdag: broodje 2 en krentenbol, geen snack -->
                <td>
                    <select name="broodje2[<?= $moment ?>]" class="form-select">
                        <?php foreach ($broodjes as $b) { ?>
                            <option value="<?= $b ?>"><?= ucfirst($b) ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="checkbox" name="krentenbol[<?= $moment ?>]"></td>
                <td>-</td>
            <?php } else { ?>
                <!-- Vrijdag: geen broodje 2 en geen krentenbol, wel een snack -->
                <td>-</td>
                <td>-</td>
                <td>
                    <select name="snack[<?= $moment ?>]" class="form-select">
                        <option value="kroket">Kroket</option>
                        <option value="frikandel">Frikandel</option>
                    </select>
                </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </table>

    <button type="submit" class="btn btn-primary">Opslaan</button>
</form>

<h2 class="mt-5">Alle bestellingen</h2>

<?php
// Lees de bestellingen uit het CSV-bestand
$bestand = __DIR__ . '/../data/bestellingen.csv';
$bestellingen = [];

if (file_exists($bestand)) {
    $f = fopen($bestand, 'r');
    fgetcsv($f); // kopregel overslaan
    while (($rij = fgetcsv($f)) !== false) {
        $bestellingen[] = $rij;
    }
    fclose($f);
}
?>

<?php if (count($bestellingen) == 0) { ?>
    <p>Er zijn nog geen bestellingen.</p>
<?php } else { ?>
    <table class="table table-bordered table-striped" border="1" cellpadding="6">
        <tr>
            <th>Deelnemer</th>
            <th>Moment</th>
            <th>Broodje 1</th>
            <th>Broodje 2</th>
            <th>Krentenbol</th>
            <th>Snack</th>
        </tr>

        <?php foreach ($bestellingen as $rij) { ?>
        <tr>
            <!-- $rij: 0 = id, 1 = naam, 2 = moment, 3 = broodje1, 4 = broodje2, 5 = krentenbol, 6 = snack -->
            <td><?= htmlspecialchars($rij[1]) ?></td>
            <td><?= htmlspecialchars($momenten[$rij[2]] ?? $rij[2]) ?></td>
            <td><?= htmlspecialchars(ucfirst($rij[3])) ?></td>
            <td><?= $rij[4] != '' ? htmlspecialchars(ucfirst($rij[4])) : '-' ?></td>
            <td><?= $rij[5] == 1 ? 'Ja' : 'Nee' ?></td>
            <td><?= $rij[6] != 'geen' ? htmlspecialchars(ucfirst($rij[6])) : '-' ?></td>
        </tr>
        <?php } ?>
    </table>
<?php } ?>