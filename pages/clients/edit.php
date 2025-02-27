<?php
    session_start();
    require_once '../../auth/check_auth.php'; // Ensure auth is checked first
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /bolt/auth/restricted.php'); // Redirect if not admin
        exit;
    }
    require_once '../../config/database.php';

// Check if ID is provided and is numeric
$clientId = $_GET['id'] ?? null;
if (!$clientId || !is_numeric($clientId)) {
    header('Location: /bolt/pages/clients/list.php');
    exit;
}

// Fetch client data
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$clientId]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        header('Location: /bolt/pages/clients/list.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching client data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include '../../includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Editar Cliente
            </h1>
            <a href="/bolt/pages/clients/list.php"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver a la lista
            </a>
        </div>

        <div class="max-w-3xl mx-auto">
            <form action="/bolt/pages/clients/update.php" method="POST" class="bg-white shadow-xl rounded-lg overflow-hidden">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($client['id']); ?>">

                <!-- Información Personal -->
                <div class="border-b border-gray-200">
                    <div class="p-6 space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información Personal
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="name" name="name" required
                                       class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
                                       placeholder="Nombre completo del cliente"
                                       style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;"
                                       value="<?php echo htmlspecialchars($client['name']); ?>">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" id="phone" name="phone" required
                                       class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
                                       placeholder="Número de teléfono"
                                       style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;"
                                       value="<?php echo htmlspecialchars($client['phone']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Ubicación -->
                <div class="border-b border-gray-200">
                    <div class="p-6 space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Información de Ubicación
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                                <input type="text" id="address" name="address" required
                                       class="form-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
                                       placeholder="Dirección completa"
                                       style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;"
                                       value="<?php echo htmlspecialchars($client['address']); ?>">
                            </div>
                            <div>
                                <label for="search_address" class="block text-sm font-medium text-gray-700 mb-1">Buscar en Google Maps</label>
                                <div class="flex space-x-2">
                                    <input type="text" id="search_address"
                                           class="form-input flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200"
                                           placeholder="Ingrese la dirección para buscar en el mapa" style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;">
                                    <button type="button" onclick="searchAddress()"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                        Buscar
                                    </button>
                                </div>
                            </div>
                            <div id="map_container" class="<?php echo $client['maps_url'] ? '' : 'hidden'; ?>">
                                <div class="rounded-lg overflow-hidden border border-gray-200">
                                    <div id="map" class="w-full h-64">
                                        <?php if ($client['maps_url']): ?>
                                            <iframe
                                                width="100%"
                                                height="300"
                                                style="border:0"
                                                allowfullscreen
                                                src="<?php echo htmlspecialchars($client['maps_url']); ?>"
                                            ></iframe>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="maps_url" name="maps_url" value="<?php echo htmlspecialchars($client['maps_url']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="p-6 bg-gray-50 flex justify-between items-center">
                    <a href="/bolt/pages/clients/list.php"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="/bolt/assets/js/main.js"></script>
    <script>
        function searchAddress() {
            const address = document.getElementById('search_address').value;
            const mapContainer = document.getElementById('map_container');
            const mapDiv = document.getElementById('map');
            const mapsUrlInput = document.getElementById('maps_url');
            const apiKey = 'AIzaSyBZ3fEAKDdmwuLWi2zCSreGprNG3BsVFLE';

            if (!address) {
                alert('Por favor, ingrese una dirección para buscar.');
                return;
            }

            const embedUrl = `https://www.google.com/maps/embed/v1/place?key=${apiKey}&q=${encodeURIComponent(address)}`;

            mapDiv.innerHTML = `<iframe width="100%" height="256" frameborder="0" style="border:0"
            src="${embedUrl}" allowfullscreen></iframe>`;
            mapsUrlInput.value = embedUrl;
            mapContainer.classList.remove('hidden');
        }
    </script>
</body>
</html>
