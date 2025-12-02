<?php
$menu_items = [
    [
        'id' => 'dashboard',
        'label' => 'Dashboard',
        'href' => 'dashboard.php',
        'icon' => 'ri-dashboard-line',
    ],
    [
        'id' => 'Settings',
        'label' => 'Settings',
        'href' => 'settings.php',
        'icon' => 'ri-settings-3-line',
    ],
    [
        'id' => 'research',
        'label' => 'Research',
        'href' => 'research.php',
        'icon' => 'ri-flask-line',
    ],
    [
        'id' => 'member',
        'label' => 'Members',
        'href' => 'member.php',
        'icon' => 'ri-team-line',
    ],
    [
        'id' => 'news',
        'label' => 'News',
        'href' => 'news.php',
        'icon' => 'ri-newspaper-line',
    ],
    [
        'id' => 'mitra',
        'label' => 'Lab Partners',
        'href' => 'mitra.php',
        'icon' => 'ri-community-line',
    ],
    [
        'id' => 'mahasiswa',
        'label' => 'Students',
        'href' => 'mahasiswa.php',
        'icon' => 'ri-graduation-cap-line',
    ],
    [
        'id' => 'alat_lab',
        'label' => 'Lab Tools',
        'href' => 'alat_lab.php',
        'icon' => 'ri-tools-line',
    ],
    [
        'id' => 'peminjaman',
        'label' => 'Tool Loan',
        'href' => 'peminjaman.php',
        'icon' => 'ri-exchange-line',
    ],
    [
        'id' => 'absensi',
        'label' => 'Attendance',
        'href' => 'absensi.php',
        'icon' => 'ri-calendar-check-line',
    ],
    [
        'id' => 'buku_tamu',
        'label' => 'Guestbook',
        'href' => 'buku_tamu.php',
        'icon' => 'ri-book-open-line',
    ],
    [
        'id' => 'gallery',
        'label' => 'Gallery',
        'href' => 'gallery.php',
        'icon' => 'ri-image-line',
    ],
    [
        'id' => 'view-site',
        'label' => 'View Site',
        'href' => '../index.php',
        'icon' => 'ri-external-link-line',
        'target' => '_blank',
    ],
    [
        'id' => 'logout',
        'label' => 'Logout',
        'href' => 'logout.php',
        'icon' => 'ri-logout-box-line',
        'class' => 'logout',
    ],
];

$active_page = $active_page ?? pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
?>

<aside class="sidebar">
    <div class="brand">InLet Polinema</div>
    <div class="menu-scroll">
        <div class="menu">
        <?php foreach ($menu_items as $item): ?>
            <?php
                $is_active = $active_page === $item['id'];
                $classes = trim(($is_active ? 'active ' : '') . ($item['class'] ?? ''));
            ?>
            <a href="<?= htmlspecialchars($item['href']); ?>"
               class="<?= $classes; ?>"
               <?= isset($item['target']) ? 'target="' . htmlspecialchars($item['target']) . '"' : ''; ?>>
                <i class="menu-icon <?= htmlspecialchars($item['icon']); ?>"></i>
                <span><?= htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
</aside>