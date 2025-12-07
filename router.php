<?php
// router.php — used with `php -S` to forward non-file requests to Symfony's front controller (public/index.php)
// Place this file in project root and start server with:
// php -S 127.0.0.1:8000 -t public router.php

// Decode URL and map to public/ directory
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicPath = __DIR__ . '/public' . $uri;

// If the request targets an existing file under public/, let PHP serve it directly
if ($uri !== '/' && file_exists($publicPath) && is_file($publicPath)) {
    return false;
}

// Otherwise forward the request to Symfony's front controller
require_once __DIR__ . '/public/index.php';

