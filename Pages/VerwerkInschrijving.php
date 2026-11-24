<?php
// Verbinding met de database ophalen
require_once '../Database/Connection.php';

// Let op: heet jouw verbinding in Connection.php anders dan $pdo (bijv. $db of $conn)?
// Pas dan alleen deze regel aan, bijvoorbeeld: $pdo = $db;
// $pdo = $db;

// Alleen doorgaan als het formulier is verstuurd
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Inschrijven.php");
    exit;
}

// Gegevens uit het formulier halen
$roepnaam      = trim($_POST['roepnaam']);
$voorvoegsel   = trim($_POST['voorvoegsel']);
$achternaam    = trim($_POST['achternaam']);
$geslacht      = $_POST['geslacht'];
$adres         = trim($_POST['adres']);
$postcode      = trim($_POST['postcode']);
$woonplaats    = $_POST['woonplaats'];
$telefoon      = trim($_POST['telefoon']);
$telefoon2     = trim($_POST['telefoon2']);
$geboortedatum = $_POST['geboortedatum'];
$vereniging    = trim($_POST['vereniging']);
$kledingmaat   = $_POST['kledingmaat'];
$keeper        = isset($_POST['keeper_voorkeur']) ? 1 : 0;

// Lege velden die niet verplicht zijn worden NULL
if ($voorvoegsel == '') { $voorvoegsel = null; }
if ($telefoon2 == '')   { $telefoon2 = null; }
if ($vereniging == '')  { $vereniging = null; }

// Als "Anders" is gekozen, gebruik de ingevulde woonplaats
if ($woonplaats == 'Anders') {
    $woonplaats = trim($_POST['woonplaats_anders']);
}

// Controleren of verplichte velden zijn ingevuld
if ($roepnaam == '' || $achternaam == '' || $adres == '' || $postcode == '' || $woonplaats == '' || $telefoon == '') {
    header("Location: Inschrijven.php?status=fout");
    exit;
}

// Nieuwste editie opzoeken
$editie = $pdo->query("SELECT editie_id FROM editie ORDER BY begindatum DESC LIMIT 1")->fetch();
if (!$editie) {
    header("Location: Inschrijven.php?status=fout");
    exit;
}
$editie_id = $editie['editie_id'];

try {
    // Transactie: of alles wordt opgeslagen, of niets
    $pdo->beginTransaction();

    // Bestaat deze deelnemer al? (zelfde naam + geboortedatum)
    $stmt = $pdo->prepare("SELECT deelnemer_id FROM deelnemer
                           WHERE roepnaam = ? AND achternaam = ? AND geboortedatum = ?");
    $stmt->execute([$roepnaam, $achternaam, $geboortedatum]);
    $bestaand = $stmt->fetch();

    if ($bestaand) {
        // Zelfde deelnemer van vorige jaren: id hergebruiken
        $deelnemer_id = $bestaand['deelnemer_id'];
    } else {
        // Nieuwe deelnemer opslaan
        $sql = "INSERT INTO deelnemer
                (roepnaam, voorvoegsel, achternaam, geboortedatum, woonplaats, telefoon, geslacht, adres, postcode, telefoon2)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $roepnaam, $voorvoegsel, $achternaam, $geboortedatum, $woonplaats,
            $telefoon, $geslacht, $adres, $postcode, $telefoon2
        ]);
        $deelnemer_id = $pdo->lastInsertId();
    }

    // Inschrijving opslaan
    $sql = "INSERT INTO inschrijving (editie_id, deelnemer_id, vereniging, kledingmaat, keeper_voorkeur)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$editie_id, $deelnemer_id, $vereniging, $kledingmaat, $keeper]);

    $pdo->commit();

    header("Location: Inschrijven.php?status=ok");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();

}