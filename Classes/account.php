<?php
class Account
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // kijken of er een acc is met deze email
    public function emailBestaat($email)
    {
        $stmt = $this->conn->prepare("SELECT account_id FROM account WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    // nieuw account maken, iedereen begint als KADERLID
    public function registreren($email, $wachtwoord)
    {
        $sql = "INSERT INTO account (email, wachtwoord_hash, rol) VALUES (:email, :hash, 'KADERLID')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':hash'  => password_hash($wachtwoord, PASSWORD_DEFAULT),
        ]);
        return $this->conn->lastInsertId();
    }

    // inloggen: geeft het account terug als het klopt, anders false
    public function inloggen($email, $wachtwoord)
    {
        $stmt = $this->conn->prepare("SELECT * FROM account WHERE email = :email AND actief = 1");
        $stmt->execute([':email' => $email]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account && password_verify($wachtwoord, $account['wachtwoord_hash'])) {
            return $account;
        }
        return false;
    }
}