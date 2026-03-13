<?php
// Setup script - run once to initialize database
require_once __DIR__ . '/config/database.php';

echo "<h1>Online Book Store - Setup</h1>";
echo "<p>Database connection established.</p>";
echo "<p>Tables created/verified.</p>";
echo "<p>Admin user created:</p>";
echo "<ul>";
echo "<li>Email: admin@bookstore.com</li>";
echo "<li>Password: 1234</li>";
echo "</ul>";
echo "<p><a href='/online-book-selling-system/'>Go to Homepage</a></p>";
?>