<?php
/**
 * Fix Storage Permissions
 * 
 * Upload to /public/ and visit:
 * https://refery.app/fix-storage-permissions.php
 * 
 * This will fix file permissions for uploaded images
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Storage Permissions</title>";
echo "<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:20px;}";
echo ".success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".warning{color:#856404;background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{color:#004085;background:#cce5ff;padding:15px;border-radius:5px;margin:10px 0;}";
echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;font-size:12px;}";
echo "code{background:#e9ecef;padding:2px 6px;border-radius:3px;}</style></head><body>";

echo "<h1>🔧 Fix Storage Permissions</h1>";
echo "<hr>";

$storagePath = realpath(__DIR__ . '/../storage/app/public');

if (!$storagePath || !is_dir($storagePath)) {
    echo "<div class='error'>❌ Storage directory not found</div>";
    echo "</body></html>";
    exit;
}

echo "<div class='info'>Working on: <code>$storagePath</code></div>";

$fixed = 0;
$errors = 0;
$checked = 0;

function fixPermissions($path, &$fixed, &$errors, &$checked) {
    if (is_dir($path)) {
        // Fix directory permissions to 755
        $checked++;
        $currentPerms = fileperms($path);
        $targetPerms = 0755;
        
        if (($currentPerms & 0777) !== $targetPerms) {
            if (@chmod($path, $targetPerms)) {
                $fixed++;
                echo "• Fixed directory: <code>" . basename($path) . "</code> → 755<br>";
            } else {
                $errors++;
                echo "• ⚠️ Could not fix: <code>" . basename($path) . "</code><br>";
            }
        }
        
        // Process subdirectories and files
        $items = @scandir($path);
        if ($items) {
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..') {
                    fixPermissions($path . '/' . $item, $fixed, $errors, $checked);
                }
            }
        }
    } else {
        // Fix file permissions to 644
        $checked++;
        $currentPerms = fileperms($path);
        $targetPerms = 0644;
        
        if (($currentPerms & 0777) !== $targetPerms) {
            if (@chmod($path, $targetPerms)) {
                $fixed++;
                $relativePath = str_replace($GLOBALS['storagePath'] . '/', '', $path);
                // Only show first 50 files to avoid huge output
                if ($fixed <= 50) {
                    echo "• Fixed file: <code>$relativePath</code> → 644<br>";
                }
            } else {
                $errors++;
            }
        }
    }
}

echo "<h2>Processing Files...</h2>";
echo "<div class='info'>";

// Fix the storage/app/public root
echo "Fixing: <code>storage/app/public/</code><br>";
@chmod($storagePath, 0755);

// Fix everything inside
if (is_dir($storagePath . '/cards')) {
    echo "<br><strong>Fixing card images...</strong><br>";
    fixPermissions($storagePath . '/cards', $fixed, $errors, $checked);
}

if (is_dir($storagePath . '/products')) {
    echo "<br><strong>Fixing product images...</strong><br>";
    fixPermissions($storagePath . '/products', $fixed, $errors, $checked);
}

if ($fixed > 50) {
    echo "<br><em>... and " . ($fixed - 50) . " more files</em><br>";
}

echo "</div>";

echo "<hr>";
echo "<div class='success'>";
echo "<h2>✅ Summary</h2>";
echo "• Files/folders checked: <strong>$checked</strong><br>";
echo "• Permissions fixed: <strong>$fixed</strong><br>";
echo "• Errors: <strong>$errors</strong><br>";
echo "</div>";

// Check the symbolic link
echo "<h2>Symbolic Link Status</h2>";
$linkPath = __DIR__ . '/storage';
if (file_exists($linkPath) && is_link($linkPath)) {
    echo "<div class='success'>✅ Symbolic link exists and is working</div>";
    
    // Try to access a test URL
    $testImage = "https://refery.app/storage/cards/733f18e5-d744-437c-97a8-ae98b733a957/profile_image/p6JY1adS16JNzio9t8dtNEUT7g8q09q1vEjddPdQ.jpg";
    echo "<div class='info'>";
    echo "<strong>Test your image now:</strong><br>";
    echo "<a href='$testImage' target='_blank'>Click here to test</a>";
    echo "</div>";
} else {
    echo "<div class='error'>❌ Symbolic link is missing!</div>";
    echo "<div class='warning'>Run <code>create-storage-link.php</code> first!</div>";
}

// Check specific file
echo "<h2>Your Specific Image</h2>";
$yourImage = $storagePath . '/cards/733f18e5-d744-437c-97a8-ae98b733a957/profile_image/p6JY1adS16JNzio9t8dtNEUT7g8q09q1vEjddPdQ.jpg';
if (file_exists($yourImage)) {
    $perms = fileperms($yourImage);
    $permsString = substr(sprintf('%o', $perms), -4);
    
    echo "<div class='success'>";
    echo "✅ File exists!<br>";
    echo "• Location: <code>$yourImage</code><br>";
    echo "• Permissions: <code>$permsString</code><br>";
    echo "• Size: " . number_format(filesize($yourImage) / 1024, 2) . " KB<br>";
    echo "• Readable: " . (is_readable($yourImage) ? "✅ Yes" : "❌ No") . "<br>";
    echo "</div>";
    
    // Fix this specific file
    if (@chmod($yourImage, 0644)) {
        echo "<div class='success'>✅ Permissions updated for this image</div>";
    }
} else {
    echo "<div class='error'>❌ File not found at expected location</div>";
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Test your card again: <a href='/' target='_blank'>Visit your card</a></li>";
echo "<li>If images still don't load, check browser console (F12) for errors</li>";
echo "<li>Make sure <code>.htaccess</code> allows access to storage folder</li>";
echo "</ol>";
echo "</div>";

// Try to delete this file
if (@unlink(__FILE__)) {
    echo "<div class='success'>✅ This script has been auto-deleted</div>";
} else {
    echo "<div class='warning'>⚠️ Please DELETE this file manually: <code>public/fix-storage-permissions.php</code></div>";
}

echo "</body></html>";
