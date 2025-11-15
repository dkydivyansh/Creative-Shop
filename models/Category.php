<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
class Category {
    private $pdo;

    /**
     * Category constructor.
     * @param PDO $pdo The database connection object.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetches all categories from the database.
     * @return array An array of categories.
     */
    public function getAllCategories() {
        try {
            $stmt = $this->pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Category Fetch Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single category's details by its name.
     * @param string $name The name of the category.
     * @return array|false The category data or false if not found.
     */
    public function getCategoryByName($name) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, description FROM categories WHERE name = :name");
            $stmt->execute(['name' => $name]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Single Category Fetch Error: " . $e->getMessage());
            return false;
        }
    }
}