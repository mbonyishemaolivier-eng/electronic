<?php

require_once __DIR__ . '/ai_recommendations.php';

function renderAIRecommendations(PDO $db, ?int $currentProductId = null, string $title = 'AI Recommended For You'): void
{
    $recommendations = getAIRecommendations($db, $currentProductId, 4);

    if (empty($recommendations)) {
        return;
    }
    ?>
    <section class="section ai-recommendations-section">
        <div class="container">
            <div class="section-header ai-section-header">
                <div>
                    <span class="ai-badge">🤖 AI Powered</span>
                    <h2><?= sanitize($title) ?></h2>
                    <p>Smart suggestions based on your browsing and shopping trends</p>
                </div>
            </div>
            <div class="product-grid">
                <?php foreach ($recommendations as $rec):
                    $product = $rec['product'];
                ?>
                <article class="product-card ai-product-card">
                    <a href="product.php?id=<?= $product['id'] ?>" class="product-image">
                        <img src="<?= getProductImage($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                        <span class="ai-match-badge"><?= sanitize(ucfirst($rec['reason'])) ?></span>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                        <h3><a href="product.php?id=<?= $product['id'] ?>"><?= sanitize($product['name']) ?></a></h3>
                        <div class="product-footer">
                            <span class="product-price"><?= formatPrice((float) $product['price']) ?></span>
                            <form action="cart-action.php" method="POST" class="add-to-cart-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="redirect" value="<?= sanitize($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderAIChatWidget(): void
{
    ?>
    <div id="aiChatWidget" class="ai-chat-widget">
        <button id="aiChatToggle" class="ai-chat-toggle" aria-label="Open AI Assistant">
            <span class="ai-toggle-icon">🤖</span>
            <span class="ai-toggle-text">AI Help</span>
        </button>

        <div id="aiChatPanel" class="ai-chat-panel" hidden>
            <div class="ai-chat-header">
                <div>
                    <strong>🤖 neresStore AI</strong>
                    <small>Smart shopping assistant</small>
                </div>
                <button id="aiChatClose" class="ai-chat-close" aria-label="Close">✕</button>
            </div>
            <div id="aiChatMessages" class="ai-chat-messages">
                <div class="ai-msg ai-msg-bot">
                    <p>Muraho! I'm your AI assistant. Ask me to recommend phones, laptops, headphones, or help with delivery info.</p>
                </div>
            </div>
            <div class="ai-chat-suggestions">
                <button type="button" class="ai-suggestion" data-msg="Recommend a cheap phone">📱 Cheap phone</button>
                <button type="button" class="ai-suggestion" data-msg="Best laptop for students">💻 Laptop</button>
                <button type="button" class="ai-suggestion" data-msg="Show me headphones">🎧 Headphones</button>
            </div>
            <form id="aiChatForm" class="ai-chat-form">
                <input type="text" id="aiChatInput" placeholder="Ask me anything..." autocomplete="off" maxlength="500">
                <button type="submit" class="ai-chat-send">Send</button>
            </form>
        </div>
    </div>
    <script src="assets/js/ai-assistant.js"></script>
    <?php
}
