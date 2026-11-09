<?php
class Deelnemers
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // alle deelnemers ophalen
    public function ophalen()
    {
        $stmt = $this->conn->query("SELECT * FROM deelnemer ORDER BY achternaam, roepnaam");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // één deelnemer ophalen (voor het aanpassen)
    public function ophalenEen($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM deelnemer WHERE deelnemer_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // deelnemer opslaan
    public function toevoegen($data)
    {
        $sql = "INSERT INTO deelnemer (roepnaam, voorvoegsel, achternaam, geboortedatum, woonplaats, telefoon)
                VALUES (:roepnaam, :voorvoegsel, :achternaam, :geboortedatum, :woonplaats, :telefoon)";

        $stmt = $this->conn->prepare($sql);

        // lege velden worden NULL
        return $stmt->execute([
            ':roepnaam'      => $data['roepnaam'],
            ':voorvoegsel'   => $data['voorvoegsel'] ?: null,
            ':achternaam'    => $data['achternaam'],
            ':geboortedatum' => $data['geboortedatum'] ?: null,
            ':woonplaats'    => $data['woonplaats'] ?: null,
            ':telefoon'      => $data['telefoon'] ?: null,
        ]);
    }

    // deelnemer aanpassen
    public function aanpassen($id, $data)
    {
        $sql = "UPDATE deelnemer
                SET roepnaam = :roepnaam, voorvoegsel = :voorvoegsel, achternaam = :achternaam,
                    geboortedatum = :geboortedatum, woonplaats = :woonplaats, telefoon = :telefoon
                WHERE deelnemer_id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':roepnaam'      => $data['roepnaam'],
            ':voorvoegsel'   => $data['voorvoegsel'] ?: null,
            ':achternaam'    => $data['achternaam'],
            ':geboortedatum' => $data['geboortedatum'] ?: null,
            ':woonplaats'    => $data['woonplaats'] ?: null,
            ':telefoon'      => $data['telefoon'] ?: null,
            ':id'            => $id,
        ]);
    }

    // deelnemer verwijderen
    public function verwijderen($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM deelnemer WHERE deelnemer_id = :id");
        return $stmt->execute([':id' => $id]);
    }
}