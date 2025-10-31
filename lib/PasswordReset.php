<?php
class PasswordReset
{
    private $conn;
    private $table = 'password_resets';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createToken($email)
    {
        $email = strtolower(trim($email));
        $this->deleteExistingTokens($email);

        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "INSERT INTO " . $this->table . "
                SET email = :email,
                    token = :token,
                    expires = :expires";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);

        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function validateToken($token)
    {
        $token = trim($token);
        $query = "SELECT email FROM " . $this->table . "
                WHERE token = :token AND expires > NOW()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['email'];
        }
        return false;
    }

    public function deleteToken($token)
    {
        $token = trim($token);
        $query = "DELETE FROM " . $this->table . " WHERE token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }

    private function deleteExistingTokens($email)
    {
        $email = strtolower(trim($email));
        $query = "DELETE FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
}
