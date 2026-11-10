<?php
class Teams
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // alle teams ophalen
    public function ophalen()
    {
        $stmt = $this->conn->query("SELECT * FROM basisteam ORDER BY editie_id, naam");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}