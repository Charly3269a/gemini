<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Log that the script was accessed.  This is VERY helpful for debugging.
error_log('delete_task_photo.php accessed');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('delete_task_photo.php: Invalid request method');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$taskId = $_POST['task_id'] ?? null;
$photoType = $_POST['photo_type'] ?? null;

if (!$taskId || !is_numeric($taskId)) {
    error_log('delete_task_photo.php: Invalid task ID');
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit;
}

if (!in_array($photoType, ['before_photo', 'after_photo'])) {
    error_log('delete_task_photo.php: Invalid photo type');
    echo json_encode(['success' => false, 'error' => 'Invalid photo type']);
    exit;
}

try {
    // Get the file path from the database
    $stmt = $pdo->prepare("SELECT {$photoType} FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        error_log("delete_task_photo.php: Task not found for ID: $taskId");
        echo json_encode(['success' => false, 'error' => 'Task not found']);
        exit;
    }

    $filePath = $row[$photoType];
    error_log("delete_task_photo.php: File path from DB: " . $filePath);

    if (!empty($filePath)) {  // Only try to delete if a path exists
        // Construct the full file path (server-side path)
        $fullFilePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
        error_log("delete_task_photo.php: Full file path: " . $fullFilePath);

        // Check if the file exists before attempting to delete
        if (file_exists($fullFilePath)) {
            if (unlink($fullFilePath)) {
                error_log("delete_task_photo.php: File deleted successfully: " . $fullFilePath);
            } else {
                error_log("delete_task_photo.php: Failed to delete file: " . $fullFilePath);
                echo json_encode(['success' => false, 'error' => 'Failed to delete file from server']);
                exit;
            }
        } else {
            error_log("delete_task_photo.php: File does not exist: " . $fullFilePath);
            // File doesn't exist, but that's OK.  We still want to clear the DB entry.
        }
    } else {
        error_log("delete_task_photo.php: No file path found in database for task ID $taskId and photo type $photoType");
        // No file to delete, but that's not necessarily an error.  The DB might already be cleared.
    }

    // Update the database to set the photo column to NULL
    $stmt = $pdo->prepare("UPDATE tasks SET {$photoType} = NULL WHERE id = ?");
    $stmt->execute([$taskId]);

    // Always return success if we reach this point.  The file (if it existed) is deleted, and the DB is updated.
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log('Database Error in delete_task_photo.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]); // More specific error
}

?>
