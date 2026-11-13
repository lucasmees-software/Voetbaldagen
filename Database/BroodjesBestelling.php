<?php
// Hier staan de toegestane keuzes
$momenten = ['maandag_dinsdag', 'woensdag_donderdag', 'vrijdag'];
$broodjes = ['hagelslag', 'kaas', 'vlees'];

$melding = '';

// Is het formulier verstuurd?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Haal de keuzes uit het formulier
    $moment = $_POST['moment'] ?? '';
    $broodje1 = $_POST['broodje1'] ?? '';
    $broodje2 = $_POST['broodje2'] ?? '';
    $snack = $_POST['snack'] ?? 'geen';
    $krentenbol = isset($_POST['krentenbol']) ? 1 : 0;

    // Wie bestelt er? Dit komt uit de login, niet uit het formulier
    $deelnemerId = $_SESSION['deelnemer_id'] ?? 1;

    // Controleer of alles klopt
    if (!in_array($moment, $momenten)) {
        $melding = 'Kies een geldig moment.';
    } elseif (!in_array($broodje1, $broodjes)) {
        $melding = 'Kies een geldig broodje 1.';
    } elseif ($moment != 'vrijdag' && !in_array($broodje2, $broodjes)) {
        $melding = 'Kies een geldig broodje 2.';
    } elseif ($moment == 'vrijdag' && $snack != 'kroket' && $snack != 'frikandel') {
        $melding = 'Kies op vrijdag een kroket of frikandel.';
    } else {

        // Op vrijdag geen broodje 2 en geen krentenbol
        if ($moment == 'vrijdag') {
            $broodje2 = '';
            $krentenbol = 0;
        } else {
            // Op de andere dagen geen snack
            $snack = 'geen';
        }

        // Maak de map "data" aan als die nog niet bestaat
        $map = __DIR__ . '/../data';
        if (!is_dir($map)) {
            mkdir($map);
        }

        // Open het bestand en zet er onderaan een regel bij
        $bestand = $map . '/bestellingen.csv';
        $isNieuw = !file_exists($bestand);
        $f = fopen($bestand, 'a');

        // Bij een nieuw bestand eerst de kopregel
        if ($isNieuw) {
            fputcsv($f, ['deelnemer', 'moment', 'broodje1', 'broodje2', 'krentenbol', 'snack']);
        }

        fputcsv($f, [$deelnemerId, $moment, $broodje1, $broodje2, $krentenbol, $snack]);
        fclose($f);

        $melding = 'Bestelling opgeslagen!';
    }
}
?>

<h1>Broodjes bestellen</h1>

<?php if ($melding != '') { ?>
    <!-- htmlspecialchars zorgt dat er geen gekke code in de pagina kan komen -->
    <p><?= htmlspecialchars($melding) ?></p>
<?php } ?>

<form method="post">
    <label>Voor welke dagen?</label><br>
    <select name="moment">