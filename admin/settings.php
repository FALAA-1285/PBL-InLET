<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Tab aktif (mengikuti pola di research.php)
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'page-titles';

// Get current admin ID and verify it exists
$admin_id = $_SESSION['id_admin'] ?? null;
if ($admin_id) {
    // Verify admin exists in database
    try {
        $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE id_admin = :id");
        $stmt->execute(['id' => $admin_id]);
        $admin_exists = $stmt->fetch();
        if (!$admin_exists) {
            $admin_id = null; // Admin doesn't exist, set to null
        }
    } catch (PDOException $e) {
        $admin_id = null; // On error, set to null
    }
}

// Check and add missing columns if they don't exist
$required_columns = [
    'page_titles' => "JSONB DEFAULT '{}'::jsonb",
    'footer_logo' => 'VARCHAR(255)',
    'footer_title' => 'VARCHAR(255)',
    'copyright_text' => 'TEXT',
    'contact_email' => 'VARCHAR(255)',
    'contact_phone' => 'VARCHAR(100)',
    'contact_address' => 'TEXT'
];

foreach ($required_columns as $column => $definition) {
    try {
        $stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'settings' AND column_name = :column");
        $stmt->execute(['column' => $column]);
        $column_exists = $stmt->fetch();
        if (!$column_exists) {
            $conn->exec("ALTER TABLE settings ADD COLUMN $column $definition");
        }
    } catch (PDOException $e) {
        // Column might already exist or error, continue
        error_log("Error checking/adding column $column: " . $e->getMessage());
    }
}

// Get current settings or create default if not exists
$stmt = $conn->query("SELECT * FROM settings ORDER BY id_setting LIMIT 1");
$settings = $stmt->fetch();

// Parse page_titles JSON
$page_titles = [];
if (!empty($settings['page_titles'])) {
    if (is_string($settings['page_titles'])) {
        $page_titles = json_decode($settings['page_titles'], true) ?: [];
    } else {
        $page_titles = $settings['page_titles'];
    }
}

// Default page titles structure
$default_pages = [
    'home' => ['title' => 'InLET - Information And Learning Engineering Technology', 'subtitle' => 'State Polytechnic of Malang'],
    'research' => ['title' => 'Research - InLET', 'subtitle' => 'Our Research Projects'],
    'member' => ['title' => 'Members - InLET', 'subtitle' => 'Our Team'],
    'news' => ['title' => 'News - InLET', 'subtitle' => 'Latest Updates'],
    'tool_loans' => ['title' => 'Tool Loans - InLET', 'subtitle' => 'Lab Equipment Rental'],
    'attendance' => ['title' => 'Attendance - InLET', 'subtitle' => 'Track Your Attendance'],
    'guestbook' => ['title' => 'Guestbook - InLET', 'subtitle' => 'Leave Your Message']
];

// Merge with defaults
foreach ($default_pages as $page => $default) {
    if (!isset($page_titles[$page])) {
        $page_titles[$page] = $default;
    } else {
        $page_titles[$page] = array_merge($default, $page_titles[$page]);
    }
}

// If no settings exist, create a default one
if (!$settings) {
    try {
        $page_titles_json = json_encode($page_titles);
        $stmt = $conn->prepare("INSERT INTO settings (site_title, site_subtitle, page_titles, created_at, updated_at) VALUES (:site_title, :site_subtitle, :page_titles::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) RETURNING *");
        $stmt->execute([
            'site_title' => 'InLET - Information And Learning Engineering Technology',
            'site_subtitle' => 'State Polytechnic of Malang',
            'page_titles' => $page_titles_json
        ]);
        $settings = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Error creating default settings: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    // Get page titles
    $page_titles = [];
    $pages = ['home', 'research', 'member', 'news', 'tool_loans', 'attendance', 'guestbook'];
    foreach ($pages as $page) {
        $page_titles[$page] = [
            'title' => trim($_POST["{$page}_title"] ?? ''),
            'subtitle' => trim($_POST["{$page}_subtitle"] ?? '')
        ];
    }

    $footer_title = trim($_POST['footer_title'] ?? '');
    $copyright_text = trim($_POST['copyright_text'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $contact_address = trim($_POST['contact_address'] ?? '');

    $site_logo = $settings['site_logo'] ?? ''; // Keep existing if not updated
    $footer_logo = $settings['footer_logo'] ?? ''; // Keep existing if not updated

    // Handle site_logo upload
    if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['site_logo_file'], 'settings/');
        if ($uploadResult['success']) {
            // Delete old logo if exists
            if (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo'])) {
                @unlink('../' . $settings['site_logo']);
            }
            $site_logo = $uploadResult['path'];
        } else {
            $message = $uploadResult['message'];
            $message_type = 'error';
        }
    } elseif (!empty($_POST['site_logo_url'])) {
        // Use URL if provided
        $site_logo = trim($_POST['site_logo_url']);
    }

    // Handle footer_logo upload
    if (isset($_FILES['footer_logo_file']) && $_FILES['footer_logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['footer_logo_file'], 'settings/');
        if ($uploadResult['success']) {
            // Delete old logo if exists
            if (!empty($settings['footer_logo']) && file_exists('../' . $settings['footer_logo'])) {
                @unlink('../' . $settings['footer_logo']);
            }
            $footer_logo = $uploadResult['path'];
        } else {
            if (empty($message)) {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }
    } elseif (!empty($_POST['footer_logo_url'])) {
        // Use URL if provided
        $footer_logo = trim($_POST['footer_logo_url']);
    }

    if (empty($message)) {
        // Validate at least one page title
        $has_title = false;
        foreach ($page_titles as $page_data) {
            if (!empty($page_data['title'])) {
                $has_title = true;
                break;
            }
        }

        if (!$has_title) {
                $message = 'At least one page must have a title!';
            $message_type = 'error';
        } else {
            try {
                $page_titles_json = json_encode($page_titles);
                $stmt = $conn->prepare("UPDATE settings SET 
                    page_titles = :page_titles::jsonb,
                    site_logo = :site_logo,
                    footer_logo = :footer_logo,
                    footer_title = :footer_title,
                    copyright_text = :copyright_text,
                    contact_email = :contact_email,
                    contact_phone = :contact_phone,
                    contact_address = :contact_address,
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                    WHERE id_setting = :id_setting");

                $stmt->execute([
                    'id_setting' => $settings['id_setting'],
                    'page_titles' => $page_titles_json,
                    'site_logo' => $site_logo ?: null,
                    'footer_logo' => $footer_logo ?: null,
                    'footer_title' => $footer_title ?: null,
                    'copyright_text' => $copyright_text ?: null,
                    'contact_email' => $contact_email ?: null,
                    'contact_phone' => $contact_phone ?: null,
                    'contact_address' => $contact_address ?: null,
                    'updated_by' => $admin_id ?: null
                ]);

                $message = 'Settings updated successfully!';
                $message_type = 'success';

                // Reload settings
                $stmt = $conn->prepare("SELECT * FROM settings WHERE id_setting = :id");
                $stmt->execute(['id' => $settings['id_setting']]);
                $settings = $stmt->fetch();

                // Reload page_titles
                if (!empty($settings['page_titles'])) {
                    if (is_string($settings['page_titles'])) {
                        $page_titles = json_decode($settings['page_titles'], true) ?: [];
                    } else {
                        $page_titles = $settings['page_titles'];
                    }
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

require __DIR__ . '/../views/admin/settings.php';
