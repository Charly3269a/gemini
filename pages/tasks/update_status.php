<?php
    session_start();
    require_once '../../config/database.php';
    header('Content-Type: application/json'); // Set Content-Type header

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $taskId = $_POST['task_id'] ?? null; // Use null coalescing operator
        $status = $_POST['status'] ?? null;

        // Validate input (important for security)
        if (empty($taskId) || !is_numeric($taskId)) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
            exit;
        }
        if (!in_array($status, ['pending', 'problems', 'completed'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid status.']);
            exit;
        }

        try {
            // Use prepared statements to prevent SQL injection
            $stmt = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :taskId");
            $stmt->bindParam(':status', $status, PDO::PARAM_STR); // Bind status as string
            $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT); // Bind task ID as integer
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No rows updated. Task ID might be incorrect.']);
            }

        } catch (PDOException $e) {
            // Log the error for debugging (check your PHP error logs)
            error_log("Database error in update_status.php: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database error. See server logs.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    }
    ?>
