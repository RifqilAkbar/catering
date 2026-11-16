<?php
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['admin_id']);
flash('success', 'Anda berhasil logout dari panel admin.');
redirect('admin/login.php');
