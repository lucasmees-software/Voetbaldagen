<?php
// Verbinding
require_once '../Database/Connection.php';
// $pdo = $db;

$csvMelding = '';

// CSV
if (isset($_POST['csv_versturen'])) {
    $bestand = $_FILES['csvbestand']['tmp_name'];

    if ($bestand == '') {
        $csvMelding = 'Kies eerst een bestand.';
    } else {
        // Editie
        $editie = $pdo->query("SELECT editie_id FROM editie ORDER BY begindatum DESC LIMIT 1")->fetch();
        $editie_id = $editie['editie_id'];

        $handle = fopen($bestand, 'r');

        // Kopregel
        $kop = fgetcsv($handle, 0, ';', '"', '\\');
        $kop[0] = str_replace("\xEF\xBB\xBF", '', $kop[0]);
        $kop = array_map('strtolower', array_map('trim', $kop));

        // Rijen
        while (($rij = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            if (count($rij) != count($kop)) {
                continue;
            }

            $data = array_combine($kop, $rij);

            $roepnaam      = trim($data['roepnaam']);
            $voorvoegsel   = ($data['voorvoegsel'] ?? '') ?: null;
            $achternaam    = trim($data['achternaam']);
            $geboortedatum = date('Y-m-d', strtotime($data['geboortedatum']));
            $woonplaats    = $data['woonplaats'] ?? null;
            $telefoon      = $data['telefoon'] ?? null;
            $telefoon2     = ($data['telefoon2'] ?? '') ?: null;
            $adres         = $data['adres'] ?? null;
            $postcode      = $data['postcode'] ?? null;
            $vereniging    = ($data['vereniging'] ?? '') ?: null;
            $kledingmaat   = $data['kledingmaat'] ?? null;

            // Geslacht
            $geslacht = strtolower($data['geslacht'] ?? '');
            if ($geslacht == 'm') {
                $geslacht = 'man';
            }
            if ($geslacht == 'v') {
                $geslacht = 'vrouw';
            }

            // Keeper
            $keeper = 0;
            if (strtolower($data['keeper'] ?? '') == 'ja') {
                $keeper = 1;
            }

            // Leeg
            if ($roepnaam == '' || $achternaam == '') {
                continue;
            }

            // Bestaat?
            $stmt = $pdo->prepare("SELECT deelnemer_id FROM deelnemer
                                   WHERE roepnaam = ? AND achternaam = ? AND geboortedatum = ?");
            $stmt->execute([$roepnaam, $achternaam, $geboortedatum]);
            $bestaand = $stmt->fetch();

            if ($bestaand) {
                $deelnemer_id = $bestaand['deelnemer_id'];
            } else {
                // Deelnemer
                $stmt = $pdo->prepare("INSERT INTO deelnemer
                    (roepnaam, voorvoegsel, achternaam, geboortedatum, woonplaats, telefoon, geslacht, adres, postcode, telefoon2)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$roepnaam, $voorvoegsel, $achternaam, $geboortedatum, $woonplaats,
                    $telefoon, $geslacht, $adres, $postcode, $telefoon2]);
                $deelnemer_id = $pdo->lastInsertId();
            }

            // Inschrijving
            try {
                $stmt = $pdo->prepare("INSERT INTO inschrijving (editie_id, deelnemer_id, vereniging, kledingmaat, keeper_voorkeur)
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$editie_id, $deelnemer_id, $vereniging, $kledingmaat, $keeper]);
            } catch (PDOException $e) {
                // Dubbel
            }
        }

        fclose($handle);
        $csvMelding = 'CSV is verwerkt.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>Inschrijven</title>

    <!-- CSV -->
    <style>
        main form.csv-form {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            width: 100% !important;
            max-width: 640px !important;
            margin: 20px 0 0 0 !important;
            padding: 10px 14px !important;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        main form.csv-form label {
            margin: 0 !important;
            font-size: 13px;
        }

        main form.csv-form input[type="file"] {
            width: auto !important;
            flex: 1;
            margin: 0 !important;
            padding: 6px !important;
            font-size: 13px;
        }

        main form.csv-form input[type="submit"] {
            width: auto !important;
            margin: 0 !important;
            padding: 8px 16px !important;
            font-size: 11px !important;
        }
    </style>
</head>

<body>
    <?php include('../Includes/Navigatie.php') ?>

    <main>
        <h1>Inschrijvingen</h1>

        <?php
        // Meldingen
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'ok') {
                echo "<p style='color:green;'>Bedankt! Je inschrijving is ontvangen.</p>";
            } elseif ($_GET['status'] == 'dubbel') {
                echo "<p style='color:red;'>Deze deelnemer is al ingeschreven.</p>";
            } else {
                echo "<p style='color:red;'>Er ging iets mis. Vul alle verplichte velden in.</p>";
            }
        }

        if ($csvMelding != '') {
            echo "<p style='color:green;'>" . htmlspecialchars($csvMelding) . "</p>";
        }
        ?>

        <p style="max-width: 640px; margin-left: 0;">Let op: De inschrijving gaat vanaf 1 februari 2027 om stipt 00.00 uur 's nachts open. Mocht je je voor dit tijdstip hebben ingeschreven, dan wordt dit niet in behandeling genomen.

            Via dit formulier kunt u uw zoon, dochter, of een andere persoon aanmelden voor de Jeugdvoetbaldagen 2027. Als u onderaan op 'Verzenden' klikt, ontvangen wij het inschrijfformulier. We benadrukken graag dat deelname na dit inschrijfformulier nog niet definitief is, aangezien we zoals ieder jaar afhankelijk zijn van onze vrijwilligers om te kunnen bepalen hoeveel deelnemers we daadwerkelijk kunnen plaatsen. We streven ernaar daar zo snel mogelijk duidelijkheid over te geven.</p>

        <form class="csv-form" action="Inschrijven.php" method="post" enctype="multipart/form-data">
            <label for="csvbestand">CSV</label>
            <input type="file" id="csvbestand" name="csvbestand" accept=".csv" required>
            <input type="submit" name="csv_versturen" value="Importeren">
        </form>

        <form action="VerwerkInschrijving.php" method="post"
            style="width: 100%; max-width: 640px; margin: 24px auto 0 0;">

            <label for="roepnaam">1. Roepnaam</label>
            <input type="text" id="roepnaam" name="roepnaam" required>

            <label for="voorvoegsel">Voorvoegsel (bijv. van, de, van den)</label>
            <input type="text" id="voorvoegsel" name="voorvoegsel">

            <label for="achternaam">2. Achternaam</label>
            <input type="text" id="achternaam" name="achternaam" required>

            <label for="geslacht">3. Geslacht</label>
            <select name="geslacht" id="geslacht">
                <option value="man">Man</option>
                <option value="vrouw">Vrouw</option>
            </select>

            <label for="adres">Adres straatnaam + huisnummer</label>
            <input type="text" id="adres" name="adres" required>

            <label for="postcode">Postcode</label>
            <input type="text" id="postcode" name="postcode" required>

            <label for="woonplaats">Woonplaats</label>
            <select name="woonplaats" id="woonplaats">
                <option value="America">America</option>
                <option value="Broekhuizen">Broekhuizen</option>
                <option value="Castenray">Castenray</option>
                <option value="Grubbenvorst">Grubbenvorst</option>
                <option value="Hegelsom">Hegelsom</option>
                <option value="Helmond">Helmond</option>
                <option value="Horst">Horst</option>
                <option value="Leunen">Leunen</option>
                <option value="Lottum">Lottum</option>
                <option value="Maashees">Maashees</option>
                <option value="Melderslo">Melderslo</option>
                <option value="Merselo">Merselo</option>
                <option value="Meterik">Meterik</option>
                <option value="Oostrum">Oostrum</option>
                <option value="Venlo">Venlo</option>
                <option value="Venray">Venray</option>
                <option value="Vredepeel">Vredepeel</option>
                <option value="Ysselsteyn">Ysselsteyn</option>
                <option value="Anders">Anders, vul hieronder in</option>
            </select>

            <label for="woonplaats_anders">Andere woonplaats (alleen bij "Anders")</label>
            <input type="text" id="woonplaats_anders" name="woonplaats_anders">

            <div class="tel-groep">
                <label for="telefoon">Contactpersoon 1 (telefoon)</label>
                <input type="tel" id="telefoon" name="telefoon" placeholder="0612345678"
                    pattern="[0-9]{10}" title="Vul 10 cijfers in." required>
            </div>

            <div class="tel-groep2">
                <label for="telefoon2">Contactpersoon 2 (niet verplicht)</label>
                <input type="tel" id="telefoon2" name="telefoon2" placeholder="0612345678"
                    pattern="[0-9]{10}" title="Vul 10 cijfers in.">
            </div>

            <label for="geboortedatum">Geboortedatum</label>
            <input type="date" id="geboortedatum" name="geboortedatum"
                min="1900-01-01" max="2026-11-23" required>

            <label for="vereniging">Vereniging (niet verplicht)</label>
            <input type="text" id="vereniging" name="vereniging">

            <label for="kledingmaat">Kledingmaat</label>
            <select name="kledingmaat" id="kledingmaat">
                <option value="116">116</option>
                <option value="128">128</option>
                <option value="140">140</option>
                <option value="152">152</option>
                <option value="164">164</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
            </select>

            <label>
                <input type="checkbox" name="keeper_voorkeur" value="1">
                Ik wil graag keeper zijn
            </label>

            <input type="submit" value="Verzenden">
        </form>
    </main>

</body>
</html>