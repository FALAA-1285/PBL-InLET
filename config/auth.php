<?php
/**
 * Authentication and Session Management
 * Includes Session Fixation protection, Secure Cookie parameters, and CSRF utilities.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Detect if connection is HTTPS (direct or behind reverse proxy/load balancer)
    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Enforce secure cookie settings
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * CSRF Protection Helpers
 */
if (!function_exists('getCSRFToken')) {
    function getCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('csrfField')) {
    function csrfField() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

/**
 * Login admin
 * @param string $username
 * @param string $password
 * @return bool
 */
function loginAdmin($username, $password)
{
    $conn = getDBConnection();

    try {
        // Find admin by username
        $stmt = $conn->prepare("SELECT id_admin, username, password_hash, role FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Prevent Session Fixation by regenerating session ID upon privilege elevation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['id_admin'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['is_admin'] = true;

            return true;
        }

        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['id_admin']) && isset($_SESSION['is_admin']);
}

/**
 * Check if admin
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Require login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Require admin
 */
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Logout
 */
function logout()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ../login.php');
    exit();
}

/**
 * Get admin info
 * @return array|null
 */
function getAdminInfo()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id_admin' => $_SESSION['id_admin'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'admin'
    ];
}
?>
