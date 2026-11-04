<?php

require_once __DIR__ . '/../classes/kaderleden.php';

// Databaseverbinding
$host = 'localhost';
$dbname = 'voetbal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Databaseverbinding mislukt. Controleer de databasegegevens.');
}

$kaderledenClass = new Kaderleden($pdo);
$melding = '';
$fout = '';

// Kaderlid verwijderen
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['verwijder_kaderlid'])
) {

    $kaderlidId = filter_input(
        INPUT_POST,
        'kaderlid_id',
        FILTER_VALIDATE_INT
    );

    if ($kaderlidId && $kaderlidId > 0) {
        try {
            if ($kaderledenClass->verwijderKaderlid($kaderlidId)) {
                header('Location: Kaderleden.php?verwijderd=1');
                exit;
            }

            $fout = 'Het kaderlid is niet gevonden.';
        } catch (PDOException $e) {
            $fout = 'Verwijderen is mislukt. Controleer de database en gekoppelde gegevens.';
        }
    } else {
        $fout = 'Selecteer eerst een kaderlid.';
    }
}

if (isset($_GET['verwijderd'])) {
    $melding = 'Het kaderlid is verwijderd.';
}

try {
    $kaderleden = $kaderledenClass->getKaderleden();
} catch (PDOException $e) {
    die('De kaderleden konden niet worden geladen. Controleer de tabelnamen in de database.');
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kaderleden - Voetbaldagen</title>

    <link rel="stylesheet" href="../css/stylesheet.css">

    <style>
        .kaderleden-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .kaderleden-tabel {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .kaderleden-tabel th,
        .kaderleden-tabel td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .kaderlid-rij {
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .kaderlid-rij:hover {
            background-color: #edf5e8;
        }

        .kaderlid-rij.geselecteerd {
            background-color: #d8edc9;
            outline: 2px solid #3d702b;
            outline-offset: -2px;
        }

        .kaderleden-acties {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .verwijder-knop {
            background-color: #b42318;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .verwijder-knop:hover:not(:disabled) {
            background-color: #8f1c13;
        }

        .verwijder-knop:disabled {
            background-color: #999;
            cursor: not-allowed;
        }

        .melding {
            padding: 12px;
            margin-top: 15px;
            background: #e3f4df;
            color: #245b1c;
            border-radius: 5px;
        }

        .foutmelding {
            padding: 12px;
            margin-top: 15px;
            background: #fde8e7;
            color: #922018;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <?php
    $header = __DIR__ . '/../includes/header.html';

    if (file_exists($header)) {
        require_once $header;
    }
    ?>

    <main class="kaderleden-container">

        <h1>Kaderleden</h1>

        <p>Klik op een kaderlid om de volledige rij te selecteren.</p>

        <?php if ($melding !== ''): ?>
            <div class="melding">
                <?= htmlspecialchars($melding, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($fout !== ''): ?>
            <div class="foutmelding">
                <?= htmlspecialchars($fout, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="verwijderForm">

            <input
                type="hidden"
                name="kaderlid_id"
                id="kaderlid_id">

            <table class="kaderleden-tabel">

                <thead>
                    <tr>
                        <th>Voornaam</th>
                        <th>Achternaam</th>
                        <th>Telefoon</th>
                        <th>E-mail</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($kaderleden)): ?>

                        <tr>
                            <td colspan="4">Geen kaderleden gevonden.</td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($kaderleden as $kaderlid): ?>

                            <tr
                                class="kaderlid-rij"
                                data-id="<?= (int) $kaderlid['kaderlid_id'] ?>"
                                tabindex="0"
                                aria-selected="false">

                                <td>
                                    <?= htmlspecialchars(
                                        $kaderlid['voornaam'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $kaderlid['achternaam'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $kaderlid['telefoon'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $kaderlid['email'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

            <div class="kaderleden-acties">
                <button
                    type="submit"
                    name="verwijder_kaderlid"
                    id="verwijderKnop"
                    class="verwijder-knop"
                    disabled>
                    Verwijderen
                </button>

                <span id="selectieTekst">
                    Geen kaderlid geselecteerd.
                </span>

            </div>

        </form>

    </main>

    <script>
        const rijen = document.querySelectorAll('.kaderlid-rij');
        const idVeld = document.getElementById('kaderlid_id');
        const verwijderKnop = document.getElementById('verwijderKnop');
        const selectieTekst = document.getElementById('selectieTekst');
        const verwijderForm = document.getElementById('verwijderForm');

        let geselecteerdeRij = null;

        function selecteerRij(rij) {
            if (geselecteerdeRij) {
                geselecteerdeRij.classList.remove('geselecteerd');
                geselecteerdeRij.setAttribute('aria-selected', 'false');
            }

            geselecteerdeRij = rij;

            rij.classList.add('geselecteerd');
            rij.setAttribute('aria-selected', 'true');

            idVeld.value = rij.dataset.id;
            verwijderKnop.disabled = false;

            const naam =
                rij.cells[0].textContent.trim() + ' ' +
                rij.cells[1].textContent.trim();

            selectieTekst.textContent = 'Geselecteerd: ' + naam;
        }

        rijen.forEach(function(rij) {
            rij.addEventListener('click', function() {
                selecteerRij(rij);
            });

            rij.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    selecteerRij(rij);
                }
            });
        });

        verwijderForm.addEventListener('submit', function(event) {
            if (!idVeld.value || !geselecteerdeRij) {
                event.preventDefault();
                alert('Selecteer eerst een kaderlid.');
                return;
            }

            const naam =
                geselecteerdeRij.cells[0].textContent.trim() + ' ' +
                geselecteerdeRij.cells[1].textContent.trim();

            if (!confirm(
                    'Weet je zeker dat je ' + naam +
                    ' wilt verwijderen? Dit kan niet ongedaan worden gemaakt.'
                )) {
                event.preventDefault();
            }
        });
    </script>

</body>

</html>