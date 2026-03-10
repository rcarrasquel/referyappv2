<?php

/**
 * Clear Laravel cache script for cPanel without SSH
 * Upload this file to your public/ directory and access it via browser
 * Delete it after use for security
 */

// For security: only allow execution for 5 minutes after upload
$creationTime = filectime(__FILE__);
if (time() - $creationTime > 300) {
    die('⛔ This script has been disabled for security. Delete and re-upload if needed.');
}

echo '<h1>🧹 Clearing Laravel Cache</h1>';
echo '<pre>';

$basePath = dirname(__DIR__);

function clearCache($directory, $name) {
    $deleted = 0;
    $failed = 0;
    
    if (!is_dir($directory)) {
        echo "⚠️  Directory not found: {$directory}\n";
        return;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            if (@rmdir($fileinfo->getRealPath())) {
                $deleted++;
            }
        } else {
            if (@unlink($fileinfo->getRealPath())) {
                $deleted++;
            } else {
                $failed++;
            }
        }
    }
    
    echo "✅ {$name}: {$deleted} files/folders deleted";
    if ($failed > 0) {
        echo " ({$failed} failed)";
    }
    echo "\n";
}

echo "Clearing Laravel caches...\n\n";

// Clear route cache
$routeCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache)) {
    if (@unlink($routeCache)) {
        echo "✅ Route cache cleared\n";
    } else {
        echo "⚠️  Could not delete route cache\n";
    }
} else {
    echo "ℹ️  No route cache found\n";
}

// Clear config cache
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    if (@unlink($configCache)) {
        echo "✅ Config cache cleared\n";
    } else {
        echo "⚠️  Could not delete config cache\n";
    }
} else {
    echo "ℹ️  No config cache found\n";
}

// Clear services cache
$servicesCache = $basePath . '/bootstrap/cache/services.php';
if (file_exists($servicesCache)) {
    if (@unlink($servicesCache)) {
        echo "✅ Services cache cleared\n";
    } else {
        echo "⚠️  Could not delete services cache\n";
    }
} else {
    echo "ℹ️  No services cache found\n";
}

// Clear compiled views
clearCache($basePath . '/storage/framework/views', 'Compiled views');

// Clear application cache
clearCache($basePath . '/storage/framework/cache/data', 'Application cache');

// Clear session files
clearCache($basePath . '/storage/framework/sessions', 'Session files');

echo "\n✨ Cache clearing completed!\n";
echo "\n⚠️  SECURITY: Delete this file after use!\n";
echo "</pre>";
