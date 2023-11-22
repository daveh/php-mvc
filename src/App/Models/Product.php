<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Model;
use PDO;

// Example model
class Product extends Model
{
    // protected $table = "products";

    protected function validate(array $data): void
    {
        if (empty($data["name"])) {
            
            $this->addError("name", "Name is required");

        }
    }

    public function getTotal(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM product";

        $conn = $this->database->getConnection();

        $stmt = $conn->query($sql);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row["total"];
    }
}