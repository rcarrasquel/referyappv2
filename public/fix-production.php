<?php
/**
 * ReferyApp - Production Fix Script
 * 
 * HOW TO USE:
 * 1. Upload this file to your /public directory
 * 2. Visit: https://refery.app/fix-production.php
 * 3. The script will run and self-delete automatically
 * 
 * ⚠️ WARNING: This file will be automatically deleted after execution
 */

// Prevent execution in non-production or if APP_KEY is not set
if (!file_exists(__DIR__ . '/../.env')) {
    die('Error: .env file not found. Make sure you are in the correct directory.');
}

echo "<h1>ReferyApp - Production Fix</h1>";
echo "<pre style='background:#f5f5f5; padding:20px; border-radius:5px;'>";

// Initialize Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Laravel loaded successfully\n\n";

// Function to log actions
function logAction($message) {
    echo "➜ $message\n";
    flush();
}

try {
    // Step 1: Ensure storage directories exist
    logAction("Creating storage directories...");
    
    $directories = [
        storage_path('framework/sessions'),
        storage_path('framework/views'),
        storage_path('framework/cache'),
        storage_path('framework/cache/data'),
        storage_path('logs'),
        base_path('bootstrap/cache'),
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            logAction("  Created: $dir");
        } else {
            logAction("  Exists: $dir");
        }
    }
    
    echo "\n";
    
    // Step 2: Clear all caches
    logAction("Clearing all caches...");
    
    try {
        Artisan::call('cache:clear');
        logAction("  ✓ Cache cleared");
    } catch (Exception $e) {
        logAction("  ⚠ Cache clear: " . $e->getMessage());
    }
    
    try {
        Artisan::call('config:clear');
        logAction("  ✓ Config cleared");
    } catch (Exception $e) {
        logAction("  ⚠ Config clear: " . $e->getMessage());
    }
    
    try {
        Artisan::call('route:clear');
        logAction("  ✓ Routes cleared");
    } catch (Exception $e) {
        logAction("  ⚠ Route clear: " . $e->getMessage());
    }
    
    try {
        Artisan::call('view:clear');
        logAction("  ✓ Views cleared");
    } catch (Exception $e) {
        logAction("  ⚠ View clear: " . $e->getMessage());
    }
    
    echo "\n";
    
    // Step 3: Rebuild caches
    logAction("Rebuilding caches...");
    
    try {
        Artisan::call('config:cache');
        logAction("  ✓ Config cached");
    } catch (Exception $e) {
        logAction("  ⚠ Config cache: " . $e->getMessage());
    }
    
    try {
        Artisan::call('route:cache');
        logAction("  ✓ Routes cached");
    } catch (Exception $e) {
        logAction("  ⚠ Route cache: " . $e->getMessage());
    }
    
    try {
        Artisan::call('view:cache');
        logAction("  ✓ Views cached");
    } catch (Exception $e) {
        logAction("  ⚠ View cache: " . $e->getMessage());
    }
    
    echo "\n";
    
    // Step 4: Check storage link
    logAction("Checking storage link...");
    try {
        $linkPath = public_path('storage');
        if (!file_exists($linkPath)) {
            Artisan::call('storage:link');
            logAction("  ✓ Storage linked");
        } elseif (is_link($linkPath)) {
            logAction("  ✓ Storage link exists");
        } else {
            logAction("  ⚠ 'public/storage' exists but is not a symlink!");
        }
    } catch (Exception $e) {
        logAction("  ⚠ Storage link: " . $e->getMessage());
    }
    
    echo "\n";
    
    // Step 5: Check permissions
    logAction("Checking directory permissions...");
    
    $checkDirs = [
        'storage/framework/sessions' => storage_path('framework/sessions'),
        'storage/framework/views' => storage_path('framework/views'),
        'storage/framework/cache' => storage_path('framework/cache'),
        'storage/logs' => storage_path('logs'),
        'bootstrap/cache' => base_path('bootstrap/cache'),
    ];
    
    foreach ($checkDirs as $name => $path) {
        if (is_writable($path)) {
            logAction("  ✓ {$name} is writable");
        } else {
            logAction("  ✗ {$name} is NOT writable - Fix permissions in cPanel!");
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ FIX COMPLETED!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "NEXT STEPS:\n";
    echo "1. If you see '✗ NOT writable' errors above:\n";
    echo "   → Go to cPanel File Manager\n";
    echo "   → Right-click 'storage' folder → Change Permissions\n";
    echo "   → Set to 755 and check 'Recurse into subdirectories'\n";
    echo "   → Do the same for 'bootstrap/cache'\n\n";
    
    echo "2. Test your application:\n";
    echo "   → Visit: https://refery.app\n\n";
    
    echo "3. ⚠️ DELETE THIS FILE for security:\n";
    echo "   → Remove /public/fix-production.php\n\n";
    
    echo "If errors persist, see: docs/TROUBLESHOOTING.md\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

// Auto-delete this file for security
$selfDeleted = false;
$thisFile = __FILE__;

try {
    if (file_exists($thisFile) && is_writable($thisFile)) {
        unlink($thisFile);
        $selfDeleted = true;
        echo "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin:20px 0;'>";
        echo "<h2>✅ Script Auto-Eliminado</h2>";
        echo "<p>Por seguridad, este archivo se ha eliminado automáticamente del servidor.</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    // Could not delete, show manual instructions
}

if (!$selfDeleted) {
    echo "<div style='background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin:20px 0;'>";
    echo "<h2>⚠️ ACCIÓN REQUERIDA</h2>";
    echo "<p style='font-weight:bold;'>No se pudo eliminar este archivo automáticamente.</p>";
    echo "<p>Por favor, ELIMÍNALO MANUALMENTE ahora:</p>";
    echo "<ol>";
    echo "<li>Ve a cPanel → File Manager</li>";
    echo "<li>Navega a: <code>public/fix-production.php</code></li>";
    echo "<li>Click derecho → Delete</li>";
    echo "</ol>";
    echo "</div>";
}
