<?php
/**
 * Variables provided by admin/settings.php
 *
 * @var string $message
 * @var string $message_type
 * @var string $current_tab
 * @var array $page_titles
 * @var array $settings
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/settings.css">
</head>

<body>
    <?php $active_page = 'Settings';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-settings-3-line"></i>Manage Settings</h1>

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
                            class="tab <?php echo ($current_tab === 'page-titles') ? 'active' : ''; ?>">Articles</a>
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
                            <p style="color: #64748b; margin-bottom: 2rem;">Set title and subtitle for each page separately</p>

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
                                        <strong style="display: block; margin-bottom: 0.5rem; color: #475569;">Current Logo:</strong>
                                        <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>"
                                            alt="Current Site Logo"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span style="display:none; color: #ef4444;">Image cannot be loaded</span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="site_logo_file" name="site_logo_file" accept="image/*">
                                <small>Upload new logo for navbar (JPG, PNG, GIF, WEBP - Max 5MB)</small>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label for="site_logo_url">Or Enter Logo URL</label>
                                    <input type="url" id="site_logo_url" name="site_logo_url"
                                        placeholder="https://example.com/logo.png">
                                    <small>If using URL, leave the upload field above empty</small>
                                </div>
                            </div>

                            <div class="section-divider"></div>

                            <!-- Footer Logo -->
                            <div class="form-group">
                                <label for="footer_logo">Logo Footer</label>
                                <?php if (!empty($settings['footer_logo'])): ?>
                                    <div class="logo-preview current">
                                        <strong style="display: block; margin-bottom: 0.5rem; color: #475569;">Current Logo:</strong>
                                        <img src="../<?php echo htmlspecialchars($settings['footer_logo']); ?>"
                                            alt="Current Footer Logo"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span style="display:none; color: #ef4444;">Image cannot be loaded</span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="footer_logo_file" name="footer_logo_file" accept="image/*">
                                <small>Upload new logo for footer (JPG, PNG, GIF, WEBP - Max 5MB)</small>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label for="footer_logo_url">Or Enter Logo URL</label>
                                    <input type="url" id="footer_logo_url" name="footer_logo_url"
                                        placeholder="https://example.com/footer-logo.png">
                                    <small>If using URL, leave the upload field above empty</small>
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
                                <small>Title displayed in footer</small>
                            </div>

                            <div class="form-group">
                                <label for="copyright_text">Copyright Text</label>
                                <textarea id="copyright_text" name="copyright_text"
                                    placeholder="© 2024 InLET. All rights reserved."><?php echo htmlspecialchars($settings['copyright_text'] ?? ''); ?></textarea>
                                <small>Copyright text displayed in footer</small>
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
                                <small>Contact email address</small>
                            </div>

                            <div class="form-group">
                                <label for="contact_phone">Phone</label>
                                <input type="tel" id="contact_phone" name="contact_phone"
                                    value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>"
                                    placeholder="+62 123 456 7890">
                                <small>Contact phone number</small>
                            </div>

                            <div class="form-group">
                                <label for="contact_address">Address</label>
                                <textarea id="contact_address" name="contact_address"
                                    placeholder="Jl. Soekarno Hatta No. 9, Malang, Jawa Timur"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                                <small>Full address</small>
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