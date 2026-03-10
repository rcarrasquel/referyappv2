<?php
/**
 * Clear Route Cache in Production
 * 
 * Upload to /public/ and visit:
 * https://refery.app/clear-route-cache.php
 * 
 * This will clear and rebuild the route cache
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Clear Route Cache</title>";
echo "<style>body{font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px;}";
echo ".success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#004085;background:#cce5ff;padding:15px;border-radius:5px;margin:10px 0;}";
echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;font-size:12px;}</style></head><body>";

echo "<h1>🗑️ Clear Route Cache</h1><hr>";

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<div class='info'>";
echo "<h2>Clearing caches...</h2>";

try {
    // Clear route cache
    echo "• Clearing route cache...<br>";
    Artisan::call('route:clear');
    echo "  <strong>✅ Done</strong><br>";
    
    // Clear config cache
    echo "• Clearing config cache...<br>";
    Artisan::call('config:clear');
    echo "  <strong>✅ Done</strong><br>";
    
    // Clear view cache
    echo "• Clearing view cache...<br>";
    Artisan::call('view:clear');
    echo "  <strong>✅ Done</strong><br>";
    
    // Clear general cache
    echo "• Clearing application cache...<br>";
    Artisan::call('cache:clear');
    echo "  <strong>✅ Done</strong><br>";
    
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h2>✅ All Caches Cleared!</h2>";
    echo "<p>Now you can access: <a href='/storage-link'>/storage-link</a></p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Rebuild caches (recommended):</h3>";
    
    // Rebuild config cache
    echo "• Rebuilding config cache...<br>";
    Artisan::call('config:cache');
    echo "  <strong>✅ Done</strong><br>";
    
    // Rebuild route cache
    echo "• Rebuilding route cache...<br>";
    Artisan::call('route:cache');
    echo "  <strong>✅ Done</strong><br>";
    
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h2>🎉 Process Complete!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Visit <a href='/storage-link' target='_blank'>/storage-link</a> to create the storage link</li>";
    echo "<li>After the storage link is created, comment out that route in <code>routes/web.php</code> for security</li>";
    echo "<li>Delete this file for security</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "</div>";
    echo "<div class='error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

// Try to delete this file
if (@unlink(__FILE__)) {
    echo "<div class='success'>✅ This script has been auto-deleted</div>";
} else {
    echo "<div class='info'>⚠️ Please DELETE this file: <code>public/clear-route-cache.php</code></div>";
}

echo "</body></html>";
