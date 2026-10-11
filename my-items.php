<?php
require_once __DIR__ . '/config/db.php';
requireLogin('login.php');

$pageTitle = 'My Items & Claims';
$userId = $_SESSION['user_id'];

// Fetch user's reported items
$myItemsStmt = $pdo->prepare("
    SELECT * FROM items 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$myItemsStmt->execute([$userId]);
$myItems = $myItemsStmt->fetchAll();

// Fetch claims submitted by this user
$myClaimsStmt = $pdo->prepare("
    SELECT claims.*, items.title AS item_title, items.type AS item_type, items.location AS item_location
    FROM claims
    JOIN items ON claims.item_id = items.id
    WHERE claims.claimer_id = ?
    ORDER BY claims.created_at DESC
");
$myClaimsStmt->execute([$userId]);
$myClaims = $myClaimsStmt->fetchAll();

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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--gray-900);">My Dashboard</h1>
            <p style="color: var(--gray-500); font-size: 0.9rem;">Manage items you reported and check status of claims you submitted.</p>
        </div>
        <a href="post-item.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Post New Item
        </a>
    </div>

    <!-- Section 1: My Reported Items -->
    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--gray-800);">
        <i class="fa-solid fa-boxes-stacked" style="color: var(--primary);"></i> Items You Reported (<?= count($myItems) ?>)
    </h3>

    <?php if (empty($myItems)): ?>
        <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); text-align: center; color: var(--gray-500); margin-bottom: 2.5rem;">
            You have not reported any lost or found items yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myItems as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                            </td>
                            <td>
                                <span class="badge-tag <?= $item['type'] === 'lost' ? 'badge-lost' : 'badge-found' ?>" style="position: static;">
                                    <?= strtoupper(htmlspecialchars($item['type'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['location']) ?></td>
                            <td><?= date('M d, Y', strtotime($item['item_date'])) ?></td>
                            <td>
                                <span class="item-status-pill status-<?= htmlspecialchars($item['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($item['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="item-details.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Section 2: Claims I Have Made -->
    <h3 style="font-size: 1.25rem; margin-top: 2rem; margin-bottom: 1rem; color: var(--gray-800);">
        <i class="fa-solid fa-hand-holding-hand" style="color: var(--accent);"></i> Claims You Submitted (<?= count($myClaims) ?>)
    </h3>

    <?php if (empty($myClaims)): ?>
        <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); text-align: center; color: var(--gray-500);">
            You have not submitted any ownership claims for found items yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Target Item</th>
                        <th>Your Proof Details</th>
                        <th>Submitted On</th>
                        <th>Claim Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myClaims as $c): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($c['item_title']) ?></strong><br>
                                <small style="color: var(--gray-500);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($c['item_location']) ?></small>
                            </td>
                            <td style="max-width: 320px;">
                                <?= nl2br(htmlspecialchars($c['proof_description'])) ?>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></td>
                            <td>
                                <span class="item-status-pill status-<?= htmlspecialchars($c['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($c['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="item-details.php?id=<?= $c['item_id'] ?>" class="btn btn-secondary btn-sm">
                                    View Item &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
