<?php
require_once __DIR__ . '/Beveiliging.php';

// Pagina waar je nu op zit
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

// map van de website
$base = '/voetbaldagen/';

$links = [
    'Index.php'       => 'Home',
    'Deelnemers.php'  => 'Deelnemers',
    'Teams.php'       => 'Teams',
    'Wedstrijden.php' => 'Wedstrijden',
    'Kaderleden.php'  => 'Kaderleden',
];

// ingelogd of niet
$ingelogd = !empty($_SESSION['account_id']);

// foutmelding en welk schermpje open moet na een mislukte poging
$fout = $_SESSION['account_fout'] ?? '';
$openModal = $_SESSION['account_modal'] ?? '';
unset($_SESSION['account_fout'], $_SESSION['account_modal']);

$actieUrl = $base . 'Scripts/AccountActie.php';
?>
<header class="site-header">
    <nav class="navbar">

        <a href="<?= $base ?>Index.php" class="nav-logo">Voetbaldagen Horst</a>

        <div class="nav-links">
            <?php foreach ($links as $file => $label): ?>
                <a href="<?= $base ?><?= $file === 'Index.php' ? $file : 'Pages/' . $file ?>"
                   class="<?= $currentPage === $file ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- account -->
        <div class="nav-account">
            <?php if ($ingelogd): ?>
                <button type="button" onclick="accountOpen('accountModal')">
                    <?= htmlspecialchars($_SESSION['account_email']) ?>
                </button>
                <form class="nav-logout" method="post" action="<?= h($actieUrl) ?>">
                    <?php csrfVeld(); ?>
                    <input type="hidden" name="actie" value="uitloggen">
                    <input type="hidden" name="terug" value="<?= h($_SERVER['REQUEST_URI'] ?? $base . 'Index.php') ?>">
                    <button type="submit">Uitloggen</button>
                </form>
            <?php else: ?>
                <button type="button" onclick="accountOpen('loginModal')">Inloggen</button>
                <button type="button" onclick="accountOpen('registreerModal')">Registreren</button>
            <?php endif; ?>
        </div>

    </nav>
</header>

<?php if ($ingelogd): ?>

    <!-- schermpje: mijn account -->
    <div class="modal" id="accountModal" style="display:none">
        <div class="modal-inhoud">
            <h2>Mijn account</h2>

            <p><strong>E-mail:</strong> <?= h($_SESSION['account_email'] ?? '') ?></p>
            <p><strong>Rol:</strong> <?= h($_SESSION['account_rol'] ?? '') ?></p>

            <button type="button" onclick="accountSluit('accountModal')">Sluiten</button>
        </div>
    </div>

<?php else: ?>

    <!-- schermpje: inloggen -->
    <div class="modal" id="loginModal" style="display:<?= $openModal == 'inloggen' ? 'flex' : 'none' ?>">
        <div class="modal-inhoud">
            <h2>Inloggen</h2>

            <?php if ($fout != "" && $openModal == 'inloggen'): ?>
                <p class="fout"><?= h($fout) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= h($actieUrl) ?>">
                <?php csrfVeld(); ?>
                <input type="hidden" name="actie" value="inloggen">
                <input type="hidden" name="terug" value="<?= h($_SERVER['REQUEST_URI'] ?? $base . 'Index.php') ?>">

                <label>E-mail</label>
                <input type="email" name="email" required>

                <label>Wachtwoord</label>
                <input type="password" name="wachtwoord" required>

                <button type="submit">Inloggen</button>
                <button type="button" onclick="accountSluit('loginModal')">Annuleren</button>
            </form>

            <p class="wissel">Nog geen account?
                <a href="#" onclick="accountSluit('loginModal'); accountOpen('registreerModal'); return false;">Registreren</a>
            </p>
        </div>
    </div>

    <!-- schermpje: registreren -->
    <div class="modal" id="registreerModal" style="display:<?= $openModal == 'registreren' ? 'flex' : 'none' ?>">
        <div class="modal-inhoud">
            <h2>Account maken</h2>

            <?php if ($fout != "" && $openModal == 'registreren'): ?>
                <p class="fout"><?= h($fout) ?></p>
            <?php endif; ?>

            <p>Registreren kan met het e-mailadres dat bij je kaderlidgegevens staat.</p>

            <form method="post" action="<?= h($actieUrl) ?>">
                <?php csrfVeld(); ?>
                <input type="hidden" name="actie" value="registreren">
                <input type="hidden" name="terug" value="<?= h($_SERVER['REQUEST_URI'] ?? $base . 'Index.php') ?>">

                <label>E-mail</label>
                <input type="email" name="email" required>

                <label>Wachtwoord (minimaal 8 tekens)</label>
                <input type="password" name="wachtwoord" minlength="8" required>

                <label>Wachtwoord herhalen</label>
                <input type="password" name="wachtwoord2" minlength="8" required>

                <button type="submit">Registreren</button>
                <button type="button" onclick="accountSluit('registreerModal')">Annuleren</button>
            </form>

            <p class="wissel">Al een account?
                <a href="#" onclick="accountSluit('registreerModal'); accountOpen('loginModal'); return false;">Inloggen</a>
            </p>
        </div>
    </div>
<?php endif; ?>

<script>
    // account-schermpjes openen en sluiten
    function accountOpen(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function accountSluit(id) {
        document.getElementById(id).style.display = 'none';
    }


</script>

<style>
/* account rechtsboven */
.nav-account {
  display: flex;
  align-items: center;
}
.nav-account .nav-logout {
    width: auto;
    margin: 0;
}
.nav-account button {
    padding: 5px 10px;
    cursor: pointer;
    margin-left: 18px;
}

/* foutmelding in het schermpje */
.fout {
    color: #b00020;
}

.wissel {
    margin-bottom: 0;
    font-size: 14px;
}

/* links in het schermpje niet wit (de nav a regel is alleen voor de navbar) */
.modal-inhoud a {
    color: #0645ad;
}
</style>
