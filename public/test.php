<?php
// Simple test file
echo "✅ PHP is working in public folder!<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Public index.php exists: " . (file_exists(__DIR__ . '/index.php') ? 'YES' : 'NO') . "<br>";
echo ".htaccess exists: " . (file_exists(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "<br>";

// Try to load Laravel
if (file_exists(__DIR__ . '/index.php')) {
    echo "<br>Laravel index.php content:<br><pre>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/index.php'));
    echo "</pre>";
}
