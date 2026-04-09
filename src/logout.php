<?php
require_once 'auth.php';
logout();
header('Location: /index.php?logout=1');
exit;
