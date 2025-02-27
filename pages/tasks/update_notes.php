<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['task_id'] ?? null;
    $notes = $_POST['notes'] ?? null;

    if (!$taskId) {
        echo json_encode(['success' => false, 'error' => 'Task ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
        $stmt->execute([$notes, $taskId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
