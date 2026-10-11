<?php
require_once __DIR__ . '/../config/db.php';
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-compass"></i>
            </div>
            <span>CampusFind</span>
        </a>

        <ul class="nav-links">
            <li>
                <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </li>
            <?php if ($user): ?>
                <li>
                    <a href="my-items.php" class="<?= $currentPage === 'my-items.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-box-archive"></i> My Items & Claims
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                    <li>
                        <a href="admin.php" class="<?= $currentPage === 'admin.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-shield-halved"></i> Admin Panel
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="post-item.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Report Item
            </a>

            <?php if ($user): ?>
                <span class="user-badge" title="Student ID: <?= htmlspecialchars($user['student_id']) ?>">
                    <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
                </span>
                <a href="logout.php" class="btn btn-secondary btn-sm" title="Log out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In
                </a>
                <a href="register.php" class="btn btn-primary btn-sm" style="background:#1e293b; border-color:#1e293b;">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
