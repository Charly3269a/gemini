    <?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        error_log("Attempting login for username: " . $username); // Debugging log

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = "Por favor, ingrese usuario y contraseña.";
            header('Location: login.php');
            exit;
        }

        require_once __DIR__ . '/config/database.php';

        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                error_log("User found in database: " . $user['username']); // Debugging log
                if (password_verify($password, $user['password'])) {
                    error_log("Password verification successful for user: " . $user['username']); // Debugging log
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: index.php');
                    exit;
                } else {
                    error_log("Password verification failed for user: " . $user['username']); // Debugging log
                    $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
                    header('Location: login.php');
                    exit;
                }
            } else {
                error_log("User NOT found in database: " . $username); // Debugging log
                $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
                header('Location: login.php');
                exit;
            }
        } catch(PDOException $e) {
            error_log("Database error in authenticate.php: " . $e->getMessage());
            $_SESSION['login_error'] = "Error de conexión.  Por favor, intente de nuevo más tarde.";
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
    ?>
