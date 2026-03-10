<?php
/**
 * Quick diagnostic for wildcard subdomain setup
 * Access this file directly to check configuration
 */

// For security: only allow execution for 5 minutes after upload
$creationTime = filectime(__FILE__);
if (time() - $creationTime > 300) {
    die('⛔ This script has been disabled for security. Delete and re-upload if needed.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wildcard Subdomain Diagnostic</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            background: #1a1a1a; 
            color: #00ff00; 
            padding: 20px;
            line-height: 1.6;
        }
        h1 { color: #00ffff; }
        .section { 
            background: #2a2a2a; 
            padding: 15px; 
            margin: 10px 0; 
            border-left: 4px solid #00ff00;
            border-radius: 5px;
        }
        .label { color: #ffff00; font-weight: bold; }
        .value { color: #00ff00; }
        .warning { color: #ff6600; }
        .error { color: #ff0000; }
        .success { color: #00ff00; }
    </style>
</head>
<body>
    <h1>🔍 Wildcard Subdomain Diagnostic</h1>
    
    <div class="section">
        <h2>📍 Server Information</h2>
        <p><span class="label">Document Root:</span><br>
        <span class="value"><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'); ?></span></p>
        
        <p><span class="label">Current File Path:</span><br>
        <span class="value"><?php echo htmlspecialchars(__FILE__); ?></span></p>
        
        <p><span class="label">Public Folder Exists:</span>
        <span class="<?php echo is_dir(__DIR__ . '/public') ? 'success' : 'error'; ?>">
            <?php echo is_dir(__DIR__ . '/public') ? '✅ Yes' : '❌ No'; ?>
        </span></p>
        
        <p><span class="label">Public/index.php Exists:</span>
        <span class="<?php echo file_exists(__DIR__ . '/public/index.php') ? 'success' : 'error'; ?>">
            <?php echo file_exists(__DIR__ . '/public/index.php') ? '✅ Yes' : '❌ No'; ?>
        </span></p>
        
        <p><span class="label">Root index.php Exists:</span>
        <span class="<?php echo file_exists(__DIR__ . '/index.php') && __FILE__ !== __DIR__ . '/index.php' ? 'success' : 'warning'; ?>">
            <?php echo file_exists(__DIR__ . '/index.php') && __FILE__ !== __DIR__ . '/index.php' ? '✅ Yes' : '⚠️ Not found or this is it'; ?>
        </span></p>
        
        <p><span class="label">.htaccess in Root:</span>
        <span class="<?php echo file_exists(__DIR__ . '/.htaccess') ? 'success' : 'warning'; ?>">
            <?php echo file_exists(__DIR__ . '/.htaccess') ? '✅ Yes' : '⚠️ No'; ?>
        </span></p>
    </div>
    
    <div class="section">
        <h2>🌐 Request Information</h2>
        <p><span class="label">Host:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A'); ?></span></p>
        <p><span class="label">Request URI:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?></span></p>
        <p><span class="label">Script Name:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A'); ?></span></p>
        <p><span class="label">Script Filename:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A'); ?></span></p>
    </div>
    
    <div class="section">
        <h2>📝 Configuration Recommendations</h2>
        
        <?php
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $currentDir = __DIR__;
        
        echo "<p><span class='label'>Status:</span> ";
        
        if ($docRoot === $currentDir) {
            echo "<span class='success'>✅ Wildcard is pointing to the correct root folder</span></p>";
            
            if (file_exists(__DIR__ . '/index.php') && __FILE__ !== __DIR__ . '/index.php') {
                echo "<p><span class='success'>✅ Root index.php proxy exists</span></p>";
                echo "<p><span class='label'>Next Steps:</span></p>";
                echo "<ol>";
                echo "<li>Make sure all files are uploaded (.htaccess, index.php in root)</li>";
                echo "<li>Try accessing: <a href='/' style='color: #00ffff;'>https://" . htmlspecialchars($_SERVER['HTTP_HOST']) . "/</a></li>";
                echo "<li>If still not working, try: <a href='/public/' style='color: #00ffff;'>https://" . htmlspecialchars($_SERVER['HTTP_HOST']) . "/public/</a></li>";
                echo "</ol>";
            } else {
                echo "<p><span class='warning'>⚠️ Upload the index.php file to the root directory</span></p>";
            }
        } else if (strpos($docRoot, '/public') !== false) {
            echo "<span class='warning'>⚠️ Wildcard is pointing to the public folder</span></p>";
            echo "<p><span class='label'>Solution:</span> In cPanel, change the wildcard subdomain Document Root to point to the parent folder (remove '/public' from the path)</p>";
        } else {
            echo "<span class='error'>❌ Wildcard configuration issue</span></p>";
            echo "<p><span class='label'>Current Document Root:</span> <span class='value'>" . htmlspecialchars($docRoot) . "</span></p>";
            echo "<p><span class='label'>Expected:</span> <span class='value'>" . htmlspecialchars($currentDir) . "</span></p>";
            echo "<p><span class='label'>Solution:</span> In cPanel Domains/Subdomains, set the wildcard subdomain Document Root to: <span class='value'>" . htmlspecialchars($currentDir) . "</span></p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🛠️ How to Fix in cPanel</h2>
        <ol>
            <li>Go to <strong>cPanel → Domains</strong> (or Subdomains)</li>
            <li>Find the wildcard entry: <code>*.refery.app</code></li>
            <li>Click <strong>Manage</strong> or <strong>Edit</strong></li>
            <li>Set Document Root to: <code><?php echo htmlspecialchars($currentDir); ?></code></li>
            <li>Save changes and wait 1-2 minutes for propagation</li>
        </ol>
    </div>
    
    <p style="margin-top: 30px;"><span class="error">⚠️ DELETE THIS FILE (diagnostic.php) AFTER DEBUGGING!</span></p>
</body>
</html>
