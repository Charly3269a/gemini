<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $maps_url = $_POST['maps_url'];

    // Basic validation (you should add more robust validation)
    if (empty($name) || empty($address) || empty($phone)) {
        die("Error: Name, address, and phone are required.");
    }

    try {
        $stmt = $pdo->prepare("UPDATE clients SET name = ?, address = ?, phone = ?, maps_url = ? WHERE id = ?");
        $stmt->execute([$name, $address, $phone, $maps_url, $id]);

        header('Location: /bolt/pages/clients/list.php');
        exit;
    } catch (PDOException $e) {
        die("Error updating client: " . $e->getMessage());
    }
} else {
    header('Location: /bolt/pages/clients/list.php'); // Redirect if not a POST request
    exit;
}
?>
