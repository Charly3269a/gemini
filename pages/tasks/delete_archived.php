<?php
session_start();
require_once '../../auth/check_auth.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: /bolt/auth/restricted.php');
    exit;
}
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        exit;
    }

    try {
        // Primero eliminamos las subtareas
        $stmt = $pdo->prepare("DELETE FROM subtasks WHERE task_id = ?");
        $stmt->execute([$id]);

        // Luego eliminamos la tarea
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND archived_at IS NOT NULL");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Tarea no encontrada o no está archivada']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>