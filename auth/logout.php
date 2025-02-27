<?php
session_start();
session_destroy();
header('Location: /bolt/auth/login.php');
exit;