<?php
/**
 * Vercel Serverless Entry Point & Front Controller Router
 * InLET - Information and Learning Engineering Technology
 */

// 1. Resolve request URI and sanitize path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';

// Suppress display of PHP warnings and notices in production to protect UI/UX
$is_debug = getenv('APP_DEBUG') === 'true' || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true');
if (!$is_debug) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
}

$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'] ?? '/';

// 2. Base directory (project root)
$base_dir = realpath(__DIR__ . '/..');

// 3. Normalize route path
$clean_path = ltrim($path, '/');
if ($clean_path === '' || $clean_path === 'index' || $clean_path === 'index.php') {
    $target_rel = 'index.php';
} else {
    $target_rel = $clean_path;
}

// 4. Directory default redirects
if ($target_rel === 'admin' || $target_rel === 'admin/') {
    header('Location: /admin/dashboard.php');
    exit;
}
if ($target_rel === 'service' || $target_rel === 'service/') {
    header('Location: /service/peminjaman.php');
    exit;
}

// 5. Automatic .php extension resolution if omitted (e.g. /login -> /login.php)
if (!pathinfo($target_rel, PATHINFO_EXTENSION)) {
    if (file_exists($base_dir . '/' . $target_rel . '.php')) {
        $target_rel .= '.php';
    }
}

// 6. Security block: Prevent direct execution of internal includes and configs
$blocked_prefixes = ['config/', 'views/', '.git', 'setup/', 'database/', 'docs/'];
foreach ($blocked_prefixes as $prefix) {
    if (strpos($target_rel, $prefix) === 0) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1><p>Direct access to internal application components is restricted.</p></body></html>";
        exit;
    }
}

// Block file types that should never be served directly
$blocked_exts = ['sql', 'md', 'env', 'json', 'lock', 'log'];
$target_ext = strtolower(pathinfo($target_rel, PATHINFO_EXTENSION));
if (in_array($target_ext, $blocked_exts, true)) {
    http_response_code(403);
    echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1></body></html>";
    exit;
}

// 7. Resolve full path and prevent path traversal
$target_full = realpath($base_dir . '/' . $target_rel);

if ($target_full === false || strpos($target_full, $base_dir) !== 0 || !file_exists($target_full)) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><title>404 Not Found</title><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#1e293b}h1{font-size:32px}p{color:#64748b}</style></head><body><h1>404 Not Found</h1><p>The requested page could not be found.</p><a href='/'>Return to Homepage</a></body></html>";
    exit;
}

// 8. Handle static assets (CSS, JS, Images, Fonts) with caching & MIME headers
$mime_types = [
    'css'   => 'text/css; charset=utf-8',
    'js'    => 'application/javascript; charset=utf-8',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'webp'  => 'image/webp',
    'svg'   => 'image/svg+xml',
    'ico'   => 'image/x-icon',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'pdf'   => 'application/pdf',
];

if (isset($mime_types[$target_ext])) {
    header('Content-Type: ' . $mime_types[$target_ext]);
    header('Cache-Control: public, max-age=86400, stale-while-revalidate=604800');
    readfile($target_full);
    exit;
}

// 9. Execute PHP Script in its native execution context
if ($target_ext === 'php') {
    // Enable Output Buffering to prevent "headers already sent" warnings
    ob_start();

    // Reconfigure server globals so that relative includes and links behave identically to native Apache
    $_SERVER['SCRIPT_FILENAME'] = $target_full;
    $_SERVER['SCRIPT_NAME'] = '/' . $target_rel;
    $_SERVER['PHP_SELF'] = '/' . $target_rel;

    // Switch working directory to script's own directory
    chdir(dirname($target_full));

    // Execute script
    require $target_full;

    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    exit;
}

// Fallback: 404
http_response_code(404);
echo "404 Not Found";
exit;
