<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/auth.php';

$user = current_user();
$flash = get_flash();
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$prefix = in_array($currentDir, ['student', 'staff', 'admin'], true) ? '../' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $prefix ?>assets/css/style.css">
</head>
<body>
<div class="bg-shape bg-shape-one"></div>
<div class="bg-shape bg-shape-two"></div>
<header class="site-header">
    <div class="container nav-wrap">
        <a href="<?= $prefix ?>index.php" class="brand" aria-label="DIU Hostel Care Home">
            <img class="brand-logo" src="https://daffodilvarsity.edu.bd/images/logo.svg" alt="Daffodil International University Logo">
            <span class="brand-text">Hostel Care</span>
        </a>
        <nav class="main-nav">
            <a href="<?= $prefix ?>index.php">Home</a>
            <?php if ($user): ?>
                <a href="<?= $prefix ?>dashboard.php">Dashboard</a>
                <a href="<?= $prefix ?>logout.php">Logout</a>
            <?php else: ?>
                <a href="<?= $prefix ?>login.php">Login</a>
                <a href="<?= $prefix ?>register.php" class="btn-link">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container page-main">
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
