<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['task_id'];
    $subtaskId = $_POST['subtask_id'];
    $completed = $_POST['completed'];

    try {
        // Actualizar el estado de la subtarea
        $stmt = $pdo->prepare("UPDATE subtasks SET completed = ? WHERE id = ? AND task_id = ?");
        $stmt->execute([$completed, $subtaskId, $taskId]);

        // Obtener el total de subtareas y las completadas
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM subtasks WHERE task_id = ?) as total_subtasks,
                (SELECT COUNT(*) FROM subtasks WHERE task_id = ? AND completed = 1) as completed_subtasks
        ");
        $stmt->execute([$taskId, $taskId]);
        $result = $stmt->fetch();

        // Determinar si necesitamos actualizar el estado de la tarea
        $shouldUpdateStatus = true;
        if ($result['completed_subtasks'] === $result['total_subtasks']) {
            // Todas las subtareas están completadas
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'terminado' WHERE id = ?");
            $stmt->execute([$taskId]);
        } elseif ($completed == 0) {
            // Si se desmarcó una subtarea y no todas están completadas
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'pending' WHERE id = ?");
            $stmt->execute([$taskId]);
        }

        echo json_encode([
            'success' => true,
            'completed_subtasks' => $result['completed_subtasks'],
            'total_subtasks' => $result['total_subtasks'],
            'should_update_status' => $shouldUpdateStatus
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>