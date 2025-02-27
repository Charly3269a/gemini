<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['task_id'];
    $subtaskId = $_POST['subtask_id'];
    $completed = $_POST['completed'];

    try {
        // Actualizar el estado de la subtarea
        // CORRECTED: Use named parameters and bind them correctly.
        $stmt = $pdo->prepare("UPDATE subtasks SET completed = :completed WHERE id = :subtaskId AND task_id = :taskId");
        $stmt->bindParam(':completed', $completed, PDO::PARAM_INT); // Bind as integer
        $stmt->bindParam(':subtaskId', $subtaskId, PDO::PARAM_INT); // Bind as integer
        $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT); // Bind as integer
        $stmt->execute();


        // Obtener el total de subtareas y las completadas
        $stmt = $pdo->prepare("
            SELECT
                (SELECT COUNT(*) FROM subtasks WHERE task_id = :taskId1) as total_subtasks,
                (SELECT COUNT(*) FROM subtasks WHERE task_id = :taskId2 AND completed = 1) as completed_subtasks
        ");

        // Bind parameters for the counts query.  Use different names to avoid conflicts.
        $stmt->bindParam(':taskId1', $taskId, PDO::PARAM_INT);
        $stmt->bindParam(':taskId2', $taskId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        $completedSubtasksCount = intval($result['completed_subtasks']);
        $totalSubtasksCount = intval($result['total_subtasks']);
        $shouldUpdateStatus = false;

        // Determinar si necesitamos actualizar el estado de la tarea
        if ($completedSubtasksCount === $totalSubtasksCount) {
            // Todas las subtareas están completadas
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'terminado' WHERE id = :taskId");
            $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT); // Bind parameter
            $stmt->execute();
            $shouldUpdateStatus = true;
        } elseif ($completed == 0) {
            // Si se desmarcó una subtarea y no todas están completadas
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'pending' WHERE id = :taskId");
            $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT); // Bind parameter
            $stmt->execute();
            $shouldUpdateStatus = true;
        }

        echo json_encode([
            'success' => true,
            'completed_subtasks' => $completedSubtasksCount,
            'total_subtasks' => $totalSubtasksCount,
            'should_update_status' => $shouldUpdateStatus
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
