<?php
require_once 'db.php';

// VULNERABILITY: Role is stored in a cookie encrypted with weak hardcoded key
// Attacker can find key via /info.php, then forge cookie with "lecturer" role

function setAuthCookie($userId, $role, $username) {
    $cookieData = $userId . '|' . $role . '|' . $username;
    $encrypted = encryptData($cookieData);
    setcookie('auth_token', $encrypted, time() + 3600, '/');
    $_COOKIE['auth_token'] = $encrypted;
}

function getAuthUser() {
    if (!isset($_COOKIE['auth_token'])) {
        return null;
    }
    $decrypted = decryptData($_COOKIE['auth_token']);
    $parts = explode('|', $decrypted);
    if (count($parts) !== 3) {
        return null;
    }
    return [
        'id'       => (int)$parts[0],
        'role'     => $parts[1],
        'username' => $parts[2]
    ];
}

function requireLogin() {
    $user = getAuthUser();
    if (!$user) {
        header('Location: /index.php');
        exit;
    }
    return $user;
}

function requireLecturer() {
    $user = requireLogin();
    // VULNERABILITY: Only checks the (forgeable) cookie value
    if ($user['role'] !== 'lecturer') {
        header('Location: /dashboard.php?error=unauthorized');
        exit;
    }
    return $user;
}

function logout() {
    setcookie('auth_token', '', time() - 3600, '/');
    unset($_COOKIE['auth_token']);
}
?>
