<?php
//session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /kbgm/login.php");
    exit;
}
