<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/voetbaldagen/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($waarde): string
{
    return htmlspecialchars((string) $waarde, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tekstVeld(array $bron, string $naam): string
{
    $waarde = $bron[$naam] ?? '';
    return is_string($waarde) ? trim($waarde) : '';
}

function positiefGetal($waarde)
{
    if (!is_string($waarde) && !is_int($waarde)) {
        return false;
    }

    return filter_var($waarde, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

function csrfVeld(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf_token']) . '">';
}

function controleerCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Ongeldige aanvraag. Vernieuw de pagina en probeer het opnieuw.');
    }
}

function vereistInloggen(): void
{
    if (empty($_SESSION['account_id'])) {
        $_SESSION['account_fout'] = 'Log in om deze wijziging uit te voeren.';
        $_SESSION['account_modal'] = 'inloggen';
        header('Location: /voetbaldagen/Index.php');
        exit;
    }
}
