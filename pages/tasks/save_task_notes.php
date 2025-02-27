<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$taskId = $_POST['task_id'] ?? null;
$notes = $_POST['notes'] ?? null;

if (!$taskId || !is_numeric($taskId)) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
    $stmt->execute([$notes, $taskId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No task found with provided ID']);
    }
} catch (PDOException $e) {
    error_log('Database Error in save_task_notes.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

?>
