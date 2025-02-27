<?php
session_start();
require_once '../auth/check_auth.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: /bolt/auth/restricted.php');
    exit;
}
require_once '../config/database.php';

$error = '';
$success = '';
$user = []; // Initialize $user

$userId = $_GET['id'] ?? null;
if (!$userId || !is_numeric($userId)) {
    header('Location: /bolt/auth/deleteuser.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: /bolt/auth/deleteuser.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error fetching user data: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $document_number = $_POST['document_number'] ?? null;
    $skills = $_POST['skills'] ?? null;
    $address = $_POST['address'] ?? null;

    if (empty($username) || empty($role)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                $error = 'El nombre de usuario ya está en uso.';
            } else {
                $updateFields = "username = ?, role = ?, document_number = ?, skills = ?, address = ?";
                $updateValues = [$username, $role, $document_number, $skills, $address];

                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'La contraseña debe tener al menos 6 caracteres.';
                        $pdo->rollBack();
                        return;
                    }
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateFields .= ", password = ?";
                    $updateValues[] = $hashedPassword;
                }

                $document_front = $user['document_front'];
                $document_back = $user['document_back'];

                if (isset($_FILES['document_front']) && $_FILES['document_front']['error'] === UPLOAD_ERR_OK) {
                    $document_front = uploadFile('document_front', 'front', $user['document_front']);
                    $updateFields .= ", document_front = ?";
                    $updateValues[] = $document_front;
                }

                if (isset($_FILES['document_back']) && $_FILES['document_back']['error'] === UPLOAD_ERR_OK) {
                    $document_back = uploadFile('document_back', 'back', $user['document_back']);
                    $updateFields .= ", document_back = ?";
                    $updateValues[] = $document_back;
                }

                $updateValues[] = $userId;

                $stmt = $pdo->prepare("UPDATE users SET $updateFields WHERE id = ?");
                $stmt->execute($updateValues);

                $pdo->commit();
                $success = 'Usuario actualizado correctamente.';
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al actualizar el usuario: ' . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

function uploadFile($inputName, $suffix, $oldFilePath = null) {
    $uploadDir = __DIR__ . '/../assets/user_documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if ($oldFilePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldFilePath)) {
        unlink($_SERVER['DOCUMENT_ROOT'] . $oldFilePath);
    }

    $fileExtension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
    $uniqueFilename = uniqid('user_doc_') . '_' . time() . '_' . $suffix . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFilename;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $maxFileSize = 5 * 1024 * 1024;

    if (!in_array($_FILES[$inputName]['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed types: JPG, PNG, GIF, PDF.');
    }

    if ($_FILES[$inputName]['size'] > $maxFileSize) {
        throw new Exception('File is too large. Max size is 5MB.');
    }

    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadPath)) {
        return '/bolt/assets/user_documents/' . $uniqueFilename;
    } else {
        throw new Exception('Failed to move uploaded file.');
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Editar Usuario</h2>
                <p class="text-gray-600 mt-2">Modifique la información del usuario</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                    <input type="text" id="username" name="username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña (opcional)</label>
                    <input type="password" id="password" name="password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select id="role" name="role" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>Usuario Común</option>
                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>

                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700">Número de Documento (Opcional)</label>
                    <input type="text" id="document_number" name="document_number"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($user['document_number'] ?? ''); ?>">
                </div>

                <div>
                    <label for="document_front" class="block text-sm font-medium text-gray-700">Documento (Frente) (Opcional)</label>
                    <input type="file" id="document_front" name="document_front" accept="image/*,.pdf"
                           class="mt-1 block w-full" style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;">
                </div>

                <div>
                    <label for="document_back" class="block text-sm font-medium text-gray-700">Documento (Dorso) (Opcional)</label>
                    <input type="file" id="document_back" name="document_back" accept="image/*,.pdf"
                           class="mt-1 block w-full" style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;">
                </div>

                <div>
                    <label for="skills" class="block text-sm font-medium text-gray-700">Áreas de Competencia (Opcional)</label>
                    <textarea id="skills" name="skills"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($user['skills'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Dirección (Opcional)</label>
                    <textarea id="address" name="address"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Guardar Cambios
                    </button>
                </div>
            </form>
            <div class="text-sm text-gray-500 mt-4">
                <a href="../pages/users/list.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Volver a la lista
                </a>
            </div>
        </div>
    </div>
</body>
</html>
