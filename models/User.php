<?php
class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $email;
    public $password;
    public $is_active;
    public $activation_token;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . "
                SET email = :email,
                    password = :password,
                    activation_token = :activation_token,
                    is_active = 0";

        $stmt = $this->conn->prepare($query);

        $this->email = strtolower(trim($this->email));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        try {
            $this->activation_token = bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            $this->activation_token = md5(uniqid((string) rand(), true));
        }

        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':activation_token', $this->activation_token);

        return $stmt->execute();
    }

    public function emailExists()
    {
        $this->email = strtolower(trim($this->email));
        $query = "SELECT id FROM " . $this->table . " 
                WHERE email = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    public function activateAccount($token)
    {
        $query = "UPDATE " . $this->table . "
                SET is_active = 1, activation_token = ''
                WHERE activation_token = :token AND is_active = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function login()
    {
        $query = "SELECT id, email, password, is_active
                FROM " . $this->table . "
                WHERE email = ? AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $email = strtolower(trim($this->email));
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->email = $row['email'];
                return true;
            }
        }
        return false;
    }

    public function updatePassword()
    {
        $query = "UPDATE " . $this->table . "
                SET password = :password
                WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(':password', $hashed_password);
        $email = strtolower(trim($this->email));
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    public function updateProfile($new_email)
    {
        $new_email = strtolower(trim($new_email));
        $new_email = htmlspecialchars(strip_tags($new_email));

        $query = "UPDATE " . $this->table . "
                SET email = :new_email
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':new_email', $new_email);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function getUserById($id)
    {
        $query = "SELECT id, email, is_active, created_at FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $email = strtolower(trim($email));
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return null;
    }

    public function deleteByEmail($email)
    {
        $query = "DELETE FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $email = strtolower(trim($email));
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }
}
