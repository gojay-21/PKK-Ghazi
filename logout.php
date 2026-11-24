<?php
require_once __DIR__ . '/koneksi.php';

unset($_SESSION['admin_id'], $_SESSION['admin_nama']);
session_regenerate_id(true);

redirect('login.php');
