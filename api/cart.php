<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Metode tidak diizinkan.'], 405);
}

if (!csrf_valid()) {
    json_response(['ok' => false, 'message' => 'Sesi keamanan tidak valid. Muat ulang halaman.'], 419);
}

$action = (string) ($_POST['action'] ?? '');

try {
    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int) $_POST['variant_id'] : null;
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $note = isset($_POST['note']) ? (string) $_POST['note'] : null;
        $addonIds = $_POST['addons'] ?? [];
        if (!is_array($addonIds)) {
            $addonIds = [];
        }

        if ($productId <= 0) {
            json_response(['ok' => false, 'message' => 'Produk tidak valid.'], 422);
        }

        [$success, $message] = add_to_cart($productId, $variantId, $quantity, $addonIds, $note);

        json_response([
            'ok'      => $success,
            'message' => $message,
            'count'   => cart_count(),
            'subtotal' => cart_subtotal(),
        ], $success ? 200 : 422);
    }

    if ($action === 'summary') {
        json_response([
            'ok'       => true,
            'count'    => cart_count(),
            'subtotal' => cart_subtotal(),
            'items'    => cart_items(),
        ]);
    }

    json_response(['ok' => false, 'message' => 'Aksi tidak dikenal.'], 400);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
}
