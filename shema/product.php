<?php
require_once 'includes/init.php';
require_once 'includes/activity.php';
require_once 'includes/ai_recommendations.php';

$productId = (int) ($_GET['id'] ?? 0);
$product = getProductById($db, $productId);

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect('products.php');
}

$pageTitle = $product['name'];
trackProductView($productId);
logActivity($db, 'product_view', 'Viewed product: ' . $product['name'], 'product', $productId);
$relatedProducts = getProducts($db, (int) $product['category_id']);
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] !== $product['id']);
$relatedProducts = array_slice($relatedProducts, 0, 4);

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Home</a> /
            <a href="products.php">Products</a> /
            <a href="products.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a> /
            <span><?= sanitize($product['name']) ?></span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?= getProductImage($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
            </div>
            <div class="product-detail-info">
                <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                <h1><?= sanitize($product['name']) ?></h1>
                <p class="product-detail-price"><?= formatPrice((float) $product['price']) ?></p>

                <div class="stock-status <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                    <?= $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ' available)' : '✗ Out of Stock' ?>
                </div>

                <p class="product-detail-desc"><?= sanitize($product['description']) ?></p>

                <?php if ($product['stock'] > 0): ?>
                <form action="cart-action.php" method="POST" class="add-to-cart-detail">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="redirect" value="product.php?id=<?= $product['id'] ?>">

                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" class="qty-btn" data-action="decrease">−</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button type="button" class="qty-btn" data-action="increase">+</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                    <a href="cart.php" class="btn btn-outline btn-lg">View Cart</a>
                </form>
                <?php endif; ?>

                <div class="product-meta">
                    <p><strong>Category:</strong> <a href="products.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a></p>
                    <p><strong>SKU:</strong> NS-<?= str_pad($product['id'], 5, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $related): ?>
                <article class="product-card">
                    <a href="product.php?id=<?= $related['id'] ?>" class="product-image">
                        <img src="<?= getProductImage($related['image']) ?>" alt="<?= sanitize($related['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-info">
                        <h3><a href="product.php?id=<?= $related['id'] ?>"><?= sanitize($related['name']) ?></a></h3>
                        <span class="product-price"><?= formatPrice((float) $related['price']) ?></span>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderAIRecommendations($db, $productId, 'AI Also Recommends'); ?>

<?php require_once 'includes/footer.php'; ?>
