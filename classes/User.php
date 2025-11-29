<?php
/**
 * User Model Class
 * Handles all user-related database operations
 */

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $role;
    public $phone;
    public $barangay;
    public $purok;
    public $preparedness_level;
    public $total_points;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register new user
     */
    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  (first_name, last_name, email, password, role, phone, barangay, purok)
                  VALUES (:first_name, :last_name, :email, :password, :role, :phone, :barangay, :purok)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = $this->role ?? 'resident';

        // Bind values
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':barangay', $this->barangay);
        $stmt->bindParam(':purok', $this->purok);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                $this->preparedness_level = $row['preparedness_level'];
                $this->total_points = $row['total_points'];
                return true;
            }
        }

        return false;
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->preparedness_level = $row['preparedness_level'];
            $this->total_points = $row['total_points'];
            return true;
        }

        return false;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Update user points
     */
    public function updatePoints($user_id, $points) {
        $query = "UPDATE " . $this->table . " 
                  SET total_points = total_points + :points 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':points', $points);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    }

    /**
     * Update preparedness level
     */
    public function updatePreparedness($user_id, $level) {
        $query = "UPDATE " . $this->table . " 
                  SET preparedness_level = :level 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    }

    /**
     * Get all users (for responder)
     */
    public function getAll() {
        $query = "SELECT id, first_name, last_name, email, role, barangay, purok, 
                         preparedness_level, total_points, created_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get user statistics
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'resident' THEN 1 ELSE 0 END) as total_residents,
                    SUM(CASE WHEN preparedness_level = 'community-ready' THEN 1 ELSE 0 END) as ready_residents
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>e