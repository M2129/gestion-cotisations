<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$requested = __DIR__ . $uri;

if ($uri !== '/' && file_exists($requested) && !is_dir($requested)) {
    return false;
}

require_once __DIR__ . '/index.php';
