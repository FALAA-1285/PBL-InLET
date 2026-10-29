<?php
/**
 * Database Configuration & Connection Handler
 * Supports Supabase (Transaction Pooler & Direct), Neon, Railway, .env, and Local Dev.
 */

// 1. Lightweight .env loader (only used when running in environments with a local .env file)
if (!function_exists('loadEnvFile')) {
    function loadEnvFile($path) {
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Strip surrounding single or double quotes
                if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        return true;
    }
}

// Load local .env if available
loadEnvFile(__DIR__ . '/../.env');

// Helper to get environment variable with fallback
if (!function_exists('getEnvValue')) {
    function getEnvValue($key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

// 2. Application Environment Settings
$app_env = getEnvValue('APP_ENV', (getEnvValue('VERCEL') ? 'production' : 'development'));
$app_debug_raw = getEnvValue('APP_DEBUG', ($app_env === 'production' ? 'false' : 'true'));
$app_debug = filter_var($app_debug_raw, FILTER_VALIDATE_BOOLEAN);

if (!defined('APP_ENV')) define('APP_ENV', $app_env);
if (!defined('APP_DEBUG')) define('APP_DEBUG', $app_debug);

// 3. Database Credentials Configuration
// Method A: Direct Connection URL (DATABASE_URL / SUPABASE_DB_URL)
$database_url = getEnvValue('DATABASE_URL', getEnvValue('SUPABASE_DB_URL', getEnvValue('SUPABASE_DATABASE_URL')));

// Method B: Supabase Short-Keys (Project Reference + Database Password)
$supabase_ref = getEnvValue('SUPABASE_PROJECT_REF', getEnvValue('SUPABASE_REF'));
$supabase_pass = getEnvValue('SUPABASE_DB_PASSWORD', getEnvValue('SUPABASE_PASSWORD'));
$supabase_region = getEnvValue('SUPABASE_REGION', 'ap-southeast-1'); // Default Southeast Asia (Singapore)
$supabase_mode = getEnvValue('SUPABASE_MODE', 'pooler'); // 'pooler' (default, port 6543) or 'direct' (port 5432)

$db_sslmode = getEnvValue('DB_SSLMODE', ($app_env === 'production' ? 'require' : 'prefer'));

if (!empty($database_url)) {
    // Parse connection string
    $dbparts = parse_url($database_url);
    $db_host = $dbparts['host'] ?? 'localhost';
    $db_port = (string)($dbparts['port'] ?? '5432');
    $db_user = $dbparts['user'] ?? 'postgres';
    $db_pass = $dbparts['pass'] ?? '';
    $db_name = isset($dbparts['path']) ? ltrim($dbparts['path'], '/') : 'postgres';
    
    // Parse query params for sslmode
    if (!empty($dbparts['query'])) {
        parse_str($dbparts['query'], $query_params);
        if (isset($query_params['sslmode'])) {
            $db_sslmode = $query_params['sslmode'];
        }
    }
} elseif (!empty($supabase_ref) && !empty($supabase_pass)) {
    // Auto-configure from Supabase keys
    if ($supabase_mode === 'direct') {
        $db_host = "db.{$supabase_ref}.supabase.co";
        $db_port = '5432';
        $db_user = 'postgres';
    } else {
        // Pooler mode (Supavisor / Transaction pooler - Recommended for Vercel/serverless)
        $db_host = "aws-0-{$supabase_region}.pooler.supabase.com";
        $db_port = '6543';
        $db_user = "postgres.{$supabase_ref}";
    }
    $db_pass = $supabase_pass;
    $db_name = getEnvValue('DB_DATABASE', 'postgres');
    $db_sslmode = 'require';
} else {
    // Method C: Individual environment variables with local development fallback
    $db_host = getEnvValue('DB_HOST', 'localhost');
    $db_port = (string)getEnvValue('DB_PORT', '5432');
    $db_name = getEnvValue('DB_DATABASE', getEnvValue('DB_NAME', 'Inlet_pbl'));
    $db_user = getEnvValue('DB_USERNAME', getEnvValue('DB_USER', 'postgres'));
    $db_pass = getEnvValue('DB_PASSWORD', getEnvValue('DB_PASS', ''));
}

// Define constants for backwards compatibility
if (!defined('DB_HOST')) define('DB_HOST', $db_host);
if (!defined('DB_PORT')) define('DB_PORT', $db_port);
if (!defined('DB_NAME')) define('DB_NAME', $db_name);
if (!defined('DB_USER')) define('DB_USER', $db_user);
if (!defined('DB_PASS')) define('DB_PASS', $db_pass);
if (!defined('DB_SSLMODE')) define('DB_SSLMODE', $db_sslmode);

/**
 * Get Database Connection (Singleton)
 * @return PDO
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            if (defined('DB_SSLMODE') && !empty(DB_SSLMODE)) {
                $dsn .= ";sslmode=" . DB_SSLMODE;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 8,
            ];

            // CRITICAL FOR SUPABASE TRANSACTION POOLER (PORT 6543):
            // Emulate prepares to prevent "prepared statement already exists / does not exist" errors in transaction mode
            if (DB_PORT === '6543' || strpos(DB_HOST, 'pooler.supabase.com') !== false) {
                $options[PDO::ATTR_EMULATE_PREPARES] = true;
            }

            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Database connection failure: " . $e->getMessage());
            
            if (defined('APP_DEBUG') && APP_DEBUG === true) {
                die("<h3>Database Connection Error (Debug Mode Active)</h3><p>" . htmlspecialchars($e->getMessage()) . "</p><p>Host: " . htmlspecialchars(DB_HOST) . ":" . htmlspecialchars(DB_PORT) . "</p><p>Database: " . htmlspecialchars(DB_NAME) . "</p><p>User: " . htmlspecialchars(DB_USER) . "</p>");
            } else {
                http_response_code(500);
                die("<!DOCTYPE html><html><head><title>Service Unavailable</title><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#1e293b}h1{font-size:24px}p{color:#64748b}</style></head><body><h1>Service Temporarily Unavailable</h1><p>Database connection could not be established. Please check your Supabase configuration or try again later.</p></body></html>");
            }
        }
    }
    
    return $conn;
}
?>
