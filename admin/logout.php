<?php
// admin/logout.php
require_once '../config.php';
session_destroy();
redirect('login.php');
?>

---FILE SEPARATOR: user/logout.php---

