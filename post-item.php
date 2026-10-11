<?php
require_once __DIR__ . '/config/db.php';
requireLogin('login.php');

$pageTitle = 'Report Lost or Found Item';
$errors = [];

$type = isset($_GET['type']) && in_array($_GET['type'], ['lost', 'found']) ? $_GET['type'] : 'lost';
$title = '';
$category = 'Electronics';
$location = '';
$item_date = date('Y-m-d');
$contact_phone = '';
$description = '';

// Pre-fill phone from logged in user if available
$userStmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userRow = $userStmt->fetch();
if ($userRow && !empty($userRow['phone'])) {
    $contact_phone = $userRow['phone'];
}

$categories = [
    'Electronics',
    'Student ID & Cards',
    'Books & Stationery',
    'Wallets & Bags',
    'Keys',
    'Clothing & Accessories',
    'Others'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type          = in_array($_POST['type'] ?? '', ['lost', 'found']) ? $_POST['type'] : 'lost';
    $title         = trim($_POST['title'] ?? '');
    $category      = in_array($_POST['category'] ?? '', $categories) ? $_POST['category'] : 'Others';
    $location      = trim($_POST['location'] ?? '');
    $item_date     = trim($_POST['item_date'] ?? date('Y-m-d'));
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // Validation
    if (empty($title))         $errors[] = "Please provide an item title.";
    if (empty($location))      $errors[] = "Please specify the campus location.";
    if (empty($item_date))     $errors[] = "Please specify the date.";
    if (empty($contact_phone)) $errors[] = "Please provide a contact phone number.";
    if (empty($description))   $errors[] = "Please provide a detailed description.";

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['item_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code: " . $file['error'];
        } else {
            // Check size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = "Uploaded image exceeds the 5MB size limit.";
            }

            // Check mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $allowedMimes)) {
                $errors[] = "Invalid image file type. Allowed: JPG, PNG, WEBP, GIF.";
            } else {
                $uploadsDir = __DIR__ . '/uploads';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueFileName = 'item_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                $destination = $uploadsDir . '/' . $uniqueFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $imagePath = 'uploads/' . $uniqueFileName;
                } else {
                    $errors[] = "Failed to save uploaded image on server.";
                }
            }
        }
    }

    if (empty($errors)) {
        $insertStmt = $pdo->prepare("
            INSERT INTO items (user_id, type, title, category, location, item_date, description, image_path, contact_phone, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $insertStmt->execute([
            $_SESSION['user_id'],
            $type,
            $title,
            $category,
            $location,
            $item_date,
            $description,
            $imagePath,
            $contact_phone
        ]);

        $newId = $pdo->lastInsertId();
        setFlash('success', "Item report created successfully!");
        header("Location: item-details.php?id={$newId}");
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container">
    <div class="form-card">
        <h2 class="form-title">
            <i class="fa-solid fa-file-pen" style="color: var(--primary);"></i> Report an Item
        </h2>
        <p class="form-subtitle">Fill in the item details as accurately as possible to facilitate quick recovery.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="post-item.php" enctype="multipart/form-data">
            <!-- Item Type Selection -->
            <div class="form-group">
                <label>Report Type *</label>
                <div style="display: flex; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; padding: 10px 16px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); flex: 1;">
                        <input type="radio" name="type" value="lost" <?= $type === 'lost' ? 'checked' : '' ?>>
                        <strong style="color: #dc2626;"><i class="fa-solid fa-circle-exclamation"></i> I LOST this item</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; padding: 10px 16px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); flex: 1;">
                        <input type="radio" name="type" value="found" <?= $type === 'found' ? 'checked' : '' ?>>
                        <strong style="color: #16a34a;"><i class="fa-solid fa-hand-holding-hand"></i> I FOUND this item</strong>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Item Name / Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="e.g. Matte Black AirPods Pro (2nd Gen) in White Silicone Case" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="item_date">Date Lost / Found *</label>
                    <input type="date" id="item_date" name="item_date" value="<?= htmlspecialchars($item_date) ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Campus Location *</label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="e.g. Library 2nd Floor, Table #12 or Canteen" required>
                </div>
                <div class="form-group">
                    <label for="contact_phone">Contact Phone Number *</label>
                    <input type="tel" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>" placeholder="e.g. 555-0142" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Detailed Description *</label>
                <textarea id="description" name="description" rows="4" placeholder="Provide distinct features (color, brand, scratches, stickers, contents, or where the item was surrendered / security desk)..." required><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="form-group">
                <label for="itemImageInput">Attach Photograph (Optional, Max 5MB)</label>
                <input type="file" id="itemImageInput" name="item_image" accept="image/png, image/jpeg, image/webp, image/gif">
                <img id="imagePreview" src="#" alt="Selected Image Preview" class="file-preview">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Publish Item Report
                </button>
                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
