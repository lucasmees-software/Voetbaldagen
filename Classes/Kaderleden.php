<?php
class Kaderleden
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Alle kaderleden ophalen of zoeken
    public function getKaderleden($zoeken = '')
    {
        $sql = "SELECT *
                FROM kaderlid
                WHERE voornaam LIKE :zoeken
                   OR achternaam LIKE :zoeken2
                ORDER BY achternaam, voornaam";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'zoeken' => '%' . $zoeken . '%',
            'zoeken2' => '%' . $zoeken . '%'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eén kaderlid ophalen
    public function getKaderlid($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM kaderlid WHERE kaderlid_id = :id"
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kaderlid toevoegen
    public function toevoegen($voornaam, $achternaam, $telefoon, $email)
    {
        $sql = "INSERT INTO kaderlid
                (voornaam, achternaam, telefoon, email)
                VALUES (:voornaam, :achternaam, :telefoon, :email)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'voornaam' => $voornaam,
            'achternaam' => $achternaam,
            'telefoon' => $telefoon ?: null,
            'email' => $email ?: null
        ]);
    }

    // Kaderlid bewerken
    public function updaten($id, $voornaam, $achternaam, $telefoon, $email)
    {
        $sql = "UPDATE kaderlid
                SET voornaam = :voornaam,
                    achternaam = :achternaam,
                    telefoon = :telefoon,
                    email = :email
                WHERE kaderlid_id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'voornaam' => $voornaam,
            'achternaam' => $achternaam,
            'telefoon' => $telefoon ?: null,
            'email' => $email ?: null
        ]);
    }

    // Kaderlid verwijderen
    public function verwijderen($id)
    {
        $this->pdo->beginTransaction();

        try {
            // Eerst de toewijzingen verwijderen
            $stmt = $this->pdo->prepare(
                "DELETE FROM kaderlid_toewijzing
                 WHERE kaderlid_id = :id"
            );
            $stmt->execute(['id' => $id]);

            // Daarna het kaderlid zelf verwijderen
            $stmt = $this->pdo->prepare(
                "DELETE FROM kaderlid WHERE kaderlid_id = :id"
            );
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
