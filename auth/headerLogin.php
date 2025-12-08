<?php
$logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$is_admin = $logged_in && isset($_SESSION["role"]) && $_SESSION["role"] == 'admin';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">