<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

function isTeacher() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'teacher';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php?page=login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        exit('Access denied');
    }
}

function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        http_response_code(403);
        exit('Access denied');
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
