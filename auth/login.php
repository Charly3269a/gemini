    <?php
    session_start();
    require_once '../config/database.php';

    error_log("login.php: Login page loaded."); // Log when login page is loaded

    // Si ya está logueado, redirigir al dashboard
    if (isset($_SESSION['user_id'])) {
        error_log("login.php: User already logged in (User ID: " . $_SESSION['user_id'] . "). Redirecting to /bolt/"); // Log if already logged in
        header('Location: /bolt/');
        exit;
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Por favor complete todos los campos.';
        } else {
            try {
                // Verificar la conexión a la base de datos
                if (!$pdo) {
                    error_log('login.php: Database connection failed.');
                    $error = 'Error de conexión a la base de datos';
                } else {
                    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?"); // Select role here
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();

                    // Debug: Imprimir información detallada
                    error_log('login.php: Login attempt - Username: ' . $username);
                    if ($user) {
                        error_log('login.php: User found in database - User ID: ' . $user['id'] . ", Role: " . $user['role']); // Log role here
                        error_log('login.php: Stored password hash: ' . $user['password']);
                        if (password_verify($password, $user['password'])) {
                            error_log('login.php: Password verification: Successful');
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $user['role']; // Ensure session role is set here!
                            error_log("login.php: Session set. Redirecting to /bolt/"); // Log before redirect
                            header('Location: /bolt/');
                            exit;
                        } else {
                            error_log('login.php: Password verification: Failed');
                            $error = 'Usuario o contraseña incorrectos.';
                        }
                    } else {
                        error_log('login.php: User NOT found in database'); // Log user not found
                        $error = 'Usuario o contraseña incorrectos.'; // Keep error message
                    }
                    // No else here, error message already set in detailed logging block
                }
            } catch (PDOException $e) {
                error_log('login.php: Error PDO: ' . $e->getMessage());
                $error = 'Error al intentar iniciar sesión: ' . $e->getMessage();
            }
        }
    }

    // Debug: Verificar si la tabla existe
    try {
        $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
        error_log('login.php: Tables found: ' . print_r($tables, true));
    } catch (PDOException $e) {
        error_log('login.php: Error verifying tables: ' . $e->getMessage());
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión - Sistema de Gestión</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Sistema de Gestión</h2>
                    <p class="text-gray-600 mt-2">Inicie sesión para continuar</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                        <input type="text" id="username" name="username" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" id="password" name="password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Iniciar Sesión
                    </button>
                 <div class="text-sm text-gray-500">
                        ¿No tiene una cuenta?
                        <a href="adduser.php" class="font-medium text-blue-600 hover:text-blue-500">
                            Registrarse
                        </a>
                    </div>
                 </form>
             </div>
         </div>
     </body>
     </html>
