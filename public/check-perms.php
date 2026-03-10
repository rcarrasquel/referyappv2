<?php
/**
 * Permission and configuration checker
 * This will tell us EXACTLY what's wrong
 */

echo "<!DOCTYPE html><html><head><style>
body{font-family:monospace;background:#000;color:#0f0;padding:20px}
.error{color:#f00}
.ok{color:#0f0}
.warn{color:#ff0}
</style></head><body>";

echo "<h1>🔍 Complete Diagnostic</h1>";

// Check file permissions
$files = [
    'index.php' => __DIR__ . '/index.php',
    'index.html' => __DIR__ . '/index.html',
    '.htaccess' => __DIR__ . '/.htaccess',
];

echo "<h2>📁 File Permissions</h2>";
foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $readable = is_readable($path) ? '<span class="ok">✓</span>' : '<span class="error">✗</span>';
        echo "$name: <span class='ok'>EXISTS</span> | Perms: $perms | Readable: $readable<br>";
        
        if ($name === 'index.php' || $name === 'index.html') {
            if ($perms !== '0644' && $perms !== '0666' && $perms !== '0755') {
                echo "<span class='warn'>⚠️ Unusual permissions! Should be 644 or 755</span><br>";
            }
        }
    } else {
        echo "$name: <span class='error'>NOT FOUND</span><br>";
    }
}

echo "<h2>🌐 Server Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";

echo "<h2>🔧 Recommended Actions</h2>";
echo "<ol>";
echo "<li><span class='warn'>In cPanel File Manager:</span><br>";
echo "   - Right-click <code>index.php</code> → Change Permissions → Set to <strong>644</strong></li>";
echo "<li><span class='warn'>Delete these test files after:</span><br>";
echo "   - check-perms.php, test.php, diagnostic.php, check-subdomain.php</li>";
echo "<li><span class='ok'>Contact your hosting support with this info:</span><br>";
echo "   <em>'Wildcard subdomain not serving index.php at document root. DirectoryIndex appears disabled.'</em></li>";
echo "</ol>";

echo "<h2>🧪 Quick Fix Test</h2>";
echo "<p>Try accessing directly: <a href='/index.php' style='color:#0ff'>https://" . $_SERVER['HTTP_HOST'] . "/index.php</a></p>";
echo "<p>If that works but the root doesn't, it's a cPanel DirectoryIndex configuration issue.</p>";

echo "</body></html>";
