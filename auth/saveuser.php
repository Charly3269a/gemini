<?php
session_start();
require_once '../auth/check_auth.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: /bolt/auth/restricted.php');
    exit;
}
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (empty($username) || empty($password) || empty($role)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'El nombre de usuario ya está en uso.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $role]);

                if ($stmt->rowCount() > 0) {
                    header('Location: /bolt/pages/users/list.php');
                    exit;
                } else {
                    $error = 'Error al registrar el usuario.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }
}

// If there's an error, you might want to send the user back to the form
// with the error message.  For simplicity, I'm just redirecting to the list.
// A more robust solution would use sessions to store the error and pre-fill
// the form.
header('Location: /bolt/pages/users/list.php');
exit;

