<?php
require_once __DIR__ . '/../Includes/Beveiliging.php';

$terug = $_POST['terug'] ?? '';
$toegestanePaginas = [
    '/voetbaldagen/Index.php',
    '/voetbaldagen/Pages/Deelnemers.php',
    '/voetbaldagen/Pages/Kaderleden.php',
    '/voetbaldagen/Pages/Teams.php',
    '/voetbaldagen/Pages/Wedstrijden.php',
    '/voetbaldagen/Pages/Broodjes.php',
];
$pad = is_string($terug) ? parse_url($terug, PHP_URL_PATH) : false;
$query = is_string($terug) ? parse_url($terug, PHP_URL_QUERY) : false;

if (!in_array($pad, $toegestanePaginas, true) || preg_match('/[\r\n\\\\]/', (string) $terug)) {
    $terug = '/voetbaldagen/Index.php';
} else {
    $terug = $pad . ($query ? '?' . $query : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    controleerCsrf();
    $actie = tekstVeld($_POST, 'actie');

    if ($actie === 'uitloggen' && !empty($_SESSION['account_id'])) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }

        session_destroy();
    } elseif ($actie === 'inloggen' || $actie === 'registreren') {
        require_once __DIR__ . '/../Classes/Accounts.php';
        $account = new Account();
        $email = tekstVeld($_POST, 'email');
        $wachtwoord = $_POST['wachtwoord'] ?? '';
        $wachtwoord = is_string($wachtwoord) ? $wachtwoord : '';

        if ($actie === 'registreren') {
            $wachtwoord2 = $_POST['wachtwoord2'] ?? '';
            $wachtwoord2 = is_string($wachtwoord2) ? $wachtwoord2 : '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['account_fout'] = 'Vul een geldig e-mailadres in.';
            } elseif (strlen($wachtwoord) < 8 || strlen($wachtwoord) > 4096) {
                $_SESSION['account_fout'] = 'Je wachtwoord moet minimaal 8 tekens zijn.';
            } elseif ($wachtwoord !== $wachtwoord2) {
                $_SESSION['account_fout'] = 'De wachtwoorden zijn niet hetzelfde.';
            } elseif ($account->emailBestaat($email)) {
                $_SESSION['account_fout'] = 'Er bestaat al een account met dit e-mailadres.';
            } else {
                try {
                    $accountId = $account->registreren($email, $wachtwoord);
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                    $accountId = false;
                }

                if ($accountId === false) {
                    $_SESSION['account_fout'] = 'Dit e-mailadres is niet gekoppeld aan een beschikbaar kaderlid. Neem contact op met de beheerder.';
                } else {
                    $gebruiker = $account->inloggen($email, $wachtwoord);
                    startSessie($gebruiker);
                }
            }

            if (isset($_SESSION['account_fout'])) {
                $_SESSION['account_modal'] = 'registreren';
            }
        } else {
            $gebruiker = $account->inloggen($email, $wachtwoord);

            if ($gebruiker) {
                startSessie($gebruiker);
            } else {
                $_SESSION['account_fout'] = 'E-mailadres of wachtwoord klopt niet.';
                $_SESSION['account_modal'] = 'inloggen';
            }
        }
    }
}

header('Location: ' . $terug);
exit;

function startSessie(array $gebruiker): void
{
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['account_id'] = $gebruiker['account_id'];
    $_SESSION['account_email'] = $gebruiker['email'];
    $_SESSION['account_rol'] = $gebruiker['rol'];
}