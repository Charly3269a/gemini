<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Update the main task, INCLUDING user_id
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET client_id = ?, description = ?, schedule_time = ?, 
                schedule_date = ?, value = ?, expenses = ?, user_id = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['client_id'],
            $_POST['description'],
            $_POST['schedule_time'],
            $_POST['schedule_date'],
            $_POST['value'],
            $_POST['expenses'],
            $_POST['user_id'] ?: null, //  Allow for NULL (unassigned)
            $_POST['id']
        ]);

        // Delete existing subtasks
        $stmt = $pdo->prepare("DELETE FROM subtasks WHERE task_id = ?");
        $stmt->execute([$_POST['id']]);

        // Insert updated subtasks
        if (isset($_POST['subtasks']) && is_array($_POST['subtasks'])) {
            $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, description, completed) VALUES (?, ?, ?)");
            foreach ($_POST['subtasks'] as $index => $subtask) {
                $completed = in_array($index, $_POST['subtasks_completed'] ?? []) ? 1 : 0;
                $stmt->execute([$_POST['id'], $subtask, $completed]);
            }
        }

        $pdo->commit();
        header('Location: /bolt/pages/tasks/list.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error al actualizar la tarea: " . $e->getMessage());
    }
}
?>
