<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';

// Get the user filter (for admin)
$user_id = $_GET['user_id'] ?? null;
$whereClause = 'WHERE t.archived_at IS NULL';

// Apply user filter based on role
if ($_SESSION['role'] === 'admin' && !empty($user_id)) {
    $whereClause .= " AND t.user_id = :user_id";
} elseif ($_SESSION['role'] !== 'admin') {
    // Regular users see only their tasks
    $whereClause .= " AND t.user_id = :user_id";
    $user_id = $_SESSION['user_id']; // Use session user ID
}
//else if admin and no user is selected, no user filter is applied.

// Get the date filter
$filter = $_GET['filter'] ?? 'today';

switch ($filter) {
    case 'today':
        $whereClause .= " AND t.schedule_date = CURDATE()";
        break;
    case 'tomorrow':
        $whereClause .= " AND t.schedule_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
        break;
    case 'this_week':
        $whereClause .= " AND YEARWEEK(t.schedule_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'last_week':
        $whereClause .= " AND YEARWEEK(t.schedule_date, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)";
        break;
    case 'this_month':
        $whereClause .= " AND MONTH(t.schedule_date) = MONTH(CURDATE()) AND YEAR(t.schedule_date) = YEAR(CURDATE())";
        break;
    case 'all':
        break; //no date filter
}

// Prepare the query
$query = "
    SELECT t.*, c.name as client_name, c.address, c.maps_url,
           GROUP_CONCAT(CONCAT(s.id, ':', s.description, ':', s.completed) SEPARATOR '||') as subtasks_info,
           (SELECT COUNT(*) FROM subtasks WHERE task_id = t.id) as total_subtasks,
           (SELECT COUNT(*) FROM subtasks WHERE task_id = t.id AND completed = 1) as completed_subtasks
    FROM tasks t
    JOIN clients c ON t.client_id = c.id
    LEFT JOIN subtasks s ON t.id = s.task_id
    {$whereClause}
    GROUP BY t.id
    ORDER BY t.schedule_date ASC, t.schedule_time ASC
";
$stmt = $pdo->prepare($query);

// Bind the user_id parameter if it's set
if ($user_id) {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}

$stmt->execute();
$tasks = $stmt->fetchAll();

// Calcular total
$total = 0;
foreach ($tasks as $task) {
    $total += $task['value'] - $task['expenses'];
}

// Fetch users (for admin dropdown)
$users = [];
if ($_SESSION['role'] === 'admin') {
    $userStmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $users = $userStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/bolt/assets/css/styles.css" rel="stylesheet">
    <style>
        .photo-upload-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-upload-photo {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4F46E5;
            background-color: #EEF2FF;
            border: 1px solid #6366F1;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-upload-photo:hover {
            background-color: #E0E7FF;
        }
        .photo-thumbnail {
            width: 3rem;
            height: 3rem;
            object-fit: cover;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .photo-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }
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
        max-height: 100%;
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
<body class="bg-gray-50">
    <?php include '../../includes/header.php'; ?>
                        <?php include '../../auth/adduser_modal.php'; ?>
    <div class="container mx-auto px-4 py-8">
    <div class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:justify-between md:items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">TAREAS</h1>

           <!-- User Filter (Admin Only) -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="flex items-center" >
                <label for="user-filter" class="mr-2 text-sm font-medium text-gray-700" >Filtrar por Usuario:</label>
                <select id="user-filter" onchange="filterTasksByUser()" class="form-select rounded-lg text-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200" style="margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); background-color: #ffffff; padding-left: 0.75rem; padding-right: 0.75rem; padding-top: 0.5rem; padding-bottom: 0.5rem; line-height: 1.5rem; font-size: 1rem;">
                    <option value="">Todos los Usuarios</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Date Filters -->
            <div class="flex flex-wrap gap-2">
                <?php
                // Function to generate filter links, preserving user_id if present
                function filterLink($filter, $label, $currentFilter, $currentUserId) {
                    $url = "/bolt/pages/tasks/list.php?filter=$filter";
                    if ($currentUserId) {
                        $url .= "&user_id=$currentUserId";
                    }
                    $activeClass = ($filter === $currentFilter) ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
                    return "<a href=\"$url\" class=\"inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 $activeClass\">$label</a>";
                }
                ?>

                <?php echo filterLink('today', 'Hoy', $filter, $user_id); ?>
                <?php echo filterLink('tomorrow', 'Mañana', $filter, $user_id); ?>
                <?php echo filterLink('this_week', 'Esta semana', $filter, $user_id); ?>
                <?php echo filterLink('this_month', 'Este mes', $filter, $user_id); ?>
                <?php echo filterLink('all', 'Todas', $filter, $user_id); ?>

                <a href="/bolt/pages/tasks/add.php"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Agregar Tarea
                </a>
            </div>
        </div>
        </div>


        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Fecha/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Ubicación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Subtareas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Gastos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Antes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Después</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider table-cell-border">Notas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="12" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    No hay tareas para mostrar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                            <tr data-task-id="<?php echo $task['id']; ?>" class="task-<?php echo strtolower($task['status']); ?> hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($task['client_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($task['schedule_date'])); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($task['schedule_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4 table-cell-border">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($task['description']); ?></div>
                                </td>
                                <td class="px-6 py-4 table-cell-border" style="min-width: 140px;">
                                   <?php if ($task['maps_url']): ?>
                                  <button onclick="openMapModal('<?php echo htmlspecialchars($task['maps_url']); ?>')"
                                            class="inline-flex items-center text-blue-600 hover:text-blue-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        Ver mapa
                                    </button>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 table-cell-border">
                                    <div class="space-y-2">
                                        <?php
                                        if ($task['subtasks_info']) {
                                            $subtasks = explode('||', $task['subtasks_info']);
                                            foreach ($subtasks as $subtask) {
                                                list($subtaskId, $description, $completed) = explode(':', $subtask);
                                                ?>
                                                <div class="flex items-center space-x-2">
                                                    <input type="checkbox"
                                                           id="subtask-<?php echo $subtaskId; ?>"
                                                           class="subtask-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-150"
                                                           data-task-id="<?php echo $task['id']; ?>"
                                                           data-subtask-id="<?php echo $subtaskId; ?>"
                                                           <?php echo $completed ? 'checked' : ''; ?>
                                                    >
                                                    <label for="subtask-<?php echo $subtaskId; ?>" class="text-sm text-gray-700">
                                                        <?php echo htmlspecialchars($description); ?>
                                                    </label>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-500">
                                        <span class="task-progress"><?php echo $task['completed_subtasks']; ?>/<?php echo $task['total_subtasks']; ?></span> completadas
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                  <select onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.value)"
                                          class="status-select form-input rounded-lg text-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                                      <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                      <option value="problems" <?php echo $task['status'] === 'problems' ? 'selected' : ''; ?>>Problemas</option>
                                      <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Terminado</option>
                                  </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="text-sm text-gray-500">$<?php echo number_format($task['value'], 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="text-sm text-gray-500">$<?php echo number_format($task['expenses'], 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="text-sm font-medium text-gray-900">$<?php echo number_format($task['value'] - $task['expenses'], 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="photo-upload-container"  data-photo-type="before_photo">
                                        <?php if ($task['before_photo']): ?>
                                            <div class="photo-actions">
                                                 <img src="<?php echo htmlspecialchars($task['before_photo']); ?>" alt="Before Photo" class="h-12 w-12 object-cover rounded mb-1 cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($task['before_photo']); ?>')"/>
                                                <button onclick="deleteTaskPhoto(<?php echo $task['id']; ?>, 'before_photo')" 
                                                        class="text-red-600 hover:text-red-900 text-xs">
                                                    Eliminar
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <input type="file" 
                                                   id="before_photo_<?php echo $task['id']; ?>" 
                                                   class="hidden" 
                                                   accept="image/*"
                                                   onchange="handlePhotoUpload(this, <?php echo $task['id']; ?>, 'before_photo')" />
                                            <button onclick="document.getElementById('before_photo_<?php echo $task['id']; ?>').click()" 
                                                    class="btn-upload-photo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap table-cell-border">
                                    <div class="photo-upload-container"  data-photo-type="after_photo">
                                        <?php if ($task['after_photo']): ?>
                                            <div class="photo-actions">
                                                <img src="<?php echo htmlspecialchars($task['after_photo']); ?>" alt="After Photo" class="h-12 w-12 object-cover rounded mb-1 cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($task['after_photo']); ?>')"/>
                                                <button onclick="deleteTaskPhoto(<?php echo $task['id']; ?>, 'after_photo')" 
                                                        class="text-red-600 hover:text-red-900 text-xs">
                                                    Eliminar
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <input type="file" 
                                                   id="after_photo_<?php echo $task['id']; ?>" 
                                                   class="hidden" 
                                                   accept="image/*"
                                                   onchange="handlePhotoUpload(this, <?php echo $task['id']; ?>, 'after_photo')" />
                                            <button onclick="document.getElementById('after_photo_<?php echo $task['id']; ?>').click()" 
                                                    class="btn-upload-photo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                 <td class="px-6 py-4 table-cell-border" style="min-width: 140px;">

        <div class="flex flex-col space-y-2">
            <textarea
                class="form-input w-full h-24 text-sm"
                placeholder="Agregar notas..."
                data-task-id="<?php echo $task['id']; ?>"
                id="notes-<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['notes']); ?></textarea>
            <div class="flex space-x-2">
                <button onclick="saveNotes(<?php echo $task['id']; ?>)"
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h-2v5.586l-1.293-1.293z"/>
                    </svg>
                    Guardar
                </button>
                <button onclick="clearNotes(<?php echo $task['id']; ?>)"
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Borrar
                </button>
            </div>
        </div>

</td>

                                <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="image-modal-close" onclick="closeModal()">&times;</span>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Full Size Image">
        </div>
   </div>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2">
                                        <a href="/bolt/pages/tasks/edit.php?id=<?php echo $task['id']; ?>"
                                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            Editar
                                        </a>
                                        <?php if($_SESSION['role'] === 'admin'): ?>
                                        <button onclick="deleteTask(<?php echo $task['id']; ?>)"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Eliminar
                                        </button>
                                        <button onclick="archiveTask(<?php echo $task['id']; ?>)"
                                                class="archive-button inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-yellow-600 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                style="display: <?php echo $task['status'] === 'completed' ? 'inline-flex' : 'none'; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Archivar
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                          <td colspan="6" class="px-6 py-4 text-right text-sm font-medium text-gray-900"></td>
                          <td colspan="2" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total General:</td>
                          <td colspan="6" class="px-6 py-4 text-left whitespace-nowrap text-sm font-medium text-gray-900"> $<?php echo number_format($total, 2); ?>
                             </td>
                         </tr>
                     </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="/bolt/assets/js/main.js"></script>
    <script>
       function deleteTask(id) {
            if (confirm('¿Está seguro de que desea eliminar esta tarea?')) {
                fetch(`/bolt/pages/tasks/delete.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error al eliminar la tarea');
                    }
                });
            }
        }

        function archiveTask(id) {
            if (confirm('¿Está seguro de que desea archivar esta tarea?')) {
                fetch(`/bolt/pages/tasks/archive.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error al archivar la tarea');
                    }
                });
            }
        }

        // Actualizar estado de subtareas
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.subtask-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.dataset.taskId;
                const subtaskId = this.dataset.subtaskId;
                const completed = this.checked;

                // Actualizar el estado de la subtarea en la base de datos
                fetch('/bolt/pages/tasks/update_subtask.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}&subtask_id=${subtaskId}&completed=${completed ? 1 : 0}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar el progreso visual
                        const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                        const progressElement = row.querySelector('.task-progress');
                        progressElement.textContent = `${data.completed_subtasks}/${data.total_subtasks}`;

                        // Actualizar el estado de la tarea si es necesario
                        if (data.should_update_status) {
                            const statusSelect = row.querySelector('.status-select');
                            statusSelect.value = data.completed_subtasks === data.total_subtasks ? 'completed' : 'pending';
                            updateTaskStatus(taskId, statusSelect.value);
                        }
                    }
                });
            });
        });
    });
function updateTaskStatus(taskId, status) {
    const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
    if (!row) return;

    // Remove existing status classes
    row.classList.remove('task-pending', 'task-problems', 'task-completed'); // Corrected class name
    
    // Add new status class
    row.classList.add(`task-${status.toLowerCase()}`);

    // Show/hide archive button
    const archiveButton = row.querySelector('.archive-button');
    if (archiveButton) {
        archiveButton.style.display = status === 'completed' ? 'inline-flex' : 'none'; //Corrected status
    }

    // Update status in database
    fetch('/bolt/pages/tasks/update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&status=${status}`
    });
}

    // Admin user filter change
    function filterTasksByUser() {
        const userId = document.getElementById('user-filter').value;
        if (userId) {
            window.location.href = `/bolt/pages/tasks/list.php?user_id=${userId}`;
        } else {
            window.location.href = '/bolt/pages/tasks/list.php';
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
   
function deleteTaskPhoto(taskId, photoType) {
    if (!confirm('¿Está seguro de que desea eliminar esta foto?')) return;

    fetch('/bolt/pages/tasks/delete_task_photo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&photo_type=${photoType}`
    })
    .then(response => {
        if (!response.ok) { // Check for HTTP errors (404, 500, etc.)
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Find correct container using data-task-id and photoType
            const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
            const cell = row.querySelector(`.photo-upload-container[data-photo-type="${photoType}"]`);

            // Recreate upload button
            cell.innerHTML = `
                <input type="file"
                       id="${photoType}_${taskId}"
                       class="hidden"
                       accept="image/*"
                       onchange="handlePhotoUpload(this, ${taskId}, '${photoType}')" />
                <button onclick="document.getElementById('${photoType}_${taskId}').click()"
                        class="btn-upload-photo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
        } else {
            alert('Error al eliminar la foto: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error en la red. Por favor, intente de nuevo.'); // Consistent error message
    });
}

    function handlePhotoUpload(input, taskId, photoType) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('task_id', taskId);
        formData.append('photo_type', photoType);

        fetch('/bolt/pages/tasks/upload_task_photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find correct container using data-photo-type
                const cell = input.closest('.photo-upload-container');
                cell.dataset.photoType = photoType; // Set data-photo-type

                // Create photo container
                const photoActions = document.createElement('div');
                photoActions.className = 'photo-actions';

                // Create thumbnail
                const thumbnail = document.createElement('img');
                thumbnail.src = data.photo_url;
                thumbnail.alt = photoType === 'before_photo' ? 'Foto Antes' : 'Foto Después';
                thumbnail.className = 'photo-thumbnail';
                thumbnail.onclick = () => openModal(data.photo_url); // Open modal on click

                // Create delete button
                const deleteButton = document.createElement('button');
                deleteButton.className = 'text-red-600 hover:text-red-900 text-xs';
                deleteButton.textContent = 'Eliminar';
                deleteButton.onclick = () => deleteTaskPhoto(taskId, photoType);

                // Assemble structure
                photoActions.appendChild(thumbnail);
                photoActions.appendChild(deleteButton);

                // Clear and add new elements
                cell.innerHTML = '';
                cell.appendChild(photoActions);
            } else {
                alert('Error al subir la foto: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de red al subir la foto.');
        });
    }

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


// Inicialización de eventos
document.addEventListener('DOMContentLoaded', () => {
    // Eventos para subtareas
    const checkboxes = document.querySelectorAll('.subtask-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const subtaskId = this.dataset.subtaskId;
            const completed = this.checked;

            fetch('/bolt/pages/tasks/update_subtask.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${taskId}&subtask_id=${subtaskId}&completed=${completed ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                    const progressElement = row.querySelector('.task-progress');
                    progressElement.textContent = `${data.completed_subtasks}/${data.total_subtasks}`;

                    if (data.should_update_status) {
                        const statusSelect = row.querySelector('.status-select');
                        statusSelect.value = data.completed_subtasks === data.total_subtasks ? 'completed' : 'pending';
                        updateTaskStatus(taskId, statusSelect.value);
                    }
                }
            });
        });
    });
});


function saveNotes(taskId) {
    const textarea = document.getElementById(`notes-${taskId}`);
    const notes = textarea.value;

    fetch('/bolt/pages/tasks/update_notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${taskId}&notes=${encodeURIComponent(notes)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar una notificación de éxito
            alert('Notas guardadas correctamente');
        } else {
            alert('Error al guardar las notas: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar las notas');
    });
}

function clearNotes(taskId) {
    if (confirm('¿Está seguro de que desea borrar las notas?')) {
        const textarea = document.getElementById(`notes-${taskId}`);
        textarea.value = '';
        saveNotes(taskId); // Guardar el campo vacío
    }
}

        </script>
    </body>
    </html>
