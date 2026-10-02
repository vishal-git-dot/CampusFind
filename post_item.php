<?php
require_once 'config.php';
require_login();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

$type = $_GET['type'] ?? 'lost';
if (!in_array($type, ['lost', 'found'], true)) {
    $type = 'lost';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'lost';
    if (!in_array($type, ['lost', 'found'], true)) {
        $type = 'lost';
    }

    $categoryId = (int)($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $itemDate = trim($_POST['item_date'] ?? '');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please refresh the page and try again.';
    }
    if (!$categoryId) {
        $errors[] = 'Please choose a category.';
    }
    if (mb_strlen($title) < 3 || mb_strlen($title) > 160) {
        $errors[] = 'Title must be between 3 and 160 characters.';
    }
    if (mb_strlen($description) < 10) {
        $errors[] = 'Please add at least 10 characters of detail.';
    }
    if (mb_strlen($location) < 2 || mb_strlen($location) > 160) {
        $errors[] = 'Please provide a valid location.';
    }
    if (!validate_date($itemDate)) {
        $errors[] = 'Please select a valid date.';
    }

    $image = null;
    if (!$errors) {
        try {
            $image = upload_image($_FILES['image'] ?? []);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO items (user_id, category_id, type, title, description, location, item_date, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                current_user_id(),
                $categoryId,
                $type,
                $title,
                $description,
                $location,
                $itemDate,
                $image,
            ]);

            $newId = (int)$pdo->lastInsertId();
            flash('success', ucfirst($type) . ' item posted successfully.');
            header('Location: item.php?id=' . $newId);
            exit;
        } catch (PDOException $e) {
            if ($image) {
                $path = __DIR__ . '/uploads/' . $image;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $errors[] = 'Could not publish the listing. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head><?php include 'partials/head.php'; ?><title>Post an item — CampusFind</title></head>
<body>
<?php include 'partials/header.php'; ?>
<main class="section form-page">
    <div class="container narrow">
        <div class="page-heading">
            <span class="eyebrow">CREATE LISTING</span>
            <h1>Tell campus what happened.</h1>
            <p>Add enough detail for someone to recognise the item and location.</p>
        </div>

        <?php include 'partials/form_errors.php'; ?>

        <form method="post" enctype="multipart/form-data" class="large-form" id="itemForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="type-switch" aria-label="Listing type">
                <label>
                    <input type="radio" name="type" value="lost" <?= $type === 'lost' ? 'checked' : '' ?>>
                    <span><b>Lost</b><small>I lost this item</small></span>
                </label>
                <label>
                    <input type="radio" name="type" value="found" <?= $type === 'found' ? 'checked' : '' ?>>
                    <span><b>Found</b><small>I found this item</small></span>
                </label>
            </div>

            <div class="form-grid two">
                <label>Item title
                    <input type="text" name="title" maxlength="160" required placeholder="e.g. Black HP laptop" value="<?= e($_POST['title'] ?? '') ?>">
                </label>
                <label>Category
                    <select name="category_id" required>
                        <option value="">Choose category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label>Description
                <textarea name="description" rows="5" maxlength="5000" required placeholder="Colour, brand, distinguishing marks, where you noticed it..."><?= e($_POST['description'] ?? '') ?></textarea>
            </label>

            <div class="form-grid two">
                <label>Location
                    <input type="text" name="location" maxlength="160" required placeholder="e.g. College Library" value="<?= e($_POST['location'] ?? '') ?>">
                </label>
                <label>Date
                    <input type="date" name="item_date" required value="<?= e($_POST['item_date'] ?? date('Y-m-d')) ?>">
                </label>
            </div>

            <label class="upload-zone" id="uploadZone">
                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp">
                <span class="upload-icon">＋</span>
                <strong>Upload item photo</strong>
                <small>JPG, PNG or WebP · maximum 5 MB</small>
                <span id="fileName"></span>
            </label>

            <div class="form-actions">
                <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
                <button class="btn btn-primary" type="submit">Publish listing →</button>
            </div>
        </form>
    </div>
</main>
<?php include 'partials/footer.php'; ?>
<script src="assets/app.js"></script>
</body>
</html>
