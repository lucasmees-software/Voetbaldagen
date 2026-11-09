<?php

class Kaderleden
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getKaderleden(): array
    {
        $sql = "SELECT kaderlid_id, voornaam, achternaam, telefoon, email
                FROM kaderlid
                ORDER BY achternaam, voornaam";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verwijderKaderlid(int $kaderlidId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Verwijder eerst de gekoppelde toewijzingen.
            $sql = "DELETE FROM kaderlid_toewijzing
                    WHERE kaderlid_id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $kaderlidId]);

            // Verwijder daarna het kaderlid zelf.
            $sql = "DELETE FROM kaderlid
                    WHERE kaderlid_id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $kaderlidId]);

            $verwijderd = $stmt->rowCount() > 0;

            if (!$verwijderd) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}