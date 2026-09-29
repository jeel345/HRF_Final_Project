<?php
require_once 'config.php';
unset($_SESSION['user_id'], $_SESSION['user_name']);
header('Location: login.php');
exit;
