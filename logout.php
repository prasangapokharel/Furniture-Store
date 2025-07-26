<?php
require_once 'include/session.php';

// Logout user
logoutUser();

// Redirect to homepage with logout message
header('Location: index.php?logout=1');
exit();
?>