<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    echo json_encode(['success' => false, 'error' => 'File upload error']);
    exit;
}

if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
    echo json_encode(['success' => false, 'error' => 'Task ID is missing']);
    exit;
}

if (!isset($_POST['photo_type']) || empty($_POST['photo_type'])) {
    echo json_encode(['success' => false, 'error' => 'Photo type is missing']);
    exit;
}

$taskId = intval($_POST['task_id']);
$photoType = $_POST['photo_type']; // 'before_photo' or 'after_photo'

if ($taskId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit;
}

if (!in_array($photoType, ['before_photo', 'after_photo'])) {
    echo json_encode(['success' => false, 'error' => 'Tipo inválido de foto']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 4 * 1024 * 1024; // 2MB max file size

if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Formato de archivo inválido. Solo están permitidos archivos con el formato JPG, PNG, y GIF.']);
    exit;
}

if ($_FILES['file']['size'] > $maxFileSize) {
    echo json_encode(['success' => false, 'error' => 'El tamaño máximo de archivo es de 4MB.']);
    exit;
}

$uploadDir = '../../assets/task_images/';
$fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$uniqueFilename = uniqid('task_photo_') . '_' . time() . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueFilename;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
    $filePathForDB = '/bolt/assets/task_images/' . $uniqueFilename;

    try {
        $stmt = $pdo->prepare("UPDATE tasks SET {$photoType} = ? WHERE id = ?");
        $stmt->execute([$filePathForDB, $taskId]);

        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'photo_url' => $filePathForDB,
            'task_id' => $taskId,
            'photo_type' => $photoType
        ]);

    } catch (PDOException $e) {
        error_log('Database Error in upload_task_photo.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
        unlink($uploadPath);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
}
?>