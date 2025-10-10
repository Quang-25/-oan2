<?php
session_start();
if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  exit;
}
?>
<h2>Xin chào, <?php echo $_SESSION["user"]; ?>!</h2>
<a href="logout.php">Đăng xuất</a>
