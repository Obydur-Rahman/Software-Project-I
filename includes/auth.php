<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        fail_and_redirect('Please log in first.', BASE_URL . '/login.php');
    }
}

function require_role(array $allowedRoles): void
{
    require_login();

    $user = current_user();
    if (!$user || !in_array($user['role'], $allowedRoles, true)) {
        fail_and_redirect('You do not have permission to access that page.', BASE_URL . '/dashboard.php');
    }
}
