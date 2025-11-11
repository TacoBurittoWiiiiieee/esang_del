<?php

session_unset();
session_destroy();

// Redirect to the main Index.php in the public directory
header('Location: login.php');
exit;
?>
