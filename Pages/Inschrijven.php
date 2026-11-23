<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/Stylesheet.css">
    <title>Inschrijven</title>
</head>

<body>
    <?php include('../Includes/Navigatie.php') ?>
    <header>
        <h1>Inschrijvingen</h1>
    </header>
    <form action="">
        <label for="roepnaam">1. roepnaam</label>
        <input type="text">
        <label for="achternaam">2. achternaam</label>
        <input type="text" name="achternaam">

        <label for="geslacht">3. geslacht</label>
        <select name="geslacht" id="">
            <option value="man">Man</option>
            <option value="vrouw">Vrouw</option>
        </select>

        <label for="adress">Adres straatnaam + huisnummer</label><input type="adress">
        <label for="postcode">Postcode</label><input type="text">
        <label for="woonplaats">Woonplaats</label>
        <select name="woonplaats" id="">
            <option value="">America</option>
            <option value="">Broekhuizen</option>
            <option value="">Castenray</option>
            <option value="">Grubbenvorst</option>
            <option value="">Hegelsom</option>
            <option value="">Helmond</option>
            <option value="">Horst</option>
            <option value="">leunen</option>
            <option value="">Lottum</option>
            <option value="">Maashees</option>
            <option value="">Melderslo</option>
            <option value="">Merselo</option>
            <option value="">Meterik</option>
            <option value="">Oostrum</option>
            <option value="">Venlo</option>
            <option value="">Venray</option>
            <option value="">Vredepeel</option>
            <option value="">Ysselsteyn</option>
        </select>
        <option value="">Anders, vul hier onder</option>
        <textarea name="" id=""></textarea>
        <div class="tel-groep">
            <label for="tel1">contactpersoon 1</label>
            <!-- Landcode kiezen -->
            <select id="landcode" name="landcode">
                <option value="+31" selected>🇳🇱 +31</option>
                <option value="+32">🇧🇪 +32</option>
                <option value="+49">🇩🇪 +49</option>
            </select>

            <input
                type="tel"
                id="telefoon"
                name="telefoon"
                placeholder="612345678"
                pattern="[0-9]{9}"
                title="Vul 9 cijfers in."
                required>
        </div>

        <div class="tel-groep2">
            <label for="tel2">Contactpersoon 2</label>
            <select id="landcode" name="landcode">
                <option value="+31" selected>🇳🇱 +31</option>
                <option value="+32">🇧🇪 +32</option>
                <option value="+49">🇩🇪 +49</option>
            </select>
        </div>

        <label for="geboortedatum">Geboortedatum</label>
        <input
            type="date"
            id="geboortedatum"
            name="geboortedatum"
            min="1900-01-01"
            max="2026-11-23"
            required>

        </input>

        <input type="submit" value="Submit">
    </form>
</body>

</html>