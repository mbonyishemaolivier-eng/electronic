<?php
require_once 'includes/init.php';

$pageTitle = 'Products';
$categorySlug = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$currentCategory = null;

if ($categorySlug) {
    $currentCategory = getCategoryBySlug($db, $categorySlug);
    if ($currentCategory) {
        $pageTitle = $currentCategory['name'];
        $products = getProducts($db, (int) $currentCategory['id']);
    } else {
        $products = getProducts($db);
    }
} else {
    $products = getProducts($db, null, $search ?: null);
}

$categories = getCategories($db);

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>
            <?php if ($currentCategory): ?>
                <?= sanitize($currentCategory['name']) ?>
            <?php elseif ($search): ?>
                Search: "<?= sanitize($search) ?>"
            <?php else: ?>
                All Products
            <?php endif; ?>
        </h1>
        <p><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="products-layout">
            <aside class="sidebar">
                <h3>Categories</h3>
                <ul class="category-list">
                    <li>
                        <a href="products.php" class="<?= !$categorySlug ? 'active' : '' ?>">All Products</a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="products.php?category=<?= sanitize($cat['slug']) ?>"
                           class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                            <?= sanitize($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="products-main">
                <?php if (empty($products)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📦</span>
                    <h2>No products found</h2>
                    <p>Try a different category or search term.</p>
                    <a href="products.php" class="btn btn-primary">Browse All Products</a>
                </div>
                <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a href="product.php?id=<?= $product['id'] ?>" class="product-image">
                            <img src="../<?= getProductImage($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                            <?php if ($product['featured']): ?>
                            <span class="product-badge">Featured</span>
                            <?php endif; ?>
                        </a>
                        <div class="product-info">
                            <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                            <h3><a href="product.php?id=<?= $product['id'] ?>"><?= sanitize($product['name']) ?></a></h3>
                            <p class="product-desc"><?= sanitize(substr($product['description'], 0, 80)) ?>...</p>
                            <div class="product-footer">
                                <span class="product-price"><?= formatPrice((float) $product['price']) ?></span>
                                <form action="cart-action.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="redirect" value="products.php<?= $categorySlug ? '?category=' . urlencode($categorySlug) : '' ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
