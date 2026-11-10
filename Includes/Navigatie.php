<?php
// page
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

    </nav>
</header>