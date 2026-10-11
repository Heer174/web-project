<?php
// ==========================================================
// Database Configuration & Global Helpers
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_name = 'campus_lost_found';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty, WAMP might be empty or root

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Graceful error display if database is not created yet
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #ffccd5; background: #fff5f5; border-radius: 8px; color: #900;'>
        <h2 style='margin-top:0;'>Database Connection Error</h2>
        <p>Could not connect to the database <code>{$db_name}</code>.</p>
        <p><strong>Troubleshooting:</strong></p>
        <ol>
            <li>Make sure MySQL is running in your XAMPP / WAMP control panel.</li>
            <li>Import the SQL schema file: <code>database/schema.sql</code> via phpMyAdmin.</li>
            <li>Verify database credentials in <code>config/db.php</code>.</li>
        </ol>
        <p style='color: #666; font-size: 13px;'>Raw error: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}

// ----------------------------------------------------------
// Auth & Session Helper Functions
// ----------------------------------------------------------

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'         => $_SESSION['user_id'],
        'student_id' => $_SESSION['student_id'] ?? '',
        'full_name'  => $_SESSION['full_name'] ?? '',
        'email'      => $_SESSION['email'] ?? '',
        'role'       => $_SESSION['role'] ?? 'student'
    ];
}

function requireLogin(string $redirectTo = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: {$redirectTo}");
        exit;
    }
}

function isAdmin(): bool {
    return isLoggedIn() && (($_SESSION['role'] ?? '') === 'admin');
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
