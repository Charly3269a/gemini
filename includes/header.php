<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <a href="/bolt/" class="text-xl font-bold text-gray-800">Sistema de Gestión</a>
            
             <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="hidden md:flex space-x-6">
                <button id="open-adduser-modal" class="text-gray-600 hover:text-gray-900">
                    Agregar Usuario
               </button>
                <a href="/bolt/pages/users/list.php" class="text-gray-600 hover:text-gray-900">Lista de Usuarios</a>
                <a href="/bolt/pages/clients/add.php" class="text-gray-600 hover:text-gray-900">Agregar Cliente</a>
                <a href="/bolt/pages/clients/list.php" class="text-gray-600 hover:text-gray-900">Lista de Clientes</a>
                <a href="/bolt/pages/tasks/add.php" class="text-gray-600 hover:text-gray-900">Agregar Tarea</a>
                <a href="/bolt/pages/tasks/list.php" class="text-gray-600 hover:text-gray-900">Tareas</a>
                <a href="/bolt/pages/tasks/archived.php" class="text-gray-600 hover:text-gray-900">Historial</a>
                <a href="/bolt/auth/logout.php" class="text-red-600 hover:text-red-900">Cerrar Sesión</a>
            </div>

            <button class="md:hidden" id="mobile-menu-button">
                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
 <?php else: ?>
                 <a href="/bolt/pages/clients/list.php" class="text-gray-600 hover:text-gray-900">Lista de Clientes</a>
                <a href="/bolt/pages/tasks/list.php" class="text-gray-600 hover:text-gray-900">Tareas</a>
                <a href="/bolt/pages/tasks/archived.php" class="text-gray-600 hover:text-gray-900">Historial</a>
                <a href="/bolt/auth/logout.php" class="text-red-600 hover:text-red-900">Cerrar Sesión</a>
                
                
                <?php endif; ?>
                
                
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="py-2 space-y-2">
                <div class="py-2 space-y-2">
                   <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
               <button id="open-adduser-modal-mobile" class="block text-gray-600 hover:text-gray-900 py-2">
                  Agregar Usuario
                </button>
                <a href="/bolt/pages/users/list.php" class="text-gray-600 hover:text-gray-900">Lista de Usuarios</a>
                <a href="/bolt/pages/clients/add.php" class="block text-gray-600 hover:text-gray-900 py-2">Agregar Cliente</a>
                <a href="/bolt/pages/clients/list.php" class="block text-gray-600 hover:text-gray-900 py-2">Lista de Clientes</a>
                <a href="/bolt/pages/tasks/add.php" class="block text-gray-600 hover:text-gray-900 py-2">Agregar Tarea</a>
                <a href="/bolt/pages/tasks/list.php" class="block text-gray-600 hover:text-gray-900 py-2">Tareas</a>
                <a href="/bolt/pages/tasks/archived.php" class="block text-gray-600 hover:text-gray-900 py-2">Historial</a>
                <a href="/bolt/auth/logout.php" class="block text-red-600 hover:text-red-900 py-2">Cerrar Sesión</a>
            </div>
 <?php else: ?>
                <a href="/bolt/pages/clients/list.php" class="block text-gray-600 hover:text-gray-900 py-2">Lista de Clientes</a>
                <a href="/bolt/pages/tasks/list.php" class="block text-gray-600 hover:text-gray-900 py-2">Tareas</a>
                <a href="/bolt/pages/tasks/archived.php" class="block text-gray-600 hover:text-gray-900 py-2">Historial</a>
                <a href="/bolt/auth/logout.php" class="block text-red-600 hover:text-red-900 py-2">Cerrar Sesión</a>
                 <?php endif; ?>
        </div>
    </div>
</nav>
