<?php
require_once 'includes/init.php';
requireAdmin();

$pageTitle = 'Add Product';
$categories = getCategories($db);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (empty($name)) $errors[] = 'Product name is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than zero.';
    if ($stock < 0) $errors[] = 'Stock cannot be negative.';
    if ($categoryId <= 0) $errors[] = 'Please select a category.';

    $image = 'default.jpg';
    if (!empty($_FILES['image']['name'])) {
        try {
            $uploaded = uploadProductImage($_FILES['image']);
            if ($uploaded) {
                $image = $uploaded;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $slug = uniqueProductSlug($db, generateSlug($name));

        $productId = createProduct($db, [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'price' => $price,
            'image' => $image,
            'stock' => $stock,
            'featured' => $featured,
        ]);

        logActivity($db, 'product_created', 'Created product: ' . $name, 'product', $productId);
        setFlash('success', 'Product "' . $name . '" created successfully.');
        redirect('products.php');
    }
}

require_once 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Add New Product</h1>
    <a href="products.php" class="btn btn-outline">← Back to Products</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <ul><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required value="<?= sanitize($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <div class="ai-desc-row">
                    <button type="button" id="aiGenerateDesc" class="btn btn-sm btn-ai">🤖 AI Generate Description</button>
                </div>
                <textarea id="description" name="description" required rows="4"><?= sanitize($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (RWF) *</label>
                    <input type="number" id="price" name="price" required min="1" step="1" value="<?= sanitize($_POST['price'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" required min="0" value="<?= sanitize($_POST['stock'] ?? '0') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <small class="form-hint">JPG, PNG, WEBP or GIF. Max 5MB.</small>
            </div>

            <div class="form-group form-check">
                <label>
                    <input type="checkbox" name="featured" value="1" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                    Feature this product on homepage
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Create Product</button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<script src="../assets/js/admin-ai.js"></script>
