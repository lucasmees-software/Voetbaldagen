<?php
// Verbinding
require_once '../Database/Connection.php';
// $pdo = $db;

// Resultaten
$melding = '';
$resultaten = [];
$aantalNieuw = 0;
$aantalBestaand = 0;
$aantalDubbel = 0;
$aantalFout = 0;
$testModus = false;

// Datum
function maakDatum($tekst)
{
    $tekst = trim($tekst);
    $formaten = ['Y-m-d', 'd-m-Y', 'j-n-Y', 'd/m/Y', 'j/n/Y'];
    foreach ($formaten as $formaat) {
        $datum = DateTime::createFromFormat($formaat, $tekst);
        if ($datum && $datum->format($formaat) == $tekst) {
            return $datum->format('Y-m-d');
        }
    }
    return null;
}

// Leeg
function leegIsNull($tekst)
{
    $tekst = trim($tekst);
    return $tekst === '' ? null : $tekst;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $testModus = isset($_POST['testmodus']);

    if (!isset($_FILES['csvbestand']) || $_FILES['csvbestand']['error'] !== UPLOAD_ERR_OK) {
        $melding = 'Upload mislukt. Kies een CSV-bestand.';
    } else {
        // Editie
        $editie = $pdo->query("SELECT editie_id FROM editie ORDER BY begindatum DESC LIMIT 1")->fetch();

        if (!$editie) {
            $melding = 'Er is nog geen editie aangemaakt in de database.';
        } else {
            $editie_id = $editie['editie_id'];

            $bestand = fopen($_FILES['csvbestand']['tmp_name'], 'r');

            // Scheidingsteken
            $eersteRegel = fgets($bestand);
            $scheiding = (substr_count($eersteRegel, ';') > substr_count($eersteRegel, ',')) ? ';' : ',';
            rewind($bestand);

            // Kopregel
            $kop = fgetcsv($bestand, 0, $scheiding, '"', '\\');

            // Opschonen
            $kop[0] = preg_replace('/^\xEF\xBB\xBF/', '', $kop[0]);
            foreach ($kop as $nummer => $naam) {
                $kop[$nummer] = strtolower(trim($naam));
            }

            // Verplicht
            $verplicht = ['roepnaam', 'achternaam', 'geboortedatum'];
            $ontbreekt = array_diff($verplicht, $kop);

            if (count($ontbreekt) > 0) {
                $melding = 'Deze kolommen ontbreken in je CSV: ' . implode(', ', $ontbreekt);
            } else {
                $regelNummer = 1;

                // Regels
                while (($rij = fgetcsv($bestand, 0, $scheiding, '"', '\\')) !== false) {
                    $regelNummer++;

                    // Leeg
                    if (count($rij) == 1 && trim($rij[0]) == '') {
                        continue;
                    }

                    // Kolommen
                    if (count($rij) != count($kop)) {
                        $resultaten[] = [$regelNummer, '-', 'Fout: aantal kolommen klopt niet', 'rood'];
                        $aantalFout++;
                        continue;
                    }

                    // Koppelen
                    $data = array_combine($kop, $rij);

                    $roepnaam      = trim($data['roepnaam']);
                    $achternaam    = trim($data['achternaam']);
                    $geboortedatum = maakDatum($data['geboortedatum']);
                    $naam          = $roepnaam . ' ' . $achternaam;

                    // Controle
                    if ($roepnaam == '' || $achternaam == '' || $geboortedatum === null) {
                        $resultaten[] = [$regelNummer, $naam, 'Fout: naam of geboortedatum ontbreekt of is ongeldig', 'rood'];
                        $aantalFout++;
                        continue;
                    }

                    // Optioneel
                    $voorvoegsel = leegIsNull($data['voorvoegsel'] ?? '');
                    $woonplaats  = leegIsNull($data['woonplaats'] ?? '');
                    $telefoon    = leegIsNull($data['telefoon'] ?? '');
                    $telefoon2   = leegIsNull($data['telefoon2'] ?? '');
                    $adres       = leegIsNull($data['adres'] ?? '');
                    $postcode    = leegIsNull($data['postcode'] ?? '');
                    $vereniging  = leegIsNull($data['vereniging'] ?? '');
                    $kledingmaat = leegIsNull($data['kledingmaat'] ?? '');

                    // Geslacht
                    $geslacht = strtolower(trim($data['geslacht'] ?? ''));
                    if ($geslacht == 'm' || $geslacht == 'man' || $geslacht == 'jongen') {
                        $geslacht = 'man';
                    } elseif ($geslacht == 'v' || $geslacht == 'vrouw' || $geslacht == 'meisje') {
                        $geslacht = 'vrouw';
                    } else {
                        $geslacht = null;
                    }

                    // Keeper
                    $keeperTekst = strtolower(trim($data['keeper'] ?? ''));
                    $keeper = in_array($keeperTekst, ['ja', '1', 'x', 'true', 'yes']) ? 1 : 0;

                    try {
                        // Transactie
                        $pdo->beginTransaction();

                        // Bestaat al?
                        $stmt = $pdo->prepare("SELECT deelnemer_id FROM deelnemer
                                               WHERE roepnaam = ? AND achternaam = ? AND geboortedatum = ?");
                        $stmt->execute([$roepnaam, $achternaam, $geboortedatum]);
                        $bestaand = $stmt->fetch();

                        if ($bestaand) {
                            $deelnemer_id = $bestaand['deelnemer_id'];
                            $soort = 'bestaand';
                        } else {
                            // Nieuw
                            $sql = "INSERT INTO deelnemer
                                    (roepnaam, voorvoegsel, achternaam, geboortedatum, woonplaats, telefoon, geslacht, adres, postcode, telefoon2)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                $roepnaam, $voorvoegsel, $achternaam, $geboortedatum, $woonplaats,
                                $telefoon, $geslacht, $adres, $postcode, $telefoon2
                            ]);
                            $deelnemer_id = $pdo->lastInsertId();
                            $soort = 'nieuw';
                        }

                        // Inschrijving
                        $sql = "INSERT INTO inschrijving (editie_id, deelnemer_id, vereniging, kledingmaat, keeper_voorkeur)
                                VALUES (?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$editie_id, $deelnemer_id, $vereniging, $kledingmaat, $keeper]);

                        // Test
                        if ($testModus) {
                            $pdo->rollBack();
                        } else {
                            $pdo->commit();
                        }

                        if ($soort == 'nieuw') {
                            $resultaten[] = [$regelNummer, $naam, 'Nieuwe deelnemer + inschrijving', 'groen'];
                            $aantalNieuw++;
                        } else {
                            $resultaten[] = [$regelNummer, $naam, 'Deelnemer bestond al, inschrijving toegevoegd', 'blauw'];
                            $aantalBestaand++;
                        }

                    } catch (PDOException $e) {
                        $pdo->rollBack();

                        // Dubbel
                        if ($e->getCode() == 23000) {
                            $resultaten[] = [$regelNummer, $naam, 'Overgeslagen: al ingeschreven voor deze editie', 'oranje'];
                            $aantalDubbel++;
                        } else {
                            $resultaten[] = [$regelNummer, $naam, 'Fout in de database', 'rood'];
                            $aantalFout++;
                        }
                    }
                }
            }

            fclose($bestand);
        }
    }
}

// Kleuren
$kleuren = [
    'groen'  => '#2e7d32',
    'blauw'  => '#1b4d5f',
    'oranje' => '#c77700',
    'rood'   => '#b3222e',
];
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>CSV importeren</title>
</head>

<body>
    <?php include('../Includes/Navigatie.php') ?>

    <main>
        <h1>CSV importeren</h1>

        <p style="max-width: 640px;">
            Upload hier het CSV-bestand met inschrijvingen. Zet het vinkje bij "Alleen testen" om eerst te zien
            wat er gebeurt, zonder dat er iets wordt opgeslagen.
        </p>

        <form action="ImportCsv.php" method="post" enctype="multipart/form-data">
            <label for="csvbestand">CSV-bestand</label>
            <input type="file" id="csvbestand" name="csvbestand" accept=".csv" required>

            <label>
                <input type="checkbox" name="testmodus" value="1" checked>
                Alleen testen (niets opslaan)
            </label>

            <input type="submit" value="Importeren">
        </form>

        <?php if ($melding != '') { ?>
            <p style="color:#b3222e; font-weight:bold;"><?= htmlspecialchars($melding) ?></p>
        <?php } ?>

        <?php if (count($resultaten) > 0) { ?>
            <h2>Resultaat <?= $testModus ? '(test, niets opgeslagen)' : '' ?></h2>

            <p>
                <strong><?= $aantalNieuw ?></strong> nieuw,
                <strong><?= $aantalBestaand ?></strong> bestaand,
                <strong><?= $aantalDubbel ?></strong> al ingeschreven,
                <strong><?= $aantalFout ?></strong> fout
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Regel</th>
                        <th>Naam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultaten as $r) { ?>
                        <tr>
                            <td><?= $r[0] ?></td>
                            <td><?= htmlspecialchars($r[1]) ?></td>
                            <td style="color:<?= $kleuren[$r[3]] ?>; font-weight:bold;">
                                <?= htmlspecialchars($r[2]) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </main>

</body>
</html>