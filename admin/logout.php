<?php
require_once '../includes/config.php';

// Destruir sesion
session_destroy();

// Redirigir al login
header('Location: login.php');
exit;
