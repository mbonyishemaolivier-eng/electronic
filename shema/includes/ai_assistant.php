<?php

require_once __DIR__ . '/ai_recommendations.php';

function normalizeMessage(string $message): string
{
    return strtolower(trim(preg_replace('/\s+/', ' ', $message)));
}

function extractPriceLimit(string $message): ?float
{
    if (preg_match('/(?:under|below|less than|max|maximum|cheaper than)\s*([\d,]+)/i', $message, $m)) {
        return (float) str_replace(',', '', $m[1]);
    }
    if (preg_match('/([\d,]+)\s*(?:rwf|frw)?/i', $message, $m)) {
        $num = (float) str_replace(',', '', $m[1]);
        if ($num >= 10000) {
            return $num;
        }
    }
    return null;
}

function detectCategorySlug(string $message): ?string
{
    $map = [
        'smartphones' => ['phone', 'smartphone', 'mobile', 'iphone', 'samsung', 'tecno', 'android'],
        'laptops'     => ['laptop', 'notebook', 'computer', 'macbook', 'hp', 'lenovo'],
        'audio'       => ['headphone', 'earphone', 'speaker', 'audio', 'sound', 'jbl', 'airpod', 'sony'],
        'accessories' => ['charger', 'cable', 'case', 'accessory', 'hub', 'powerbank', 'power bank'],
        'tv-monitors' => ['tv', 'television', 'monitor', 'screen', 'display'],
    ];

    foreach ($map as $slug => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return $slug;
            }
        }
    }

    return null;
}

function searchProductsForAI(PDO $db, string $message, int $limit = 4): array
{
    $categorySlug = detectCategorySlug($message);
    $priceLimit = extractPriceLimit($message);
    $sortCheap = str_contains($message, 'cheap') || str_contains($message, 'affordable') || str_contains($message, 'budget');
    $sortPremium = str_contains($message, 'premium') || str_contains($message, 'best') || str_contains($message, 'expensive');

    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.stock > 0';
    $params = [];

    if ($categorySlug) {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }

    if ($priceLimit) {
        $sql .= ' AND p.price <= ?';
        $params[] = $priceLimit;
    }

    if ($sortCheap) {
        $sql .= ' ORDER BY p.price ASC';
    } elseif ($sortPremium) {
        $sql .= ' ORDER BY p.featured DESC, p.price DESC';
    } else {
        $sql .= ' ORDER BY p.featured DESC, p.name ASC';
    }

    $sql .= ' LIMIT ' . (int) $limit;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    if (empty($products) && ($categorySlug || $priceLimit)) {
        $stmt = $db->query(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON p.category_id = c.id
             WHERE p.stock > 0 ORDER BY p.featured DESC LIMIT ' . (int) $limit
        );
        $products = $stmt->fetchAll();
    }

    return $products;
}

function processAIChat(PDO $db, string $message): array
{
    $msg = normalizeMessage($message);

    if ($msg === '') {
        return [
            'reply' => "Hello! I'm the neresStore AI Assistant. Ask me things like:\n• \"Recommend a cheap phone\"\n• \"Laptop under 800000 RWF\"\n• \"Show me headphones\"\n• \"What's your delivery info?\"",
            'products' => [],
        ];
    }

    if (preg_match('/\b(hi|hello|hey|muraho|bonjour|good morning|good evening)\b/', $msg)) {
        return [
            'reply' => "Muraho! 👋 Welcome to neresStore. I'm your AI shopping assistant. I can recommend products, help you find phones, laptops, audio gear, and answer questions about delivery. What are you looking for today?",
            'products' => [],
        ];
    }

    if (preg_match('/\b(delivery|shipping|deliver|ship)\b/', $msg)) {
        return [
            'reply' => "🚚 Delivery Information:\n• Shipping cost: 2,000 RWF\n• Delivery time: 2–5 business days\n• Free delivery in Kigali for orders over 500,000 RWF\n• We deliver nationwide across Rwanda",
            'products' => [],
        ];
    }

    if (preg_match('/\b(contact|phone|call|email|support|help me)\b/', $msg) && !detectCategorySlug($msg)) {
        return [
            'reply' => "📞 Contact neresStore:\n• Phone: +250 791 591 773\n• Email: info@neresstore.rw\n• Location: Kigali, Rwanda\n\nOr tell me what product you're looking for and I'll recommend options!",
            'products' => [],
        ];
    }

    if (preg_match('/\b(cart|checkout|order|pay|payment)\b/', $msg) && !detectCategorySlug($msg)) {
        return [
            'reply' => "🛒 To place an order:\n1. Browse products and click \"Add to Cart\"\n2. Go to your Cart and review items\n3. Click \"Proceed to Checkout\"\n4. Fill in your delivery details\n5. Confirm your order!\n\nWe accept payment on delivery. Need product recommendations?",
            'products' => [],
        ];
    }

    if (preg_match('/\b(recommend|suggestion|suggest|what should|best for|looking for|need a|want a|show me|find me)\b/', $msg) || detectCategorySlug($msg) || extractPriceLimit($msg)) {
        $recommendations = getAIRecommendations($db, null, 4);

        if (detectCategorySlug($msg) || extractPriceLimit($msg) || str_contains($msg, 'cheap') || str_contains($msg, 'affordable')) {
            $products = searchProductsForAI($db, $msg, 4);
        } else {
            $products = array_map(fn($r) => $r['product'], $recommendations);
        }

        if (empty($products)) {
            return [
                'reply' => "I couldn't find matching products right now. Try browsing our full catalog or ask about phones, laptops, or headphones!",
                'products' => [],
            ];
        }

        $categorySlug = detectCategorySlug($msg);
        $priceLimit = extractPriceLimit($msg);

        if ($categorySlug) {
            $cat = getCategoryBySlug($db, $categorySlug);
            $catName = $cat['name'] ?? 'products';
            $intro = "Based on your interest in **{$catName}**, here are my AI-picked recommendations:";
        } elseif ($priceLimit) {
            $intro = "Here are great options under **" . number_format($priceLimit, 0, '.', ',') . " RWF**:";
        } elseif (str_contains($msg, 'cheap') || str_contains($msg, 'affordable')) {
            $intro = "Here are our most **affordable** picks for you:";
        } else {
            $intro = "Based on your browsing patterns and popular trends, I recommend:";
        }

        return [
            'reply' => "🤖 {$intro}",
            'products' => array_slice($products, 0, 4),
        ];
    }

    $keywordProducts = searchProductsForAI($db, $msg, 3);
    if (!empty($keywordProducts)) {
        return [
            'reply' => "I found these products matching your query:",
            'products' => $keywordProducts,
        ];
    }

    return [
        'reply' => "I'm not sure I understood that. Try asking:\n• \"Recommend a laptop for students\"\n• \"Cheap phone under 200000\"\n• \"Best headphones\"\n• \"Delivery information\"",
        'products' => [],
    ];
}

function formatAIProductCards(array $products): array
{
    return array_map(function ($p) {
        return [
            'id' => (int) $p['id'],
            'name' => $p['name'],
            'price' => formatPrice((float) $p['price']),
            'category' => $p['category_name'] ?? '',
            'url' => 'product.php?id=' . $p['id'],
            'image' => getProductImage($p['image']),
        ];
    }, $products);
}
