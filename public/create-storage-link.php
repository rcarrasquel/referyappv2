<?php
/**
 * Create Storage Symbolic Link
 * 
 * Upload to /public/ and visit once:
 * https://refery.app/create-storage-link.php
 * 
 * This file will self-delete after execution
 */

$target = realpath(__DIR__ . '/../storage/app/public');
$link = __DIR__ . '/storage';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Create Storage Link</title>";
echo "<style>body{font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px;}";
echo ".success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;}";
echo "code{background:#e9ecef;padding:2px 6px;border-radius:3px;font-family:monospace;}</style></head><body>";

echo "<h1>🔗 Create Storage Link</h1><hr>";

// Check if target exists
if (!$target || !is_dir($target)) {
    echo "<div class='error'>";
    echo "<strong>❌ Error:</strong> Target directory not found<br>";
    echo "Looking for: <code>storage/app/public</code>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

// Remove existing link/directory
if (file_exists($link)) {
    if (is_link($link)) {
        echo "<div class='warning'>⚠️ Removing existing symbolic link...</div>";
        unlink($link);
    } else {
        // It's a directory, rename it
        $backup = $link . '_backup_' . time();
        echo "<div class='warning'>⚠️ Backing up existing directory to: <code>$backup</code></div>";
        rename($link, $backup);
    }
}

// Try to create symbolic link
if (symlink($target, $link)) {
    echo "<div class='success'>";
    echo "<h2>✅ Success!</h2>";
    echo "<p>Symbolic link created successfully:</p>";
    echo "<code>public/storage</code> → <code>storage/app/public</code>";
    echo "<p><strong>Your images should now load correctly!</strong></p>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<p>Test your card now: <a href='/'>Go to your card</a></p>";
    echo "</div>";
    
    // Try to delete this file
    if (@unlink(__FILE__)) {
        echo "<div class='success'>✅ This script has been auto-deleted.</div>";
    } else {
        echo "<div class='warning'>⚠️ Please delete this file manually: <code>public/create-storage-link.php</code></div>";
    }
} else {
    echo "<div class='error'>";
    echo "<h2>❌ Failed to Create Symlink</h2>";
    echo "<p>Your hosting may not allow symlinks via PHP.</p>";
    echo "<h3>Alternative Solutions:</h3>";
    echo "<ol>";
    echo "<li><strong>Contact your hosting support</strong> and ask them to create a symbolic link:<br>";
    echo "<code>public/storage</code> → <code>../storage/app/public</code></li>";
    echo "<li>Or ask them to enable <code>symlink()</code> function in PHP</li>";
    echo "<li>Some hosts require symlinks to be created via SSH (which you don't have)</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
