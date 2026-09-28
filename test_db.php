<?php
require_once __DIR__ . '/includes/db.php';

if ($conn && !$conn->connect_error) {
    echo "Database connection successful! Connected to: " . ($is_local ? "Localhost (XAMPP)" : "InfinityFree MySQL");
} else {
    echo "Database connection failed.";
}
?>