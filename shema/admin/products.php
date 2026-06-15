<?php
require_once 'includes/init.php';
requireAdmin();

$pageTitle = 'Manage Products';
$products = getAllProductsAdmin($db);

require_once 'includes/header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Products</h1>
        <p><?= count($products) ?> products in catalog</p>
    </div>
    <a href="product-create.php" class="btn btn-primary">+ Add New Product</a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($products)): ?>
        <div class="empty-state">
            <span class="empty-icon">📦</span>
            <h2>No products yet</h2>
            <p>Add your first product to get started.</p>
            <a href="product-create.php" class="btn btn-primary">Add Product</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <img src="<?= getProductImage($product['image']) ?>" alt="" class="table-thumb">
                        </td>
                        <td><strong><?= sanitize($product['name']) ?></strong></td>
                        <td><?= sanitize($product['category_name']) ?></td>
                        <td><?= formatPrice((float) $product['price']) ?></td>
                        <td>
                            <span class="<?= $product['stock'] <= 5 ? 'text-danger' : '' ?>">
                                <?= $product['stock'] ?>
                            </span>
                        </td>
                        <td><?= $product['featured'] ? '⭐ Yes' : 'No' ?></td>
                        <td class="actions-cell">
                            <a href="product-edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <form action="product-delete.php" method="POST" class="inline-form"
                                  onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
