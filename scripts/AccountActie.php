<?php
session_start();
require_once '../Classes/Account.php';

// verbinding met de database (aanpassen naar jullie eigen verbinding)
$conn = new PDO('mysql:host=localhost;dbname=voetbaldagen_horst;charset=utf8mb4', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$account = new Account($conn);

// terug naar de pagina waar je vandaan kwam
$terug = $_POST['terug'] ?? '/voetbaldagen/Index.php';
if ($terug[0] != '/' || substr($terug, 0, 2) == '//') {
    $terug = '/voetbaldagen/Index.php';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actie = $_POST['actie'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if ($actie == 'registreren') {
        $wachtwoord2 = $_POST['wachtwoord2'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['account_fout'] = 'Vul een geldig e-mailadres in.';
        } elseif (strlen($wachtwoord) < 8) {
            $_SESSION['account_fout'] = 'Je wachtwoord moet minimaal 8 tekens zijn.';
        } elseif ($wachtwoord != $wachtwoord2) {
            $_SESSION['account_fout'] = 'De wachtwoorden zijn niet hetzelfde.';
        } elseif ($account->emailBestaat($email)) {
            $_SESSION['account_fout'] = 'Er bestaat al een account met dit e-mailadres.';
        } else {
            $account->registreren($email, $wachtwoord);

            // meteen inloggen na het registreren
            $gebruiker = $account->inloggen($email, $wachtwoord);
            session_regenerate_id(true);
            $_SESSION['account_id'] = $gebruiker['account_id'];
            $_SESSION['account_email'] = $gebruiker['email'];
            $_SESSION['account_rol'] = $gebruiker['rol'];
        }

        if (isset($_SESSION['account_fout'])) {
            $_SESSION['account_modal'] = 'registreren';
        }
    }

    if ($actie == 'inloggen') {
        $gebruiker = $account->inloggen($email, $wachtwoord);

        if ($gebruiker) {
            session_regenerate_id(true);
            $_SESSION['account_id'] = $gebruiker['account_id'];
            $_SESSION['account_email'] = $gebruiker['email'];
            $_SESSION['account_rol'] = $gebruiker['rol'];
        } else {
            $_SESSION['account_fout'] = 'E-mailadres of wachtwoord klopt niet.';
            $_SESSION['account_modal'] = 'inloggen';
        }
    }

    if ($actie == 'uitloggen') {
        unset($_SESSION['account_id'], $_SESSION['account_email'], $_SESSION['account_rol']);
        session_regenerate_id(true);
    }
}

header('Location: ' . $terug);
exit;