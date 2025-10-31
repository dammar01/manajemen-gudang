<?php
class Product
{
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $description;
    public $quantity;
    public $price;
    public $created_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . "
                SET name = :name,
                    description = :description,
                    quantity = :quantity,
                    price = :price,
                    created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':created_by', $this->created_by);

        return $stmt->execute();
    }

    public function getAll($createdBy = null)
    {
        if ($createdBy !== null) {
            $query = "SELECT * FROM " . $this->table . " WHERE created_by = :created_by ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':created_by', $createdBy, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        }

        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->quantity = $row['quantity'];
            $this->price = $row['price'];
            $this->created_by = $row['created_by'];
            return true;
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . "
                SET name = :name,
                    description = :description,
                    quantity = :quantity,
                    price = :price
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
