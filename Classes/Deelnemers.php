<?php
class Deelnemers
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // deelnemer opslaan in de database
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
}