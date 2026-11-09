<?php

class Teams
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTeams(): array
    {
        return $this->pdo
            ->query('SELECT id, naam, aantal_personen FROM teams ORDER BY naam')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeam(int $id): ?array
    {
        $query = $this->pdo->prepare(
            'SELECT id, naam, aantal_personen FROM teams WHERE id = :id'
        );
        $query->execute(['id' => $id]);
        $team = $query->fetch(PDO::FETCH_ASSOC);

        return $team ?: null;
    }

    public function saveTeam(?int $id, string $naam, int $aantalPersonen): bool
    {
        $sql = $id === null
            ? 'INSERT INTO teams (naam, aantal_personen) VALUES (:naam, :aantal_personen)'
            : 'UPDATE teams SET naam = :naam, aantal_personen = :aantal_personen WHERE id = :id';
        $query = $this->pdo->prepare($sql);
        $values = ['naam' => $naam, 'aantal_personen' => $aantalPersonen];

        if ($id !== null) {
            $values['id'] = $id;
        }

        return $query->execute($values);
    }

    public function deleteTeam(int $id): bool
    {
        $query = $this->pdo->prepare('DELETE FROM teams WHERE id = :id');

        return $query->execute(['id' => $id]);
    }
}
