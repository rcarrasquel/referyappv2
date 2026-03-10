<?php

/**
 * Subdomain diagnostics script
 * Upload to public/ directory and access via browser
 * Delete after use for security
 */

// For security: only allow execution for 5 minutes after upload
$creationTime = filectime(__FILE__);
if (time() - $creationTime > 300) {
    die('⛔ This script has been disabled for security. Delete and re-upload if needed.');
}

echo '<h1>🔍 Subdomain Configuration Diagnostics</h1>';
echo '<style>
    body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
    h1 { color: #4ec9b0; }
    .section { background: #252526; padding: 15px; margin: 10px 0; border-left: 3px solid #007acc; }
    .ok { color: #4ec9b0; }
    .warning { color: #ce9178; }
    .error { color: #f48771; }
    .info { color: #9cdcfe; }
</style>';

$basePath = dirname(__DIR__);

// Load Laravel's autoloader and bootstrap
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '<div class="section">';
echo '<h2>📋 Current Request Information</h2>';
echo '<strong>Current Host:</strong> <span class="info">' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') . '</span><br>';
echo '<strong>Request URI:</strong> <span class="info">' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . '</span><br>';
echo '<strong>Server Name:</strong> <span class="info">' . htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'N/A') . '</span><br>';
echo '<strong>Document Root:</strong> <span class="info">' . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</span><br>';
echo '<strong>Script Filename:</strong> <span class="info">' . htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . '</span><br>';
echo '<strong>Current File:</strong> <span class="info">' . htmlspecialchars(__FILE__) . '</span><br>';
echo '</div>';

echo '<div class="section">';
echo '<h2>⚙️ Laravel Configuration</h2>';
$appUrl = config('app.url');
$parsedHost = parse_url($appUrl, PHP_URL_HOST);
echo '<strong>APP_URL:</strong> <span class="info">' . htmlspecialchars($appUrl) . '</span><br>';
echo '<strong>Parsed Host:</strong> <span class="info">' . htmlspecialchars($parsedHost ?? 'N/A') . '</span><br>';
echo '<strong>APP_ENV:</strong> <span class="info">' . htmlspecialchars(config('app.env')) . '</span><br>';
echo '<strong>APP_DEBUG:</strong> <span class="info">' . (config('app.debug') ? 'true' : 'false') . '</span><br>';
echo '<strong>SESSION_DOMAIN:</strong> <span class="info">' . htmlspecialchars(config('session.domain') ?? 'null') . '</span><br>';
echo '</div>';

echo '<div class="section">';
echo '<h2>🌐 Subdomain Detection</h2>';

$currentHost = $_SERVER['HTTP_HOST'] ?? '';
$baseHost = preg_replace('/^www\./i', '', $parsedHost ?? '');

echo '<strong>Base Host:</strong> <span class="info">' . htmlspecialchars($baseHost) . '</span><br>';
echo '<strong>Current Host:</strong> <span class="info">' . htmlspecialchars($currentHost) . '</span><br>';

if (str_ends_with(strtolower($currentHost), '.' . strtolower($baseHost))) {
    $subdomain = str_replace('.' . $baseHost, '', $currentHost);
    echo '<strong>Status:</strong> <span class="ok">✅ Subdomain detected</span><br>';
    echo '<strong>Subdomain:</strong> <span class="ok">' . htmlspecialchars($subdomain) . '</span><br>';
    
    // Check if username exists in database
    try {
        $card = \App\Models\Card::where('username', $subdomain)->where('is_active', true)->first();
        if ($card) {
            echo '<strong>Card Found:</strong> <span class="ok">✅ Yes</span><br>';
            echo '<strong>Card Name:</strong> <span class="info">' . htmlspecialchars($card->name) . '</span><br>';
            echo '<strong>Card Slug:</strong> <span class="info">' . htmlspecialchars($card->slug) . '</span><br>';
        } else {
            echo '<strong>Card Found:</strong> <span class="warning">⚠️ No active card found for username "' . htmlspecialchars($subdomain) . '"</span><br>';
        }
    } catch (Exception $e) {
        echo '<strong>Database Check:</strong> <span class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
    }
} else if ($currentHost === $baseHost) {
    echo '<strong>Status:</strong> <span class="info">ℹ️ Main domain (no subdomain)</span><br>';
} else {
    echo '<strong>Status:</strong> <span class="error">❌ Unknown domain configuration</span><br>';
}
echo '</div>';

echo '<div class="section">';
echo '<h2>📁 Cache Status</h2>';
$routeCache = $basePath . '/bootstrap/cache/routes-v7.php';
$configCache = $basePath . '/bootstrap/cache/config.php';
$servicesCache = $basePath . '/bootstrap/cache/services.php';

echo '<strong>Route Cache:</strong> ' . (file_exists($routeCache) ? '<span class="warning">⚠️ Cached</span>' : '<span class="ok">✅ Not cached</span>') . '<br>';
echo '<strong>Config Cache:</strong> ' . (file_exists($configCache) ? '<span class="warning">⚠️ Cached</span>' : '<span class="ok">✅ Not cached</span>') . '<br>';
echo '<strong>Services Cache:</strong> ' . (file_exists($servicesCache) ? '<span class="warning">⚠️ Cached</span>' : '<span class="ok">✅ Not cached</span>') . '<br>';

if (file_exists($routeCache) || file_exists($configCache)) {
    echo '<br><span class="warning">⚠️ Caches detected. Run <a href="clear-cache.php" style="color: #9cdcfe;">clear-cache.php</a> to clear them.</span>';
}
echo '</div>';

echo '<div class="section">';
echo '<h2>✅ Recommendations</h2>';
$issues = [];

if (config('session.domain') !== '.' . $baseHost) {
    $issues[] = 'Set <code>SESSION_DOMAIN=.' . htmlspecialchars($baseHost) . '</code> in your .env file';
}

if (file_exists($routeCache) || file_exists($configCache)) {
    $issues[] = 'Clear Laravel cache using clear-cache.php';
}

if (!str_ends_with(strtolower($currentHost), '.' . strtolower($baseHost)) && $currentHost !== $baseHost) {
    $issues[] = 'Domain configuration mismatch - APP_URL might be incorrect';
}

if (empty($issues)) {
    echo '<span class="ok">✅ No issues detected</span>';
} else {
    foreach ($issues as $issue) {
        echo '<span class="warning">⚠️ ' . $issue . '</span><br>';
    }
}
echo '</div>';

echo '<br><p><strong>⚠️ SECURITY: Delete this file (check-subdomain.php) after debugging!</strong></p>';
