<?php
require_once __DIR__ . '/config/db.php';

$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$itemId) {
    setFlash('error', 'Invalid item requested.');
    header('Location: index.php');
    exit;
}

// Fetch item with poster details
$stmt = $pdo->prepare("
    SELECT items.*, users.full_name, users.student_id, users.email AS user_email
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE items.id = ?
");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Item not found or has been removed.');
    header('Location: index.php');
    exit;
}

$pageTitle = $item['title'];
$currentUser = currentUser();
$isOwner = $currentUser && ($currentUser['id'] == $item['user_id']);
$canManage = $isOwner || isAdmin();

// Handle Status Updates (Resolved / Active / Delete) by Owner or Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $canManage) {
    $action = $_POST['action'];

    if ($action === 'mark_resolved') {
        $updateStmt = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
        $updateStmt->execute([$itemId]);
        setFlash('success', 'Item marked as Resolved / Reunited successfully!');
        header("Location: item-details.php?id={$itemId}");
        exit;
    } elseif ($action === 'mark_active') {
        $updateStmt = $pdo->prepare("UPDATE items SET status = 'active' WHERE id = ?");
        $updateStmt->execute([$itemId]);
        setFlash('success', 'Item status updated to Active.');
        header("Location: item-details.php?id={$itemId}");
        exit;
    } elseif ($action === 'delete_item') {
        // Delete image file if exists
        if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])) {
            @unlink(__DIR__ . '/' . $item['image_path']);
        }
        $delStmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $delStmt->execute([$itemId]);
        setFlash('success', 'Item post deleted successfully.');
        header('Location: index.php');
        exit;
    }
}

// Handle Claim Submissions by other logged-in users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_claim'])) {
    requireLogin('login.php');

    $proof_description = trim($_POST['proof_description'] ?? '');
    $contact_info      = trim($_POST['contact_info'] ?? '');

    if (empty($proof_description) || empty($contact_info)) {
        setFlash('error', 'Please provide both ownership proof details and your contact information.');
    } else {
        $claimStmt = $pdo->prepare("
            INSERT INTO claims (item_id, claimer_id, proof_description, contact_info, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $claimStmt->execute([$itemId, $_SESSION['user_id'], $proof_description, $contact_info]);
        setFlash('success', 'Your ownership claim has been submitted! The reporter will review your proof.');
        header("Location: item-details.php?id={$itemId}");
        exit;
    }
}

// Handle Claim Approvals/Rejections by Owner or Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'], $_POST['claim_status']) && $canManage) {
    $claimId = (int)$_POST['claim_id'];
    $claimStatus = in_array($_POST['claim_status'], ['approved', 'rejected']) ? $_POST['claim_status'] : 'pending';

    $updateClaimStmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ? AND item_id = ?");
    $updateClaimStmt->execute([$claimStatus, $claimId, $itemId]);

    if ($claimStatus === 'approved') {
        // Automatically mark the item as resolved when a claim is approved
        $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ?")->execute([$itemId]);
        setFlash('success', 'Claim approved! Item has been marked as Resolved.');
    } else {
        setFlash('success', 'Claim has been marked as rejected.');
    }
    header("Location: item-details.php?id={$itemId}");
    exit;
}

// Fetch all claims for this item (if owner or admin)
$claims = [];
if ($canManage) {
    $claimsStmt = $pdo->prepare("
        SELECT claims.*, users.full_name AS claimer_name, users.student_id AS claimer_student_id, users.email AS claimer_email
        FROM claims
        JOIN users ON claims.claimer_id = users.id
        WHERE claims.item_id = ?
        ORDER BY claims.created_at DESC
    ");
    $claimsStmt->execute([$itemId]);
    $claims = $claimsStmt->fetchAll();
}

function getCategoryIcon(string $category): string {
    return match ($category) {
        'Electronics' => 'fa-laptop',
        'Student ID & Cards' => 'fa-id-card',
        'Books & Stationery' => 'fa-book',
        'Wallets & Bags' => 'fa-wallet',
        'Keys' => 'fa-key',
        'Clothing & Accessories' => 'fa-shirt',
        default => 'fa-box',
    };
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
    <!-- Flash Messages -->
    <?php if ($success = getFlash('success')): ?>
        <div class="alert alert-success" style="margin-top: 1.5rem;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error = getFlash('error')): ?>
        <div class="alert alert-danger" style="margin-top: 1.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 1.5rem;">
        <a href="index.php" style="color: var(--gray-600); font-size: 0.9rem;">
            &larr; Back to Item Catalog
        </a>
    </div>

    <!-- Main Item Details Layout -->
    <div class="detail-layout">
        <!-- Left: Image Gallery / Graphic -->
        <div class="detail-gallery">
            <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="detail-img">
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 1rem; color: var(--gray-400);">
                    <i class="fa-solid <?= getCategoryIcon($item['category']) ?>" style="font-size: 5rem; margin-bottom: 1rem;"></i>
                    <p style="font-size: 0.9rem; color: var(--gray-500);">No photo attached for this report</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Information & Actions -->
        <div class="detail-info">
            <div style="display: flex; gap: 8px; margin-bottom: 0.75rem;">
                <span class="badge-tag <?= $item['type'] === 'lost' ? 'badge-lost' : 'badge-found' ?>" style="position: static;">
                    <?= strtoupper(htmlspecialchars($item['type'])) ?> ITEM
                </span>
                <span class="item-status-pill status-<?= htmlspecialchars($item['status']) ?>">
                    Status: <?= ucfirst(htmlspecialchars($item['status'])) ?>
                </span>
            </div>

            <h1><?= htmlspecialchars($item['title']) ?></h1>

            <table class="detail-meta-table">
                <tr>
                    <td class="label"><i class="fa-solid fa-tag"></i> Category</td>
                    <td class="val"><?= htmlspecialchars($item['category']) ?></td>
                </tr>
                <tr>
                    <td class="label"><i class="fa-solid fa-location-dot"></i> Location</td>
                    <td class="val"><strong><?= htmlspecialchars($item['location']) ?></strong></td>
                </tr>
                <tr>
                    <td class="label"><i class="fa-solid fa-calendar-days"></i> Date Event</td>
                    <td class="val"><?= date('F d, Y', strtotime($item['item_date'])) ?></td>
                </tr>
                <tr>
                    <td class="label"><i class="fa-solid fa-user"></i> Reported By</td>
                    <td class="val">
                        <?= htmlspecialchars($item['full_name']) ?> 
                        <span style="color: var(--gray-400); font-size: 0.85rem;">(ID: <?= htmlspecialchars($item['student_id']) ?>)</span>
                    </td>
                </tr>
                <tr>
                    <td class="label"><i class="fa-solid fa-phone"></i> Contact</td>
                    <td class="val">
                        <a href="tel:<?= htmlspecialchars($item['contact_phone']) ?>">
                            <i class="fa-solid fa-phone-volume"></i> <?= htmlspecialchars($item['contact_phone']) ?>
                        </a>
                    </td>
                </tr>
            </table>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--gray-800); margin-bottom: 0.5rem;">Description &amp; Notes:</h4>
                <div style="background: var(--gray-50); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--gray-200); line-height: 1.6; color: var(--gray-700);">
                    <?= nl2br(htmlspecialchars($item['description'])) ?>
                </div>
            </div>

            <!-- Action Area -->
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <?php if ($canManage): ?>
                    <!-- Owner / Admin Controls -->
                    <?php if ($item['status'] !== 'resolved'): ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="mark_resolved">
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark this item as resolved/reunited?');">
                                <i class="fa-solid fa-circle-check"></i> Mark as Resolved / Returned
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="mark_active">
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-rotate-left"></i> Re-open as Active
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="delete_item">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this listing permanently?');">
                            <i class="fa-solid fa-trash"></i> Delete Post
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Other Students: Claim Item -->
                    <?php if ($item['status'] === 'active'): ?>
                        <button type="button" id="openClaimModalBtn" class="btn btn-primary">
                            <i class="fa-solid fa-hand-holding-hand"></i> Claim This Item (Verify Ownership)
                        </button>
                    <?php else: ?>
                        <div class="alert alert-success" style="margin: 0; padding: 0.5rem 1rem;">
                            <i class="fa-solid fa-check-double"></i> This item is already marked as resolved or returned.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Owner / Admin Claims Review Section -->
    <?php if ($canManage): ?>
        <div style="margin-top: 3rem; margin-bottom: 3rem;">
            <h3 style="font-size: 1.35rem; margin-bottom: 1rem; color: var(--gray-900);">
                <i class="fa-solid fa-clipboard-check"></i> Submitted Ownership Claims (<?= count($claims) ?>)
            </h3>

            <?php if (empty($claims)): ?>
                <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); text-align: center; color: var(--gray-500);">
                    No one has submitted a claim for this item yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Claimer</th>
                                <th>Proof of Ownership</th>
                                <th>Contact Details</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $c): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($c['claimer_name']) ?></strong><br>
                                        <small style="color: var(--gray-500);"><?= htmlspecialchars($c['claimer_student_id']) ?></small>
                                    </td>
                                    <td style="max-width: 320px;">
                                        <?= nl2br(htmlspecialchars($c['proof_description'])) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($c['contact_info']) ?><br>
                                        <small><?= htmlspecialchars($c['claimer_email']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('M d, Y h:i A', strtotime($c['created_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="item-status-pill status-<?= htmlspecialchars($c['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($c['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($c['status'] === 'pending'): ?>
                                            <div style="display: flex; gap: 6px;">
                                                <form method="POST" style="margin: 0;">
                                                    <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="claim_status" value="approved">
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this claim? This marks the item as resolved.');">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" style="margin: 0;">
                                                    <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="claim_status" value="rejected">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this claim?');">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: var(--gray-400);">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Claim / Proof of Ownership Form -->
<div id="claimModal" class="modal-overlay">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900);">
                <i class="fa-solid fa-hand-holding-hand" style="color: var(--primary);"></i> Claim This Item
            </h3>
            <button type="button" id="closeClaimModalBtn" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--gray-400);">&times;</button>
        </div>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 1.25rem;">
            To verify you are the rightful owner, describe unique features not mentioned in the public listing (e.g. wallpaper, serial digits, inside contents, unique marks).
        </p>

        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-danger">
                Please <a href="login.php" style="text-decoration: underline; font-weight: bold;">log in</a> with your student account first to submit a claim.
            </div>
        <?php else: ?>
            <form method="POST" action="item-details.php?id=<?= $item['id'] ?>">
                <input type="hidden" name="submit_claim" value="1">
                
                <div class="form-group">
                    <label for="proof_description">Proof of Ownership / Identifiers *</label>
                    <textarea id="proof_description" name="proof_description" rows="4" placeholder="e.g. My lock screen shows a mountain, the inside compartment contains a student card ending in 9021, blue scratch on back..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="contact_info">Your Best Contact Info (Phone / Email) *</label>
                    <input type="text" id="contact_info" name="contact_info" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Submit Ownership Claim
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
