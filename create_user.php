<?php
require_once 'config/database.php';

// Cambia estos valores por los deseados
$username = 'admin';
$password = 'admin123';

// Hash de la contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    echo "Usuario creado exitosamente";
} catch (PDOException $e) {
    echo "Error al crear usuario: " . $e->getMessage();
}
?>
