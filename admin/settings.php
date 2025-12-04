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
            $message = 'Minimal satu halaman harus memiliki title!';
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

                $message = 'Settings berhasil diupdate!';
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: var(--light);
        }

        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-section h2 i {
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="url"],
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group small {
            display: block;
            margin-top: 0.5rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .logo-preview {
            margin-top: 0.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
        }

        .logo-preview.current {
            margin-bottom: 1.5rem;
        }

        .logo-preview img {
            max-width: 200px;
            max-height: 100px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 2rem 0;
        }

        .page-title-group {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 8px 8px 8px rgba(146, 15, 15, 0.05);
        }

        .page-title-group h3 {
            margin: 0 0 1rem 0;
            color: #475569;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php $active_page = 'Settings';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-settings-3-line"></i> Kelola Site Settings</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i class="ri-<?php echo $message_type === 'success' ? 'check-line' : 'error-warning-line'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Settings Form -->
                <form method="POST" enctype="multipart/form-data" id="settingsForm">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="tabs">
                        <a href="?tab=page-titles&page=1"
                            class="tab <?php echo ($current_tab === 'page-titles') ? 'active' : ''; ?>">Artikel</a>
                        <a href="?tab=logos&page=1"
                            class="tab <?php echo ($current_tab === 'logos') ? 'active' : ''; ?>">Logos</a>
                        <a href="?tab=footer&page=1"
                            class="tab <?php echo ($current_tab === 'footer') ? 'active' : ''; ?>">Footer</a>
                        <a href="?tab=contact&page=1"
                            class="tab <?php echo ($current_tab === 'contact') ? 'active' : ''; ?>">Contact</a>
                    </div>

                    <!-- Page Titles Tab -->
                    <div class="tab-content <?php echo ($current_tab === 'page-titles') ? 'active' : ''; ?>"
                        id="page-titles">
                        <div class="form-section">
                            <h2><i class="ri-global-line"></i> Page Titles & Subtitles</h2>
                            <p style="color: #64748b; margin-bottom: 2rem;">Atur title dan subtitle untuk setiap
                                halaman secara terpisah</p>

                            <?php
                            $page_labels = [
                                'home' => ['icon' => 'ri-home-line', 'label' => 'Home Page'],
                                'research' => ['icon' => 'ri-flask-line', 'label' => 'Research Page'],
                                'member' => ['icon' => 'ri-team-line', 'label' => 'Members Page'],
                                'news' => ['icon' => 'ri-newspaper-line', 'label' => 'News Page'],
                                'tool_loans' => ['icon' => 'ri-tools-line', 'label' => 'Tool Loans Page'],
                                'attendance' => ['icon' => 'ri-calendar-check-line', 'label' => 'Attendance Page'],
                                'guestbook' => ['icon' => 'ri-book-open-line', 'label' => 'Guestbook Page']
                            ];

                            foreach ($page_labels as $page => $info):
                                $current = $page_titles[$page] ?? ['title' => '', 'subtitle' => ''];
                                ?>
                                <div class="page-title-group">
                                    <h3><i class="<?php echo $info['icon']; ?>"></i> <?php echo $info['label']; ?></h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="<?php echo $page; ?>_title">Title *</label>
                                            <input type="text" id="<?php echo $page; ?>_title"
                                                name="<?php echo $page; ?>_title"
                                                value="<?php echo htmlspecialchars($current['title'] ?? ''); ?>"
                                                placeholder="Enter page title">
                                        </div>
                                        <div class="form-group">
                                            <label for="<?php echo $page; ?>_subtitle">Subtitle</label>
                                            <input type="text" id="<?php echo $page; ?>_subtitle"
                                                name="<?php echo $page; ?>_subtitle"
                                                value="<?php echo htmlspecialchars($current['subtitle'] ?? ''); ?>"
                                                placeholder="Enter page subtitle">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Logos Tab -->
                    <div class="tab-content <?php echo ($current_tab === 'logos') ? 'active' : ''; ?>" id="logos">
                        <div class="form-section">
                            <h2><i class="ri-image-line"></i> Logo Settings</h2>

                            <!-- Site Logo (Navbar) -->
                            <div class="form-group">
                                <label for="site_logo">Logo Navbar</label>
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <div class="logo-preview current">
                                        <strong style="display: block; margin-bottom: 0.5rem; color: #475569;">Logo Saat
                                            Ini:</strong>
                                        <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>"
                                            alt="Current Site Logo"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span style="display:none; color: #ef4444;">Gambar tidak dapat dimuat</span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="site_logo_file" name="site_logo_file" accept="image/*">
                                <small>Upload logo baru untuk navbar (JPG, PNG, GIF, WEBP - Max 5MB)</small>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label for="site_logo_url">Atau Masukkan URL Logo</label>
                                    <input type="url" id="site_logo_url" name="site_logo_url"
                                        placeholder="https://example.com/logo.png">
                                    <small>Jika menggunakan URL, kosongkan field upload di atas</small>
                                </div>
                            </div>

                            <div class="section-divider"></div>

                            <!-- Footer Logo -->
                            <div class="form-group">
                                <label for="footer_logo">Logo Footer</label>
                                <?php if (!empty($settings['footer_logo'])): ?>
                                    <div class="logo-preview current">
                                        <strong style="display: block; margin-bottom: 0.5rem; color: #475569;">Logo Saat
                                            Ini:</strong>
                                        <img src="../<?php echo htmlspecialchars($settings['footer_logo']); ?>"
                                            alt="Current Footer Logo"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span style="display:none; color: #ef4444;">Gambar tidak dapat dimuat</span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="footer_logo_file" name="footer_logo_file" accept="image/*">
                                <small>Upload logo baru untuk footer (JPG, PNG, GIF, WEBP - Max 5MB)</small>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label for="footer_logo_url">Atau Masukkan URL Logo</label>
                                    <input type="url" id="footer_logo_url" name="footer_logo_url"
                                        placeholder="https://example.com/footer-logo.png">
                                    <small>Jika menggunakan URL, kosongkan field upload di atas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tab -->
                    <div class="tab-content <?php echo ($current_tab === 'footer') ? 'active' : ''; ?>" id="footer">
                        <div class="form-section">
                            <h2><i class="ri-file-list-3-line"></i> Footer Settings</h2>

                            <div class="form-group">
                                <label for="footer_title">Footer Title</label>
                                <input type="text" id="footer_title" name="footer_title"
                                    value="<?php echo htmlspecialchars($settings['footer_title'] ?? ''); ?>"
                                    placeholder="Enter footer title">
                                <small>Judul yang ditampilkan di footer</small>
                            </div>

                            <div class="form-group">
                                <label for="copyright_text">Copyright Text</label>
                                <textarea id="copyright_text" name="copyright_text"
                                    placeholder="© 2024 InLET. All rights reserved."><?php echo htmlspecialchars($settings['copyright_text'] ?? ''); ?></textarea>
                                <small>Teks copyright yang ditampilkan di footer</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Tab -->
                    <div class="tab-content <?php echo ($current_tab === 'contact') ? 'active' : ''; ?>" id="contact">
                        <div class="form-section">
                            <h2><i class="ri-contacts-line"></i> Contact Information</h2>

                            <div class="form-group">
                                <label for="contact_email">Email</label>
                                <input type="email" id="contact_email" name="contact_email"
                                    value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>"
                                    placeholder="contact@inlet.edu">
                                <small>Alamat email kontak</small>
                            </div>

                            <div class="form-group">
                                <label for="contact_phone">Phone</label>
                                <input type="tel" id="contact_phone" name="contact_phone"
                                    value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>"
                                    placeholder="+62 123 456 7890">
                                <small>Nomor telepon kontak</small>
                            </div>

                            <div class="form-group">
                                <label for="contact_address">Address</label>
                                <textarea id="contact_address" name="contact_address"
                                    placeholder="Jl. Soekarno Hatta No. 9, Malang, Jawa Timur"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                                <small>Alamat lengkap</small>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Submit Button -->
            <div class="form-section" style="text-align: center;">
                <button type="submit" class="btn-submit">
                    <i class="ri-save-line"></i> Update All Settings
                </button>
            </div>
            </form>
        </div>
        </div>
    </main>

</body>

</html>