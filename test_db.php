<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "<h1>Database connection successful!</h1>";
    } else {
        echo "<h1>Database connection failed (but no exception).</h1>";
    }
} catch (PDOException $e) {
    echo "<h1>Database connection failed!</h1>";
    echo "<p>Error message: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>";
}
?>