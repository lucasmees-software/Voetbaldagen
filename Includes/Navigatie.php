<?php
$basePath = basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'Pages' ? '../' : '';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$links = [
    'Index.php' => 'Home',
    'Pages/Deelnemers.php' => 'Deelnemers',
    'Pages/Teams.php' => 'Teams',
    'Pages/Wedstrijden.php' => 'Wedstrijden',
    'Pages/Kaderleden.php' => 'Kaderleden',
];
?>
<header class="site-header">
    <nav class="navbar">
        <a href="<?= $basePath ?>Index.php" class="nav-logo">Voetbaldagen Horst</a>
        <div class="nav-links">
            <?php foreach ($links as $path => $label): ?>
                <a href="<?= $basePath . $path ?>"<?= basename($path) === $currentPage ? ' class="active"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
</header>
