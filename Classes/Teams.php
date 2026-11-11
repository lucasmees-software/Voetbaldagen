<?php
class Teams
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    // alle teams ophalen, met de naam van de editie erbij
    public function ophalen(): array
    {
        $sql = "SELECT basisteam.*, editie.naam AS editie_naam
                FROM basisteam
                JOIN editie ON editie.editie_id = basisteam.editie_id
                ORDER BY basisteam.editie_id, basisteam.basisteam_id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // alle edities ophalen voor de dropdown
    public function ophalenEdities(): array
    {
        $stmt = $this->conn->query("SELECT editie_id, naam FROM editie ORDER BY editie_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // team opslaan
    public function toevoegen($data)
    {
        $sql = "INSERT INTO basisteam (editie_id, naam, doel_aantal_deelnemers, kleedlokaal)
                VALUES (:editie_id, :naam, :doel, :kleedlokaal)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'editie_id'   => $data['editie_id'],
            'naam'        => trim($data['naam'] ?? ''),
            'doel'        => $data['doel_aantal_deelnemers'],
            'kleedlokaal' => trim($data['kleedlokaal'] ?? '') ?: null,
        ]);
    }

    // team aanpassen
    public function aanpassen($id, $data)
    {
        $sql = "UPDATE basisteam
                SET editie_id = :editie_id, naam = :naam,
                    doel_aantal_deelnemers = :doel, kleedlokaal = :kleedlokaal
                WHERE basisteam_id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'editie_id'   => $data['editie_id'],
            'naam'        => trim($data['naam'] ?? ''),
            'doel'        => $data['doel_aantal_deelnemers'],
            'kleedlokaal' => trim($data['kleedlokaal'] ?? '') ?: null,
            'id'          => $id,
        ]);
    }

    // team verwijderen
    public function verwijderen($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM basisteam WHERE basisteam_id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
