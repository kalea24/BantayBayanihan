<?php
/**
 * Drill Progress Model Class
 * Handles gamification and drill tracking
 */

class DrillProgress {
    private $conn;
    private $table = 'drill_progress';

    public $id;
    public $user_id;
    public $drill_type;
    public $total_points;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get user's drill progress
     */
    public function getUserProgress($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY last_activity DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get or create drill progress for specific type
     */
    public function getOrCreate($user_id, $drill_type) {
        // Check if exists
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND drill_type = :drill_type 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':drill_type', $drill_type);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }

        // Create new
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, drill_type, completed_tasks, quiz_results, checklist_items)
                  VALUES (:user_id, :drill_type, '[]', '[]', '[]')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':drill_type', $drill_type);
        $stmt->execute();

        // Return newly created
        return $this->getOrCreate($user_id, $drill_type);
    }

    /**
     * Complete a task
     */
    public function completeTask($user_id, $drill_type, $task_id, $task_name, $points) {
        $progress = $this->getOrCreate($user_id, $drill_type);
        $completed_tasks = json_decode($progress['completed_tasks'], true);

        // Check if already completed
        foreach ($completed_tasks as $task) {
            if ($task['taskId'] === $task_id) {
                return ['success' => false, 'message' => 'Task already completed'];
            }
        }

        // Add new task
        $completed_tasks[] = [
            'taskId' => $task_id,
            'taskName' => $task_name,
            'completedAt' => date('Y-m-d H:i:s'),
            'pointsEarned' => $points
        ];

        // Update database
        $query = "UPDATE " . $this->table . " 
                  SET completed_tasks = :tasks,
                      total_points = total_points + :points,
                      status = 'in_progress'
                  WHERE user_id = :user_id AND drill_type = :drill_type";
        
        $stmt = $this->conn->prepare($query);
        $tasks_json = json_encode($completed_tasks);
        $stmt->bindParam(':tasks', $tasks_json);
        $stmt->bindParam(':points', $points);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':drill_type', $drill_type);
        
        if ($stmt->execute()) {
            // Update user total points
            $user_query = "UPDATE users SET total_points = total_points + :points WHERE id = :user_id";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(':points', $points);
            $user_stmt->bindParam(':user_id', $user_id);
            $user_stmt->execute();

            return ['success' => true, 'points_earned' => $points];
        }

        return ['success' => false, 'message' => 'Update failed'];
    }

    /**
     * Submit quiz
     */
    public function submitQuiz($user_id, $drill_type, $quiz_id, $score, $total_questions) {
        $progress = $this->getOrCreate($user_id, $drill_type);
        $quiz_results = json_decode($progress['quiz_results'], true);

        // Calculate points (1 point per correct answer)
        $points = $score;

        // Add quiz result
        $quiz_results[] = [
            'quizId' => $quiz_id,
            'score' => $score,
            'totalQuestions' => $total_questions,
            'completedAt' => date('Y-m-d H:i:s'),
            'pointsEarned' => $points
        ];

        // Update database
        $query = "UPDATE " . $this->table . " 
                  SET quiz_results = :results,
                      total_points = total_points + :points
                  WHERE user_id = :user_id AND drill_type = :drill_type";
        
        $stmt = $this->conn->prepare($query);
        $results_json = json_encode($quiz_results);
        $stmt->bindParam(':results', $results_json);
        $stmt->bindParam(':points', $points);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':drill_type', $drill_type);
        
        if ($stmt->execute()) {
            // Update user total points
            $user_query = "UPDATE users SET total_points = total_points + :points WHERE id = :user_id";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(':points', $points);
            $user_stmt->bindParam(':user_id', $user_id);
            $user_stmt->execute();

            // Check for badges
            $this->checkBadges($user_id);

            return ['success' => true, 'points_earned' => $points];
        }

        return ['success' => false, 'message' => 'Update failed'];
    }

    /**
     * Check and award badges
     */
    private function checkBadges($user_id) {
        // Get user total points
        $query = "SELECT total_points FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) return;

        $total_points = $user['total_points'];

        // Badge thresholds
        $badges = [
            ['points' => 10, 'name' => 'First Steps', 'desc' => 'Earned your first 10 points'],
            ['points' => 50, 'name' => 'Prepared Resident', 'desc' => 'Reached 50 points'],
            ['points' => 100, 'name' => 'Safety Champion', 'desc' => 'Reached 100 points'],
            ['points' => 200, 'name' => 'Community Ready', 'desc' => 'Reached 200 points']
        ];

        foreach ($badges as $badge) {
            if ($total_points >= $badge['points']) {
                // Check if badge already awarded
                $check = "SELECT id FROM badges 
                         WHERE user_id = :user_id AND name = :name";
                $check_stmt = $this->conn->prepare($check);
                $check_stmt->bindParam(':user_id', $user_id);
                $check_stmt->bindParam(':name', $badge['name']);
                $check_stmt->execute();

                if ($check_stmt->rowCount() == 0) {
                    // Award badge
                    $insert = "INSERT INTO badges (user_id, name, description) 
                              VALUES (:user_id, :name, :desc)";
                    $insert_stmt = $this->conn->prepare($insert);
                    $insert_stmt->bindParam(':user_id', $user_id);
                    $insert_stmt->bindParam(':name', $badge['name']);
                    $insert_stmt->bindParam(':desc', $badge['desc']);
                    $insert_stmt->execute();
                }
            }
        }

        // Update preparedness level
        if ($total_points >= 200) {
            $level = 'community-ready';
        } elseif ($total_points >= 100) {
            $level = 'prepared';
        } elseif ($total_points >= 50) {
            $level = 'aware';
        } else {
            $level = 'beginner';
        }

        $update = "UPDATE users SET preparedness_level = :level WHERE id = :user_id";
        $update_stmt = $this->conn->prepare($update);
        $update_stmt->bindParam(':level', $level);
        $update_stmt->bindParam(':user_id', $user_id);
        $update_stmt->execute();
    }

    /**
     * Get drill statistics
     */
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(DISTINCT user_id) as total_participants,
                    SUM(total_points) as total_points_earned,
                    AVG(total_points) as avg_points,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_drills
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>