<?php
// Database configuration
define('DB_HOST', 'db');
define('DB_USER', 'attendance_user');
define('DB_PASS', 'attendance_pass');
define('DB_NAME', 'attendance_db');

// VULNERABILITY: Hardcoded encryption key (also exposed via phpinfo as env var)
define('ENCRYPTION_KEY', 'secret123');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// VULNERABILITY: Weak XOR-based "encryption" using hardcoded key
function encryptData($data) {
    $key = ENCRYPTION_KEY;
    $encrypted = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $encrypted .= $data[$i] ^ $key[$i % strlen($key)];
    }
    return base64_encode($encrypted);
}

function decryptData($data) {
    $key = ENCRYPTION_KEY;
    $decoded = base64_decode($data);
    $decrypted = '';
    for ($i = 0; $i < strlen($decoded); $i++) {
        $decrypted .= $decoded[$i] ^ $key[$i % strlen($key)];
    }
    return $decrypted;
}
?>
