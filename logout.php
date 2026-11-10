<?php
require __DIR__ . '/core/core.php';

$_SESSION = [];
session_destroy();

redirect(BASE_URL . '/index.php');
?>
