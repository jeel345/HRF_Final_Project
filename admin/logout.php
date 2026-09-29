<?php
require_once __DIR__ . '/init.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
session_regenerate_id(true);
redirect('login.php');
