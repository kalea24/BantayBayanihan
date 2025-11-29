<?php
/**
 * Evacuation Site Model Class
 * Handles evacuation site operations with spatial data
 */

class EvacuationSite {
    private $conn;
    private $table = 'evacuation_sites';

    public $id;
    public $name;
    public $type;
    public $barangay;
    public $address;
    public $latitude;
    public $longitude;
    public $capacity;
    public $facilities;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all active evacuation sites
     */
    public function getAll() {
        $query = "SELECT 
                    id, name, type, barangay, address,
                    ST_X(location) as longitude,
                    ST_Y(location) as latitude,
                    capacity, facilities, wheelchair_accessible,
                    has_parking, contact_name, contact_phone, notes
                  FROM " . $this->table . " 
                  WHERE is_active = 1
                  ORDER BY name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
 * Get nearest evacuation sites
 */
public function getNearest($lat, $lng, $limit = 5) {
    $query = "SELECT 
                id, name, type, barangay, address,
                ST_X(location) as longitude,
                ST_Y(location) as latitude,
                capacity, facilities,
                ST_Distance_Sphere(
                    location,
                    ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')'))
                ) / 1000 AS distance_km
              FROM " . $this->table . "
              WHERE is_active = 1
              ORDER BY distance_km
              LIMIT ?";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $lng);
    $stmt->bindParam(2, $lat);
    $stmt->bindParam(3, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
    /**
     * Add new evacuation site
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (name, type, barangay, address, location, capacity, facilities, 
                   wheelchair_accessible, has_parking, contact_name, contact_phone, notes)
                  VALUES 
                  (:name, :type, :barangay, :address, 
                   ST_GeomFromText('POINT(:lng :lat)'),
                   :capacity, :facilities, :wheelchair, :parking, 
                   :contact_name, :contact_phone, :notes)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->barangay = htmlspecialchars(strip_tags($this->barangay));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Convert facilities array to JSON
        $facilities_json = is_array($this->facilities) 
            ? json_encode($this->facilities) 
            : $this->facilities;

        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':barangay', $this->barangay);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':lng', $this->longitude);
        $stmt->bindParam(':lat', $this->latitude);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':facilities', $facilities_json);
        $stmt->bindParam(':wheelchair', $this->wheelchair_accessible);
        $stmt->bindParam(':parking', $this->has_parking);
        $stmt->bindParam(':contact_name', $this->contact_name);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':notes', $this->notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update evacuation site
     */
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET name = :name,
                      type = :type,
                      barangay = :barangay,
                      address = :address,
                      location = ST_GeomFromText('POINT(:lng :lat)'),
                      capacity = :capacity,
                      facilities = :facilities,
                      wheelchair_accessible = :wheelchair,
                      has_parking = :parking,
                      contact_name = :contact_name,
                      contact_phone = :contact_phone,
                      notes = :notes
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $facilities_json = is_array($this->facilities) 
            ? json_encode($this->facilities) 
            : $this->facilities;

        // Bind
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':barangay', $this->barangay);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':lng', $this->longitude);
        $stmt->bindParam(':lat', $this->latitude);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':facilities', $facilities_json);
        $stmt->bindParam(':wheelchair', $this->wheelchair_accessible);
        $stmt->bindParam(':parking', $this->has_parking);
        $stmt->bindParam(':contact_name', $this->contact_name);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Delete (deactivate) evacuation site
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = 0 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Get site by ID
     */
    public function getById($id) {
        $query = "SELECT 
                    id, name, type, barangay, address,
                    ST_X(location) as longitude,
                    ST_Y(location) as latitude,
                    capacity, facilities, wheelchair_accessible,
                    has_parking, contact_name, contact_phone, notes
                  FROM " . $this->table . "
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Calculate route distance
     */
    public function calculateDistance($fromLat, $fromLng, $toLat, $toLng) {
        $query = "SELECT ST_Distance_Sphere(
                    ST_GeomFromText('POINT(? ?)'),
                    ST_GeomFromText('POINT(? ?)')
                  ) / 1000 AS distance_km";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fromLng);
        $stmt->bindParam(2, $fromLat);
        $stmt->bindParam(3, $toLng);
        $stmt->bindParam(4, $toLat);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['distance_km'];
    }
}
?>