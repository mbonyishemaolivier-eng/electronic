<?php

function trackProductView(int $productId): void
{
    if (!isset($_SESSION['viewed_products'])) {
        $_SESSION['viewed_products'] = [];
    }

    $_SESSION['viewed_products'] = array_values(array_unique(
        array_merge([$productId], array_filter($_SESSION['viewed_products'], fn($id) => $id !== $productId))
    ));

    $_SESSION['viewed_products'] = array_slice($_SESSION['viewed_products'], 0, 10);
}

function getCoPurchasedProducts(PDO $db, int $productId, int $limit = 5): array
{
    $stmt = $db->prepare(
        'SELECT oi2.product_id, COUNT(*) AS score
         FROM order_items oi1
         JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi2.product_id != oi1.product_id
         WHERE oi1.product_id = ?
         GROUP BY oi2.product_id
         ORDER BY score DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $productId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAIRecommendations(PDO $db, ?int $currentProductId = null, int $limit = 4): array
{
    $scores = [];
    $viewedIds = $_SESSION['viewed_products'] ?? [];

    if ($currentProductId && !in_array($currentProductId, $viewedIds, true)) {
        $viewedIds = array_merge([$currentProductId], $viewedIds);
    }

    $allProducts = $db->query(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.stock > 0'
    )->fetchAll();

    if (empty($allProducts)) {
        return [];
    }

    $viewedProducts = [];
    foreach ($viewedIds as $vid) {
        foreach ($allProducts as $p) {
            if ((int) $p['id'] === (int) $vid) {
                $viewedProducts[] = $p;
                break;
            }
        }
    }

    $avgPrice = 0;
    $categories = [];
    foreach ($viewedProducts as $vp) {
        $avgPrice += (float) $vp['price'];
        $categories[(int) $vp['category_id']] = ($categories[(int) $vp['category_id']] ?? 0) + 1;
    }
    if (count($viewedProducts) > 0) {
        $avgPrice /= count($viewedProducts);
    }

    foreach ($allProducts as $product) {
        $pid = (int) $product['id'];

        if ($currentProductId && $pid === $currentProductId) {
            continue;
        }

        $score = 0;
        $reasons = [];

        if (isset($categories[(int) $product['category_id']])) {
            $score += 35 * $categories[(int) $product['category_id']];
            $reasons[] = 'same category interest';
        }

        if ($avgPrice > 0) {
            $priceDiff = abs((float) $product['price'] - $avgPrice) / $avgPrice;
            if ($priceDiff <= 0.3) {
                $score += 25;
                $reasons[] = 'similar price range';
            }
        }

        if ($product['featured']) {
            $score += 10;
            $reasons[] = 'popular choice';
        }

        $stmt = $db->prepare(
            'SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.product_id = ?'
        );
        $stmt->execute([$pid]);
        $sales = (int) $stmt->fetchColumn();
        $score += min($sales * 3, 30);
        if ($sales > 0) {
            $reasons[] = 'frequently bought';
        }

        foreach ($viewedIds as $vid) {
            $coPurchased = getCoPurchasedProducts($db, (int) $vid, 3);
            foreach ($coPurchased as $cp) {
                if ((int) $cp['product_id'] === $pid) {
                    $score += 40 * (int) $cp['score'];
                    $reasons[] = 'often bought together';
                }
            }
        }

        if ($score > 0) {
            $scores[$pid] = [
                'product' => $product,
                'score' => $score,
                'reason' => $reasons[0] ?? 'recommended for you',
            ];
        }
    }

    if (empty($scores)) {
        usort($allProducts, fn($a, $b) => $b['featured'] <=> $a['featured']);
        $fallback = array_slice(array_filter($allProducts, fn($p) => !$currentProductId || (int) $p['id'] !== $currentProductId), 0, $limit);
        return array_map(fn($p) => ['product' => $p, 'score' => 0, 'reason' => 'trending at neresStore'], $fallback);
    }

    uasort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_slice(array_values($scores), 0, $limit);
}

function generateAIDescription(string $name, string $categoryName, float $price): string
{
    $templates = [
        'Smartphones' => "Experience premium mobile technology with the {name}. Designed for everyday use in Rwanda, it delivers excellent performance, sharp camera quality, and long battery life. Perfect for students, professionals, and anyone who wants reliable connectivity at an affordable price of {price}. Available now at neresStore with fast delivery across Kigali.",
        'Laptops' => "Boost your productivity with the {name}. Built for work, study, and entertainment, this laptop offers smooth performance and portable design. Whether you're a student at UNILAK or a professional in Kigali, the {name} at {price} is an excellent choice. Order today from neresStore.",
        'Audio' => "Immerse yourself in superior sound with the {name}. Engineered for crystal-clear audio, comfortable design, and everyday durability. Ideal for music lovers and remote workers across Rwanda. Get yours for {price} at neresStore with nationwide delivery.",
        'Accessories' => "Enhance your devices with the {name}. This essential accessory combines quality, durability, and great value at {price}. A must-have from neresStore for keeping your electronics powered and protected.",
        'TV & Monitors' => "Transform your viewing experience with the {name}. Stunning display quality and modern features make it perfect for home entertainment or office work. Available at neresStore for {price} with delivery across Rwanda.",
    ];

    $template = $templates[$categoryName] ?? "Discover the {name} — a quality {category} product available at neresStore for {price}. Shop now for fast delivery across Rwanda.";

    return str_replace(
        ['{name}', '{category}', '{price}'],
        [$name, strtolower($categoryName), number_format($price, 0, '.', ',') . ' RWF'],
        $template
    );
}
