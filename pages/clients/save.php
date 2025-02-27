<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $maps_url = $_POST['maps_url'];

    try {
        $stmt = $pdo->prepare("INSERT INTO clients (name, address, phone, maps_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $address, $phone, $maps_url]);
        
        header('Location: /bolt/pages/clients/list.php');
        exit;
    } catch (PDOException $e) {
        die("Error al guardar el cliente: " . $e->getMessage());
    }
}
?>