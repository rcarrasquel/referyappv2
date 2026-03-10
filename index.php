<?php

/**
 * Root index.php for wildcard subdomain support
 * This file redirects all root requests to the public folder
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Remove leading slash
$requestUri = ltrim($requestUri, '/');

// If it's empty (root request), redirect to public/index.php
if (empty($requestUri) || $requestUri === 'index.php') {
    require_once __DIR__ . '/public/index.php';
    exit;
}

// Check if file exists in public directory
$publicFile = __DIR__ . '/public/' . $requestUri;

if (file_exists($publicFile) && is_file($publicFile)) {
    // Serve the file from public directory
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $mimeType);
    readfile($publicFile);
    exit;
}

// Otherwise, let Laravel handle it
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/public/index.php';
