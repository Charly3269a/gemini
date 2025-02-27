<?php
require_once '../config/database.php';

try {
    // Verificar si la tabla users existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Crear la tabla si no existe
        $pdo->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "Tabla 'users' creada correctamente.<br>";
    }

    // Verificar si existe el usuario admin
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetch();

        if (!$adminExists) {
         // Crear el usuario admin si no existe
         $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
         $stmt->execute(['admin', $password, 'admin']);
         echo "Usuario admin creado correctamente.<br>";
         echo "Usuario: admin<br>";
        echo "Contraseña: admin123<br>";
    } else {
        echo "El usuario admin ya existe.<br>";
    }

    // Mostrar información de debug
    echo "<br>Información de debug:<br>";
    $stmt = $pdo->query("SELECT id, username, password FROM users");
    while ($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Username: " . $row['username'] . "<br>";
        echo "Password Hash: " . $row['password'] . "<br><br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>