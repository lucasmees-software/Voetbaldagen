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

// Haal alle deelnemers op uit de database id => naam
$deelnemers = [];
$resultaat = $pdo->query("SELECT deelnemer_id, roepnaam, voorvoegsel, achternaam FROM deelnemer ORDER BY roepnaam");
foreach ($resultaat as $d) {
    // array_filter laat een leeg voorvoegsel (NULL) weg
    $deelnemers[$d['deelnemer_id']] = implode(' ', array_filter([$d['roepnaam'], $d['voorvoegsel'], $d['achternaam']]));
}

$map = __DIR__ . '/../data';
$bestand = $map . '/bestellingen.csv';
$melding = '';

// form opgeslagen ?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nieuw = [];
    $fout = '';

    // Loop langs alle deelnemers en alle momenten
    foreach ($deelnemers as $id => $naam) {
        foreach ($momenten as $moment => $momentNaam) {

            $b1 = $_POST['broodje1'][$moment][$id] ?? '';

            // Geen broodje gekozen = niets besteld voor dit moment
            if ($b1 == '') {
                continue;
            }

            $b2 = $_POST['broodje2'][$moment][$id] ?? '';
            $snack = $_POST['snack'][$moment][$id] ?? 'geen';
            $krentenbol = isset($_POST['krentenbol'][$moment][$id]) ? 1 : 0;

            if (!in_array($b1, $broodjes)) {
                $fout = 'Ongeldig broodje bij ' . $naam . ' (' . $momentNaam . ').';
                continue;
            }

            if ($moment == 'vrijdag') {
                // Vrijdag: geen broodje 2 en geen krentenbol, wel een snack
                $b2 = '';
                $krentenbol = 0;
                if ($snack != 'kroket' && $snack != 'frikandel') {
                    $fout = 'Kies een snack bij ' . $naam . '.';
                    continue;
                }
            } else {
                // Andere momenten: broodje 2 verplicht, geen snack
                if (!in_array($b2, $broodjes)) {
                    $fout = 'Kies een tweede broodje bij ' . $naam . ' (' . $momentNaam . ').';
                    continue;
                }
                $snack = 'geen';
            }

            $nieuw[] = [$id, $naam, $moment, $b1, $b2, $krentenbol, $snack];
        }
    }

    // Alleen opslaan als alles klopt
    if ($fout == '') {
        if (!is_dir($map)) {
            mkdir($map);
        }

        // Schrijf alles weg: kopregel + alle bestellingen
        $f = fopen($bestand, 'w');
        fputcsv($f, ['deelnemer_id', 'naam', 'moment', 'broodje1', 'broodje2', 'krentenbol', 'snack']);
        foreach ($nieuw as $rij) {
            fputcsv($f, $rij);
        }
        fclose($f);

        $melding = 'Opgeslagen!';
    } else {
        $melding = $fout;
    }
}

// Lees wat er is opgeslagen (zodat het ingevuld staat in de tabel)
$opgeslagen = [];
if (file_exists($bestand)) {
    $f = fopen($bestand, 'r');
    fgetcsv($f); // kopregel overslaan
    while (($rij = fgetcsv($f)) !== false) {
        $opgeslagen[$rij[2]][$rij[0]] = [
            'broodje1' => $rij[3],
            'broodje2' => $rij[4],
            'krentenbol' => $rij[5],
            'snack' => $rij[6],
        ];
    }
    fclose($f);
}
?>

<?php include('../Includes/Navigatie.php') ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>broodje bestellen</title>
</head>

</html>

<h1>Broodjes bestellen</h1>

<form method="post">
    <table class="table table-bordered align-middle" border="1" cellpadding="6">
        <tr>
            <th>Deelnemer</th>
            <th>Moment</th>
            <th>Broodje 1</th>
            <th>Broodje 2</th>
            <th>Krentenbol</th>
            <th>Snack</th>
        </tr>

        <?php foreach ($deelnemers as $id => $naam) {
            $eerste = true; // om de naam maar 1 keer te tonen
            foreach ($momenten as $moment => $momentNaam) {
                // Wat had deze deelnemer al gekozen voor dit moment?
                $o = $opgeslagen[$moment][$id] ?? ['broodje1' => '', 'broodje2' => '', 'krentenbol' => 0, 'snack' => 'kroket'];
        ?>
                <tr>
                    <?php if ($eerste) { ?>
                        <!-- De naam staat over 3 rijen (een per moment) -->
                        <td rowspan="<?= count($momenten) ?>"><strong><?= htmlspecialchars($naam) ?></strong></td>
                        <?php $eerste = false; ?>
                    <?php } ?>

                    <!-- Kolom Moment -->
                    <td><?= $momentNaam ?></td>

                    <!-- Broodje 1 (bij alle momenten) -->
                    <td>
                        <select name="broodje1[<?= $moment ?>][<?= $id ?>]" class="form-select">
                            <option value="">-</option>
                            <?php foreach ($broodjes as $b) { ?>
                                <option value="<?= $b ?>" <?= $o['broodje1'] == $b ? 'selected' : '' ?>><?= ucfirst($b) ?></option>
                            <?php } ?>
                        </select>
                    </td>

                    <?php if ($moment != 'vrijdag') { ?>
                        <!-- Maandag en woensdag: broodje 2 en krentenbol, geen snack -->
                        <td>
                            <select name="broodje2[<?= $moment ?>][<?= $id ?>]" class="form-select">
                                <option value="">-</option>
                                <?php foreach ($broodjes as $b) { ?>
                                    <option value="<?= $b ?>" <?= $o['broodje2'] == $b ? 'selected' : '' ?>><?= ucfirst($b) ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" name="krentenbol[<?= $moment ?>][<?= $id ?>]" <?= $o['krentenbol'] == 1 ? 'checked' : '' ?>>
                        </td>
                        <td>-</td>
                    <?php } else { ?>
                        <!-- Vrijdag: geen broodje 2 en geen krentenbol, wel een snack -->
                        <td>-</td>
                        <td>-</td>
                        <td>
                            <select name="snack[<?= $moment ?>][<?= $id ?>]" class="form-select">
                                <option value="kroket" <?= $o['snack'] == 'kroket' ? 'selected' : '' ?>>Kroket</option>
                                <option value="frikandel" <?= $o['snack'] == 'frikandel' ? 'selected' : '' ?>>Frikandel</option>
                            </select>
                        </td>
                    <?php } ?>
                </tr>
        <?php
            }
        } ?>
    </table>
    <button type="submit" class="btn btn-primary">Opslaan</button>
</form>