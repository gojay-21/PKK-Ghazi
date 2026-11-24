<?php
require_once __DIR__ . '/koneksi.php';

unset($_SESSION['penyewa_id'], $_SESSION['penyewa_nama']);
session_regenerate_id(true);

redirect('login_penyewa.php');
