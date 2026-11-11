<?php

require_once __DIR__ . '/../Database/Connection.php';

class Account
{
    private function connection(): PDO
    {
        global $pdo;
        return $pdo;
    }

    // kijken of er een acc is met deze email
    public function emailBestaat($email)
    {
        $stmt = $this->connection()->prepare("SELECT account_id FROM account WHERE email = :email");
        $stmt->execute(['email' => trim($email)]);
        return $stmt->fetch() ? true : false;
    }

    // nieuw account maken
    public function registreren($email, $wachtwoord)
    {
        $sql = "INSERT INTO account (email, wachtwoord_hash, rol, kaderlid_id)
                SELECT :email, :hash, 'KADERLID', kaderlid_id
                FROM kaderlid
                WHERE email = :kaderlid_email
                  AND NOT EXISTS (
                      SELECT 1 FROM account WHERE account.kaderlid_id = kaderlid.kaderlid_id
                  )
                LIMIT 1";
        $connection = $this->connection();
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            'email' => trim($email),
            'hash' => password_hash($wachtwoord, PASSWORD_DEFAULT),
            'kaderlid_email' => trim($email),
        ]);

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $connection->lastInsertId();
    }

    // inloggen
    public function inloggen($email, $wachtwoord)
    {
        $stmt = $this->connection()->prepare("SELECT * FROM account WHERE email = :email AND actief = 1");
        $stmt->execute(['email' => trim($email)]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account && password_verify($wachtwoord, $account['wachtwoord_hash'])) {
            return $account;
        }
        return false;
    }
}
