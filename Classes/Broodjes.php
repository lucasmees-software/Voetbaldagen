<?php

class Broodjes
{
    public const DAGEN = [
        'maandag' => 'Maandag',
        'dinsdag' => 'Dinsdag',
        'woensdag' => 'Woensdag',
        'donderdag' => 'Donderdag',
        'vrijdag' => 'Vrijdag',
    ];

    public const KEUZES = [
        'kaas' => 'Kaas',
        'vlees' => 'Vlees',
        'hagelslag' => 'Hagelslag',
        'krentenbol' => 'Krentenbol',
    ];

    public const VRIJDAG_KEUZES = [
        'kroket' => 'Kroket',
        'frikandel' => 'Frikandel',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function getBestellingenVoorWeek(string $weekStart): array
    {
        $query = $this->pdo->prepare(
            'SELECT naam, dag, broodje, vrijdag_keuze
             FROM broodjes_week_bestellingen
             WHERE week_start = :week_start
             ORDER BY naam'
        );
        $query->execute(['week_start' => $weekStart]);

        $bestellingen = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $rij) {
            $bestellingen[$rij['naam']]['broodjes'][$rij['dag']] = $rij['broodje'];
            if ($rij['dag'] === 'vrijdag') {
                $bestellingen[$rij['naam']]['vrijdag_keuze'] = $rij['vrijdag_keuze'];
            }
        }

        return $bestellingen;
    }

    public function slaWeekBestellingOp(string $naam, string $weekStart, array $keuzes, string $vrijdagKeuze): void
    {
        $this->pdo->beginTransaction();

        try {
            $query = $this->pdo->prepare(
                'INSERT INTO broodjes_week_bestellingen
                    (naam, week_start, dag, broodje, vrijdag_keuze)
                 VALUES
                    (:naam, :week_start, :dag, :broodje, :vrijdag_keuze)
                 ON DUPLICATE KEY UPDATE
                    broodje = VALUES(broodje),
                    vrijdag_keuze = VALUES(vrijdag_keuze)'
            );

            foreach (self::DAGEN as $dag => $label) {
                $query->execute([
                    'naam' => $naam,
                    'week_start' => $weekStart,
                    'dag' => $dag,
                    'broodje' => $keuzes[$dag],
                    'vrijdag_keuze' => $dag === 'vrijdag' ? $vrijdagKeuze : null,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
