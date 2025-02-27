<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';

// Obtener lista de clientes
$stmt = $pdo->query("SELECT * FROM clients ORDER BY name");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include '../../includes/header.php'; ?>
                    <?php include '../../auth/adduser_modal.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Lista de Clientes
            </h1>
            <a href="/bolt/pages/clients/add.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Agregar Cliente
            </a>
        </div>
        
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1" />
                                    </svg>
                                    ID
                                </div>
                            </th>
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Nombre
                                </div>
                            </th>
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Dirección
                                </div>
                            </th>
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Teléfono
                                </div>
                            </th>
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    Maps
                                </div>
                            </th>
                            <th scope="col" class="table-header">
                                <div class="inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($clients as $client): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="table-cell text-gray-500 inline-flex items-center text-center"><?php echo $client['id']; ?></td>
                            <td class="table-cell font-medium text-gray-900 inline-flex items-center text-center"><?php echo htmlspecialchars($client['name']); ?></td>
                            <td class="table-cell text-gray-500 inline-flex items-center text-center"><?php echo htmlspecialchars($client['address']); ?></td>
                            <td class="table-cell text-gray-500 inline-flex items-center text-center"><?php echo htmlspecialchars($client['phone']); ?></td>
                            <td class="table-cell inline-flex items-center">
                                                                    <?php if ($client['maps_url']): ?>
                                  <button onclick="openMapModal('<?php echo htmlspecialchars($client['maps_url']); ?>')"
                                            class="inline-flex items-center text-blue-600 hover:text-blue-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        Ver mapa
                                    </button>
                                    <?php endif; ?>
                            </td>
                            <td class="table-cell inline-flex items-center text-center">
                                <div class="flex space-x-2">
                                    <?php if($_SESSION['role'] === 'admin'): ?>
                                    <a href="/bolt/pages/clients/edit.php?id=<?php echo $client['id']; ?>" class="btn-edit inline-flex  items-center text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Editar
                                    </a>
                                    <?php endif; ?>
                                    <a href="/bolt/pages/tasks/archived.php?client_id=<?php echo $client['id']; ?>" class="btn-view inline-flex  items-center text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        Ver Historial
                                    </a>
                                    <?php if($_SESSION['role'] === 'admin'): ?>
                                    <button onclick="deleteClient(<?php echo $client['id']; ?>)" class="btn-delete inline-flex  items-center text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Eliminar
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="/bolt/assets/js/main.js"></script>
    <script>
    function deleteClient(id) {
        if (confirm('¿Está seguro de que desea eliminar este cliente?')) {
            fetch(`/bolt/pages/clients/delete.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error al eliminar el cliente');
                }
            });
        }
    }

// Google Maps modal handler
function openMapModal(mapUrl) {
    const overlay = document.createElement('div');
    overlay.id = 'map-overlay';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'; // Added z-50

    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white p-4 rounded-lg shadow-lg w-3/4 h-3/4 relative'; // Added relative

    const closeButton = document.createElement('button');
    closeButton.className = 'absolute top-2 right-2 text-gray-600 hover:text-gray-900';
    closeButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>`;
    closeButton.onclick = closeMapModal;

    const iframe = document.createElement('iframe');
    iframe.src = mapUrl;
    iframe.width = '100%';
    iframe.height = '100%';
    iframe.style.border = '0';
    iframe.allowFullscreen = true;

    modalContent.appendChild(closeButton);
    modalContent.appendChild(iframe);
    overlay.appendChild(modalContent);
    document.body.appendChild(overlay);
}

function closeMapModal() {
    const overlay = document.getElementById('map-overlay');
    if (overlay) {
        document.body.removeChild(overlay);
    }
}

        function embedMap(mapUrl, container) {
            const iframe = document.createElement('iframe');
            iframe.src = mapUrl;  // Use the URL directly
            iframe.width = '100%';
            iframe.height = '300';
            iframe.style.border = '0';
            iframe.allowFullscreen = true;

            container.innerHTML = '';
            container.appendChild(iframe);
        }

    </script>
</body>
</html>