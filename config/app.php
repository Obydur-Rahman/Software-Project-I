<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

const APP_NAME = 'DIU Hostel Complaint Management System';
const BASE_URL = '/Software Project 1';
const ALLOWED_GENDERS = ['male', 'female'];
const ALLOWED_CATEGORIES = ['Electricity', 'Water', 'Internet', 'Cleaning', 'Security', 'Furniture', 'Others'];
const ALLOWED_PRIORITIES = ['Low', 'Medium', 'High'];
const ALLOWED_STATUSES = ['pending', 'in_progress', 'resolved'];

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function request_is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function require_post_request(string $redirectPath): void
{
    if (!request_is_post()) {
        redirect($redirectPath);
    }
}

function fail_and_redirect(string $message, string $redirectPath): void
{
    set_flash('error', $message);
    redirect($redirectPath);
}

function success_and_redirect(string $message, string $redirectPath): void
{
    set_flash('success', $message);
    redirect($redirectPath);
}

function in_allowed_list(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

function post_string(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function post_int(string $key, int $default = 0): int
{
    return (int)($_POST[$key] ?? $default);
}

function get_int(string $key, int $default = 0): int
{
    return (int)($_GET[$key] ?? $default);
}
