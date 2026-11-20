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

// Welk moment is gekozen? Komt uit de url (?moment=...), anders het eerste
$moment = $_GET['moment'] ?? 'maandag_dinsdag';
if (!isset($momenten[$moment])) {
    $moment = 'maandag_dinsdag';
}

// Haal alle deelnemers op uit de database: id => naam
$deelnemers = [];
$resultaat = $pdo->query("SELECT deelnemer_id, roepnaam, voorvoegsel, achternaam FROM deelnemer ORDER BY roepnaam");
foreach ($resultaat as $d) {
    // array_filter laat een leeg voorvoegsel (NULL) weg
    $deelnemers[$d['deelnemer_id']] = implode(' ', array_filter([$d['roepnaam'], $d['voorvoegsel'], $d['achternaam']]));
}

$map = __DIR__ . '/../data';
$bestand = $map . '/bestellingen.csv';
$melding = '';

// Is het formulier verstuurd?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nieuw = [];
    $fout = '';

    // Loop langs alle deelnemers
    foreach ($deelnemers as $id => $naam) {
        $b1 = $_POST['broodje1'][$id] ?? '';

        // Geen broodje gekozen = deze deelnemer bestelt niets
        if ($b1 == '') {
            continue;
        }

        $b2 = $_POST['broodje2'][$id] ?? '';
        $snack = $_POST['snack'][$id] ?? 'geen';
        $krentenbol = isset($_POST['krentenbol'][$id]) ? 1 : 0;

        if (!in_array($b1, $broodjes)) {
            $fout = 'Ongeldig broodje bij ' . $naam . '.';
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
            // Andere dagen: broodje 2 verplicht, geen snack
            if (!in_array($b2, $broodjes)) {
                $fout = 'Kies een tweede broodje bij ' . $naam . '.';
                continue;
            }
            $snack = 'geen';
        }

        $nieuw[] = [$id, $naam, $moment, $b1, $b2, $krentenbol, $snack];
    }

    // Alleen opslaan als alles klopt
    if ($fout == '') {
        if (!is_dir($map)) {
            mkdir($map);
        }

        // Bewaar de bestellingen van de andere momenten
        $bewaren = [];
        if (file_exists($bestand)) {
            $f = fopen($bestand, 'r');
            fgetcsv($f); // kopregel overslaan
            while (($rij = fgetcsv($f)) !== false) {
                if ($rij[2] != $moment) {
                    $bewaren[] = $rij;
                }
            }
            fclose($f);
        }

        // Schrijf alles weg: kopregel, de andere momenten, en dit moment
        $f = fopen($bestand, 'w');
        fputcsv($f, ['deelnemer_id', 'naam', 'moment', 'broodje1', 'broodje2', 'krentenbol', 'snack']);
        foreach ($bewaren as $rij) {
            fputcsv($f, $rij);
        }
        foreach ($nieuw as $rij) {
            fputcsv($f, $rij);
        }
        fclose($f);

        $melding = 'Opgeslagen voor ' . $momenten[$moment] . '!';
    } else {
        $melding = $fout;
    }
}

// Lees wat er al is opgeslagen voor dit moment (zodat het ingevuld staat in de tabel)
$opgeslagen = [];
if (file_exists($bestand)) {
    $f = fopen($bestand, 'r');
    fgetcsv($f); // kopregel overslaan
    while (($rij = fgetcsv($f)) !== false) {
        if ($rij[2] == $moment) {
            $opgeslagen[$rij[0]] = [
                'broodje1' => $rij[3],
                'broodje2' => $rij[4],
                'krentenbol' => $rij[5],
                'snack' => $rij[6],
            ];
        }
    }
    fclose($f);
}
?>

<h1>Broodjes bestellen</h1>

<!-- Knoppen om het moment te kiezen -->
<p>
    <?php foreach ($momenten as $sleutel => $naam) { ?>
        <a href="?moment=<?= $sleutel ?>" class="btn <?= $sleutel == $moment ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= $naam ?>
        </a>
    <?php } ?>
</p>

<?php if ($melding != '') { ?>
    <!-- htmlspecialchars zorgt dat er geen gekke code in de pagina kan komen -->
    <p><strong><?= htmlspecialchars($melding) ?></strong></p>
<?php } ?>

<form method="post" action="?moment=<?= $moment ?>">
    <table class="table table-bordered table-striped align-middle" border="1" cellpadding="6">
        <tr>
            <th>Deelnemer</th>
            <?php if ($moment != 'vrijdag') { ?>
                <th>Broodje 1</th>
                <th>Broodje 2</th>
                <th>Krentenbol</th>
            <?php } else { ?>
                <th>Broodje</th>
                <th>Snack</th>
            <?php } ?>
        </tr>

        <?php foreach ($deelnemers as $id => $naam) {
            // Wat had deze deelnemer al gekozen?
            $o = $opgeslagen[$id] ?? ['broodje1' => '', 'broodje2' => '', 'krentenbol' => 0, 'snack' => 'kroket'];
        ?>
        <tr>
            <td><?= htmlspecialchars($naam) ?></td>

            <td>
                <select name="broodje1[<?= $id ?>]" class="form-select">
                    <option value="">-</option>
                    <?php foreach ($broodjes as $b) { ?>
                        <option value="<?= $b ?>" <?= $o['broodje1'] == $b ? 'selected' : '' ?>><?= ucfirst($b) ?></option>
                    <?php } ?>
                </select>
            </td>

            <?php if ($moment != 'vrijdag') { ?>
                <td>
                    <select name="broodje2[<?= $id ?>]" class="form-select">
                        <option value="">-</option>
                        <?php foreach ($broodjes as $b) { ?>
                            <option value="<?= $b ?>" <?= $o['broodje2'] == $b ? 'selected' : '' ?>><?= ucfirst($b) ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <input type="checkbox" name="krentenbol[<?= $id ?>]" <?= $o['krentenbol'] == 1 ? 'checked' : '' ?>>
                </td>
            <?php } else { ?>
                <td>
                    <select name="snack[<?= $id ?>]" class="form-select">
                        <option value="kroket" <?= $o['snack'] == 'kroket' ? 'selected' : '' ?>>Kroket</option>
                        <option value="frikandel" <?= $o['snack'] == 'frikandel' ? 'selected' : '' ?>>Frikandel</option>
                    </select>
                </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </table>

    <button type="submit" class="btn btn-primary">Opslaan</button>
</form>