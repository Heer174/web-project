<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Home';

// Fetch stats for the dashboard counters
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) AS total_items,
        SUM(CASE WHEN type = 'lost' AND status = 'active' THEN 1 ELSE 0 END) AS active_lost,
        SUM(CASE WHEN type = 'found' AND status = 'active' THEN 1 ELSE 0 END) AS active_found,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_items
    FROM items
");
$stats = $statsStmt->fetch() ?: ['total_items' => 0, 'active_lost' => 0, 'active_found' => 0, 'resolved_items' => 0];

// Fetch items list
$stmt = $pdo->query("
    SELECT items.*, users.full_name, users.student_id 
    FROM items 
    JOIN users ON items.user_id = users.id 
    ORDER BY items.created_at DESC
");
$items = $stmt->fetchAll();

// Distinct categories
$categories = [
    'Electronics',
    'Student ID & Cards',
    'Books & Stationery',
    'Wallets & Bags',
    'Keys',
    'Clothing & Accessories',
    'Others'
];

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

<div class="hero">
    <div class="container">
        <h1>Lost Something on Campus?</h1>
        <p>A centralized portal for students and faculty to report, search, and reclaim lost belongings across campus buildings and facilities.</p>
        <div class="hero-actions">
            <a href="post-item.php?type=lost" class="btn btn-primary" style="background: #dc2626; border-color: #dc2626;">
                <i class="fa-solid fa-circle-exclamation"></i> Report Lost Item
            </a>
            <a href="post-item.php?type=found" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a;">
                <i class="fa-solid fa-hand-holding-hand"></i> Report Found Item
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Flash Notifications -->
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

    <!-- Live Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-cubes"></i>
            </div>
            <div class="stat-info">
                <h3><?= number_format($stats['total_items'] ?? 0) ?></h3>
                <p>Total Reported</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="stat-info">
                <h3><?= number_format($stats['active_lost'] ?? 0) ?></h3>
                <p>Active Lost Items</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div class="stat-info">
                <h3><?= number_format($stats['active_found'] ?? 0) ?></h3>
                <p>Found Items Waiting</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-info">
                <h3><?= number_format($stats['resolved_items'] ?? 0) ?></h3>
                <p>Items Reunited</p>
            </div>
        </div>
    </div>

    <!-- Live Search & Filtering Toolbar -->
    <div class="filter-card">
        <div class="filter-row">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass icon"></i>
                <input type="text" id="searchQuery" placeholder="Search by item name, location, or keyword...">
            </div>

            <div class="filter-group">
                <div class="pill-group">
                    <button type="button" class="pill-btn active" data-type="all">All Items</button>
                    <button type="button" class="pill-btn" data-type="lost">Lost</button>
                    <button type="button" class="pill-btn" data-type="found">Found</button>
                </div>

                <select id="categoryFilter" class="filter-select">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="item-grid" id="itemsContainer">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h3>No Items Reported Yet</h3>
                <p>Be the first to post a lost or found item to help our campus community.</p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="item-card" 
                     data-type="<?= htmlspecialchars($item['type']) ?>" 
                     data-category="<?= htmlspecialchars($item['category']) ?>"
                     data-title="<?= htmlspecialchars($item['title']) ?>"
                     data-location="<?= htmlspecialchars($item['location']) ?>"
                     data-desc="<?= htmlspecialchars($item['description']) ?>">
                    
                    <div class="item-image-wrapper">
                        <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="item-image">
                        <?php else: ?>
                            <i class="fa-solid <?= getCategoryIcon($item['category']) ?> item-placeholder-icon"></i>
                        <?php endif; ?>

                        <span class="badge-tag <?= $item['type'] === 'lost' ? 'badge-lost' : 'badge-found' ?>">
                            <?= strtoupper(htmlspecialchars($item['type'])) ?>
                        </span>
                        <span class="badge-category">
                            <?= htmlspecialchars($item['category']) ?>
                        </span>
                    </div>

                    <div class="item-content">
                        <h4 class="item-title" title="<?= htmlspecialchars($item['title']) ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </h4>
                        <p class="item-desc">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>

                        <div class="item-meta">
                            <div class="meta-row">
                                <i class="fa-solid fa-location-dot" style="color: #ef4444; width: 14px;"></i>
                                <span title="<?= htmlspecialchars($item['location']) ?>"><?= htmlspecialchars($item['location']) ?></span>
                            </div>
                            <div class="meta-row">
                                <i class="fa-solid fa-calendar-day" style="color: #3b82f6; width: 14px;"></i>
                                <span>Date: <?= date('M d, Y', strtotime($item['item_date'])) ?></span>
                            </div>
                        </div>

                        <div class="item-footer">
                            <span class="item-status-pill status-<?= htmlspecialchars($item['status']) ?>">
                                <?= ucfirst(htmlspecialchars($item['status'])) ?>
                            </span>
                            <a href="item-details.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Hidden container for JS when filtering finds nothing -->
        <div id="noResultsMessage" class="empty-state" style="display: none;">
            <div class="empty-state-icon">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </div>
            <h3>No Matching Items</h3>
            <p>Try refining your search keyword or switching category filters.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
