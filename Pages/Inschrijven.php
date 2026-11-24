<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>Inschrijven</title>
</head>

<body>
    <?php include('../Includes/Navigatie.php') ?>

    <main>
        <h1>Inschrijvingen</h1>

        <?php
        // Meldingen na het versturen
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'ok') {
                echo "<p style='color:green;'>Bedankt! Je inschrijving is ontvangen.</p>";
            } elseif ($_GET['status'] == 'dubbel') {
                echo "<p style='color:red;'>Deze deelnemer is al ingeschreven.</p>";
            } else {
                echo "<p style='color:red;'>Er ging iets mis. Vul alle verplichte velden in.</p>";
            }
        }
        ?>

        <p style="max-width: 600px;">Let op: De inschrijving gaat vanaf 1 februari 2027 om stipt 00.00 uur 's nachts open. Mocht je je voor dit tijdstip hebben ingeschreven, dan wordt dit niet in behandeling genomen.

            Via dit formulier kunt u uw zoon, dochter, of een andere persoon aanmelden voor de Jeugdvoetbaldagen 2027. Als u onderaan op 'Verzenden' klikt, ontvangen wij het inschrijfformulier. We benadrukken graag dat deelname na dit inschrijfformulier nog niet definitief is, aangezien we zoals ieder jaar afhankelijk zijn van onze vrijwilligers om te kunnen bepalen hoeveel deelnemers we daadwerkelijk kunnen plaatsen. We streven ernaar daar zo snel mogelijk duidelijkheid over te geven.</p>

        <form action="VerwerkInschrijving.php" method="post">
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