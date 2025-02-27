<?php
    session_start();
    require_once '../../auth/check_auth.php'; // Ensure auth is checked first
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /bolt/auth/restricted.php'); // Redirect if not admin
        exit;
    }
    require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>