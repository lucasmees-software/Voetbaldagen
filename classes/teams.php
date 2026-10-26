<?php
class Teams
{
    private $conn;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getTeams()
    {
        $sql = "SELECT * FROM teams";
        $result = $this->conn->query($sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}