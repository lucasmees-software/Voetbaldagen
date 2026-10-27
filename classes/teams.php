
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
        $query = $this->pdo->query(
            "SELECT * FROM ploeg ORDER BY naam ASC"
        );

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
