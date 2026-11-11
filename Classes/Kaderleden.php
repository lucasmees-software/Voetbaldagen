<?php

class Kaderleden
{
    public function getKaderleden(PDO $pdo): array
    {
        $sql = "SELECT kaderlid_id, voornaam, achternaam, telefoon, email
                FROM kaderlid
                ORDER BY achternaam, voornaam";

        return $pdo->query($sql)->fetchAll();
    }
}