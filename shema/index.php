<?php
require_once 'includes/init.php';
require_once 'includes/ai_recommendations.php';

$pageTitle = 'Home';
$featuredProducts = getFeaturedProducts($db, 6);
$categories = getCategories($db);

require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <span class="hero-badge">Rwanda's #1 Electronics Store</span>
            <h1>Discover the Latest <span class="text-gradient">Electronics</span></h1>
            <p>Shop smartphones, laptops, audio gear, and accessories at unbeatable prices. Fast delivery across Kigali and nationwide.</p>
            <div class="hero-actions">
                <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
                <a href="products.php?category=smartphones" class="btn btn-outline btn-lg">Browse Phones</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-card">
                <div class="hero-card-icon">📱</div>
                <h3>Smartphones</h3>
                <p>From 195,000 RWF</p>
            </div>
            <div class="hero-card">
                <div class="hero-card-icon">💻</div>
                <h3>Laptops</h3>
                <p>From 620,000 RWF</p>
            </div>
            <div class="hero-card">
                <div class="hero-card-icon">🎧</div>
                <h3>Audio</h3>
                <p>From 145,000 RWF</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Find exactly what you need</p>
        </div>
        <div class="category-grid">
            <?php
            $categoryIcons = [
                'smartphones' => '📱',
                'laptops' => '💻',
                'audio' => '🎧',
                'accessories' => '🔌',
                'tv-monitors' => '📺',
            ];
            foreach ($categories as $cat):
            ?>
            <a href="products.php?category=<?= sanitize($cat['slug']) ?>" class="category-card">
                <span class="category-icon"><?= $categoryIcons[$cat['slug']] ?? '📦' ?></span>
                <h3><?= sanitize($cat['name']) ?></h3>
                <p><?= sanitize($cat['description']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <a href="products.php" class="btn btn-outline">View All</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <article class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>" class="product-image">
                    <img src="<?= getProductImage($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                    <?php if ($product['featured']): ?>
                    <span class="product-badge">Featured</span>
                    <?php endif; ?>
                </a>
                <div class="product-info">
                    <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                    <h3><a href="product.php?id=<?= $product['id'] ?>"><?= sanitize($product['name']) ?></a></h3>
                    <div class="product-footer">
                        <span class="product-price"><?= formatPrice((float) $product['price']) ?></span>
                        <form action="cart-action.php" method="POST" class="add-to-cart-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="redirect" value="index.php">
                            <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php renderAIRecommendations($db, null, 'AI Picks For You'); ?>

<section class="section features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🚚</span>
                <h3>Fast Delivery</h3>
                <p>Free delivery in Kigali for orders over 500,000 RWF</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">✅</span>
                <h3>Genuine Products</h3>
                <p>100% authentic electronics with warranty</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🤖</span>
                <h3>AI Shopping Assistant</h3>
                <p>Get smart product recommendations powered by AI</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📞</span>
                <h3>24/7 Support</h3>
                <p>Call us anytime at +250 791 591 773</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
