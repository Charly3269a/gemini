<?php
session_start();
require_once '../../auth/check_auth.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: /bolt/auth/restricted.php'); // Redirect if not admin
    exit;
}
require_once '../../config/database.php';

// Fetch all users
$stmt = $pdo->query("SELECT id, username, role, document_number, document_front, document_back, skills, address, created_at FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
    <style>
        /* Styles for the image modal */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow: auto;
        }

        .image-modal-content {
            position: relative;
            margin: auto;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .image-modal-content img {
            max-width: 100%;
            max-height: 90vh;
            display: block;
        }

        .image-modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #f1f1f1;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/header.php'; ?>
    <?php include '../../auth/adduser_modal.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Lista de Usuarios</h1>

        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doc. Frente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doc. Dorso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Habilidades</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['document_number'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['document_front']): ?>
                                    <img src="<?php echo htmlspecialchars($user['document_front']); ?>" alt="Document Front" class="h-10 w-10 object-cover cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($user['document_front']); ?>')">
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['document_back']): ?>
                                <img src="<?php echo htmlspecialchars($user['document_back']); ?>" alt="Document Back" class="h-10 w-10 object-cover cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($user['document_back']); ?>')">

                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['skills'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/bolt/auth/edituser.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">Editar</a>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-800">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal structure -->
    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="image-modal-close" onclick="closeModal()">&times;</span>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Full Size Image">
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="/bolt/assets/js/main.js"></script>
    <script>
        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modalImg.src = imageSrc;
            modal.style.display = "block";
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = "none";
        }

        function deleteUser(userId) {
            if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción eliminará también todas las tareas y subtareas asociadas.')) {
                fetch('/bolt/auth/deleteuser.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete_user_id=' + userId
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload(); // Simplest way to refresh
                    } else {
                        // More robust error handling
                        response.text().then(text => {
                            alert('Error al eliminar el usuario: ' + text);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de red al intentar eliminar el usuario.');
                });
            }
        }
    </script>
</body>
</html>
