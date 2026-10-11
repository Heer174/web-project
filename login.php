<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Login';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$login_identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['login_identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_identifier) || empty($password)) {
        $error = "Please enter both Email/Student ID and Password.";
    } else {
        // Query user by email OR student_id
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        $stmt->execute([$login_identifier, $login_identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password verified
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            setFlash('success', "Welcome back, {$user['full_name']}!");
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid credentials. Please verify your student ID/email and password.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
    <div class="form-card" style="max-width: 500px;">
        <h2 class="form-title"><i class="fa-solid fa-arrow-right-to-bracket" style="color: var(--primary);"></i> Portal Login</h2>
        <p class="form-subtitle">Log in using your registered Email or Student ID.</p>

        <?php if ($flashErr = getFlash('error')): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashErr) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="login_identifier">Email or Student ID</label>
                <input type="text" id="login_identifier" name="login_identifier" value="<?= htmlspecialchars($login_identifier) ?>" placeholder="e.g. STU202401 or alex.j@campus.edu" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="fa-solid fa-lock-open"></i> Log In
            </button>
        </form>

        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-top: 1.5rem; font-size: 0.85rem; color: #475569;">
            <strong><i class="fa-solid fa-key"></i> Demo Credentials for Testing / Review:</strong>
            <ul style="margin: 6px 0 0 20px; padding: 0;">
                <li><strong>Student:</strong> <code>alex.j@campus.edu</code> &bull; Pass: <code>password123</code></li>
                <li><strong>Admin:</strong> <code>admin@campus.edu</code> &bull; Pass: <code>password123</code></li>
            </ul>
        </div>

        <p style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: #64748b;">
            Don't have an account? <a href="register.php" style="font-weight: 600;">Register as Student</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
