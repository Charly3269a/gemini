<?php
$password = "AndresPiscinas!"; // Replace with the actual password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo $hashedPassword;
?>