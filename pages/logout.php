<?php
// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_id() != "") {
    session_destroy();
}

// Redirect to home page
header('Location: index.php');
exit();
?>