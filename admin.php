<?php
require_once __DIR__ . '/config/db.php';
requireLogin('login.php');

if (!isAdmin()) {
    setFlash('error', 'Access denied. Administrator privileges required.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Security Admin Panel';

// Handle admin actions (Delete Item or Toggle User Role)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_item_id'])) {
        $delId = (int)$_POST['delete_item_id'];
        $itemStmt = $pdo->prepare("SELECT image_path FROM items WHERE id = ?");
        $itemStmt->execute([$delId]);
        $row = $itemStmt->fetch();
        if ($row && !empty($row['image_path']) && file_exists(__DIR__ . '/' . $row['image_path'])) {
            @unlink(__DIR__ . '/' . $row['image_path']);
        }
        $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$delId]);
        setFlash('success', "Item #{$delId} removed successfully by admin.");
        header('Location: admin.php');
        exit;
    }
}

// Fetch all items
$allItems = $pdo->query("
    SELECT items.*, users.full_name, users.student_id 
    FROM items 
    JOIN users ON items.user_id = users.id 
    ORDER BY items.created_at DESC
")->fetchAll();

// Fetch all users
$allUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container" style="margin-top: 2rem; margin-bottom: 3rem;">
    <!-- Flash Messages -->
    <?php if ($success = getFlash('success')): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error = getFlash('error')): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--gray-900);">
            <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Campus Security &bull; Admin Console
        </h1>
        <p style="color: var(--gray-500); font-size: 0.95rem;">
            Moderation center for overseeing reported campus belongings, student accounts, and dispute resolutions.
        </p>
    </div>

    <!-- Items Management Table -->
    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--gray-800);">
        <i class="fa-solid fa-list-check"></i> All Campus Listings (<?= count($allItems) ?>)
    </h3>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Reported By</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allItems as $item): ?>
                    <tr>
                        <td>#<?= $item['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                        </td>
                        <td>
                            <span class="badge-tag <?= $item['type'] === 'lost' ? 'badge-lost' : 'badge-found' ?>" style="position: static;">
                                <?= strtoupper(htmlspecialchars($item['type'])) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td>
                            <?= htmlspecialchars($item['full_name']) ?><br>
                            <small style="color: var(--gray-500);"><?= htmlspecialchars($item['student_id']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($item['location']) ?></td>
                        <td>
                            <span class="item-status-pill status-<?= htmlspecialchars($item['status']) ?>">
                                <?= ucfirst(htmlspecialchars($item['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="item-details.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm" title="Inspect">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this listing as Admin?');">
                                    <input type="hidden" name="delete_item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Registered Users Table -->
    <h3 style="font-size: 1.25rem; margin-top: 2.5rem; margin-bottom: 1rem; color: var(--gray-800);">
        <i class="fa-solid fa-users"></i> Registered Students &amp; Staff (<?= count($allUsers) ?>)
    </h3>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><code><?= htmlspecialchars($u['student_id']) ?></code></td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td>
                            <span class="user-badge" style="<?= $u['role'] === 'admin' ? 'background:#fee2e2; color:#dc2626;' : '' ?>">
                                <?= ucfirst(htmlspecialchars($u['role'])) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
