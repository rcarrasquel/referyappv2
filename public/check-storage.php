<?php
/**
 * Check Storage Link Status
 * 
 * Upload this file to /public/ and visit:
 * https://refery.app/check-storage.php
 * 
 * This will verify if the storage link is working correctly
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Storage Link Check</title>";
echo "<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:20px;}";
echo ".success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#004085;background:#cce5ff;padding:15px;border-radius:5px;margin:10px 0;}";
echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;}";
echo "code{background:#e9ecef;padding:2px 6px;border-radius:3px;}</style></head><body>";

echo "<h1>🔍 Storage Link Diagnostic</h1>";
echo "<hr>";

// Check 1: Does storage directory exist?
echo "<h2>1. Storage Directory</h2>";
$storagePath = __DIR__ . '/../storage/app/public';
if (is_dir($storagePath)) {
    echo "<div class='success'>✅ Storage directory exists: <code>$storagePath</code></div>";
} else {
    echo "<div class='error'>❌ Storage directory NOT found: <code>$storagePath</code></div>";
    echo "<div class='info'>This is critical! The storage/app/public directory should exist.</div>";
}

// Check 2: Does public/storage link exist?
echo "<h2>2. Public Storage Link</h2>";
$linkPath = __DIR__ . '/storage';
if (file_exists($linkPath)) {
    if (is_link($linkPath)) {
        $target = readlink($linkPath);
        echo "<div class='success'>✅ Symbolic link exists: <code>public/storage</code></div>";
        echo "<div class='info'>Points to: <code>$target</code></div>";
        
        // Verify the link points to the right place
        $expectedTarget = realpath(__DIR__ . '/../storage/app/public');
        $actualTarget = realpath($linkPath);
        
        if ($actualTarget === $expectedTarget) {
            echo "<div class='success'>✅ Link is correctly configured!</div>";
        } else {
            echo "<div class='error'>❌ Link points to wrong location!</div>";
            echo "<div class='info'>Expected: <code>$expectedTarget</code><br>Actual: <code>$actualTarget</code></div>";
        }
    } else {
        echo "<div class='error'>❌ 'public/storage' exists but is NOT a symbolic link!</div>";
        echo "<div class='warning'>This is probably a directory. It should be a symbolic link instead.</div>";
    }
} else {
    echo "<div class='error'>❌ Symbolic link does NOT exist: <code>public/storage</code></div>";
    echo "<div class='warning'>⚠️ THIS IS YOUR PROBLEM! Images won't load without this link.</div>";
}

// Check 3: Can we write to storage?
echo "<h2>3. Storage Permissions</h2>";
if (is_dir($storagePath)) {
    if (is_writable($storagePath)) {
        echo "<div class='success'>✅ Storage directory is writable</div>";
    } else {
        echo "<div class='error'>❌ Storage directory is NOT writable</div>";
        echo "<div class='info'>Fix permissions: chmod 755 storage/app/public</div>";
    }
} else {
    echo "<div class='error'>❌ Cannot check permissions (directory doesn't exist)</div>";
}

// Check 4: List some card images if they exist
echo "<h2>4. Uploaded Images</h2>";
$cardsPath = $storagePath . '/cards';
if (is_dir($cardsPath)) {
    $found = false;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cardsPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    echo "<div class='info'>";
    echo "<strong>Found images in storage:</strong><br>";
    $count = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $count < 10) {
            $relativePath = str_replace($storagePath . '/', '', $file->getPathname());
            echo "• <code>$relativePath</code><br>";
            $found = true;
            $count++;
        }
    }
    if ($count === 0) {
        echo "No images found in storage/app/public/cards/";
    }
    if ($count === 10) {
        echo "<br><em>... and more</em>";
    }
    echo "</div>";
} else {
    echo "<div class='warning'>⚠️ No 'cards' directory found in storage</div>";
    echo "<div class='info'>This is normal if no images have been uploaded yet.</div>";
}

// Check 5: Test if images are accessible via URL
echo "<h2>5. URL Accessibility Test</h2>";
if (file_exists($linkPath) && is_link($linkPath)) {
    echo "<div class='success'>✅ The symbolic link exists, images should be accessible via:<br>";
    echo "<code>https://refery.app/storage/cards/your-card-id/profile_image/image.jpg</code></div>";
} else {
    echo "<div class='error'>❌ Images are NOT accessible via URL because the symbolic link is missing.</div>";
}

// Solutions
echo "<hr>";
echo "<h2>🔧 How to Fix</h2>";

if (!file_exists($linkPath)) {
    echo "<div class='warning'>";
    echo "<h3>Solution 1: Run Laravel Command (Recommended)</h3>";
    echo "<p>If the fix-production.php script didn't work, try this PHP script:</p>";
    echo "<p>Create a file <code>public/create-storage-link.php</code> with:</p>";
    echo "<pre>";
    echo htmlspecialchars('<?php
require __DIR__ . \'/../vendor/autoload.php\';
$app = require_once __DIR__ . \'/../bootstrap/app.php\';
$app->make(\'Illuminate\Contracts\Console\Kernel\')->bootstrap();
Artisan::call(\'storage:link\');
echo "Storage link created!";
unlink(__FILE__);
');
    echo "</pre>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<h3>Solution 2: Manual Symbolic Link via PHP</h3>";
    echo "<p>Copy this code to a new file <code>public/manual-link.php</code> and visit it once:</p>";
    echo "<pre>";
    echo htmlspecialchars('<?php
$target = __DIR__ . \'/../storage/app/public\';
$link = __DIR__ . \'/storage\';

// Remove if exists
if (file_exists($link)) {
    if (is_link($link)) {
        unlink($link);
    } else {
        rename($link, $link . \'_backup_\' . time());
    }
}

// Create symlink
if (symlink($target, $link)) {
    echo "✅ Symbolic link created successfully!";
} else {
    echo "❌ Could not create symbolic link. Contact hosting support.";
}

// Delete this file
unlink(__FILE__);
');
    echo "</pre>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<h3>Solution 3: Manual via cPanel File Manager (If symlinks don't work)</h3>";
    echo "<ol>";
    echo "<li>Go to cPanel → File Manager</li>";
    echo "<li>Navigate to your app's <code>public/</code> folder</li>";
    echo "<li>If <code>storage</code> exists as a folder (not a link), rename it to <code>storage_old</code></li>";
    echo "<li>Unfortunately, cPanel File Manager can't create symlinks easily</li>";
    echo "<li>Contact your hosting support to create a symlink from:<br>";
    echo "<code>public/storage</code> → <code>../storage/app/public</code></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3>✅ Storage link exists!</h3>";
    echo "<p>If images still don't show:</p>";
    echo "<ol>";
    echo "<li>Check if images actually exist in <code>storage/app/public/cards/</code></li>";
    echo "<li>Check browser console for 404 errors</li>";
    echo "<li>Verify the database has correct paths (should be like: <code>cards/123/profile_image/abc.jpg</code>)</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>After fixing, DELETE this file for security:</strong> <code>public/check-storage.php</code></p>";

echo "</body></html>";
