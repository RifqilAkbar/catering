<?php
require_once __DIR__ . '/config/config.php';

unset($_SESSION['user_id'], $_SESSION['cart_session'], $_SESSION['last_order_number'], $_SESSION['last_order_guest']);
flash('success', 'Anda berhasil logout.');
redirect('index.php');
