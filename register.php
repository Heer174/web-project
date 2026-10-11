<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Student Registration';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$student_id = '';
$full_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($student_id)) $errors[] = "Student ID / Roll Number is required.";
    if (empty($full_name))  $errors[] = "Full Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid university/personal email is required.";
    if (empty($phone))      $errors[] = "Contact phone number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check if student_id or email already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE student_id = ? OR email = ?");
        $checkStmt->execute([$student_id, $email]);
        if ($checkStmt->fetch()) {
            $errors[] = "An account with this Student ID or Email already exists.";
        } else {
            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("
                INSERT INTO users (student_id, full_name, email, phone, password, role) 
                VALUES (?, ?, ?, ?, ?, 'student')
            ");
            $insertStmt->execute([$student_id, $full_name, $email, $phone, $hashedPassword]);

            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['student_id'] = $student_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'student';

            setFlash('success', "Registration successful! Welcome to CampusFind, {$full_name}.");
            header('Location: index.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
    <div class="form-card">
        <h2 class="form-title"><i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> Student Registration</h2>
        <p class="form-subtitle">Create an account to report lost or found items and submit ownership claims.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <div>
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID / Roll No. *</label>
                    <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" placeholder="e.g. STU2024-001" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>" placeholder="e.g. John Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Campus / Personal Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. john@campus.edu" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="e.g. 555-0199" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password (min 6 characters) *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="fa-solid fa-check"></i> Register Account
            </button>
        </form>

        <p style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: #64748b;">
            Already have an account? <a href="login.php" style="font-weight: 600;">Log In here</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
