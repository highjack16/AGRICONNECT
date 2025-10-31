<?php
header("Location: /agriconnect/pages/products/marketplace.php" . ($_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : ""));
exit();
?>
