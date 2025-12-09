<?php
/**
 * Settings Helper Functions
 * Functions to retrieve and manage site settings
 */

require_once __DIR__ . '/database.php';

/**
 * Get all settings from database
 * @return array|false Settings array or false on error
 */
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->query("SELECT * FROM settings ORDER BY id_setting LIMIT 1");
            $settings = $stmt->fetch();
            
            // If no settings found, initialize as empty array
            if ($settings === false) {
                $settings = [];
            }
            
            // Parse page_titles JSON if exists
            if (!empty($settings['page_titles'])) {
                if (is_string($settings['page_titles'])) {
                    $settings['page_titles'] = json_decode($settings['page_titles'], true) ?: [];
                }
            } else {
                $settings['page_titles'] = [];
            }
        } catch (PDOException $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            // Return empty array instead of false
            $settings = [];
        }
    }
    
    return $settings;
}

/**
 * Get page title and subtitle for a specific page
 * @param string $page_name Page name (home, research, member, news, tool_loans, attendance, guestbook)
 * @return array ['title' => string, 'subtitle' => string]
 */
function getPageTitle($page_name) {
    $settings = getSettings();
    
    // Check if settings is empty array
    if (empty($settings)) {
        // Default fallback
        return [
            'title' => 'InLET - Information And Learning Engineering Technology',
            'subtitle' => 'State Polytechnic of Malang'
        ];
    }
    
    $page_titles = $settings['page_titles'] ?? [];
    
    if (isset($page_titles[$page_name])) {
        return [
            'title' => $page_titles[$page_name]['title'] ?? '',
            'subtitle' => $page_titles[$page_name]['subtitle'] ?? ''
        ];
    }
    
    // Fallback to default
    return [
        'title' => $settings['site_title'] ?? 'InLET - Information And Learning Engineering Technology',
        'subtitle' => $settings['site_subtitle'] ?? 'State Polytechnic of Malang'
    ];
}

/**
 * Get site logo URL
 * @return string Logo URL or empty string
 */
function getSiteLogo() {
    $settings = getSettings();
    if (!empty($settings['site_logo'])) {
        return $settings['site_logo'];
    }
    return 'assets/logo.png'; // Default fallback
}

/**
 * Get footer logo URL
 * @return string Footer logo URL or empty string
 */
function getFooterLogo() {
    $settings = getSettings();
    if (!empty($settings['footer_logo'])) {
        return $settings['footer_logo'];
    }
    return 'assets/logoPutih.png'; // Default fallback
}

/**
 * Get footer settings
 * @return array Footer settings
 */
function getFooterSettings() {
    $settings = getSettings();
    if (empty($settings)) {
        return [
            'title' => '',
            'copyright' => '',
            'logo' => 'assets/logoPutih.png'
        ];
    }
    
    return [
        'title' => $settings['footer_title'] ?? '',
        'copyright' => $settings['copyright_text'] ?? '',
        'logo' => $settings['footer_logo'] ?? 'assets/logoPutih.png'
    ];
}

/**
 * Get contact information
 * @return array Contact information
 */
function getContactInfo() {
    $settings = getSettings();
    if (empty($settings)) {
        return [
            'email' => '',
            'phone' => '',
            'address' => ''
        ];
    }
    
    return [
        'email' => $settings['contact_email'] ?? '',
        'phone' => $settings['contact_phone'] ?? '',
        'address' => $settings['contact_address'] ?? ''
    ];
}
?>

