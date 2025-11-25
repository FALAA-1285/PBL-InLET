<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

// Statistik
$stats = [];

// Total Artikel
$stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
$stats['articles'] = $stmt->fetch()['count'];

// Total Berita
$stmt = $conn->query("SELECT COUNT(*) as count FROM berita");
$stats['news'] = $stmt->fetch()['count'];

// Total Member
$stmt = $conn->query("SELECT COUNT(*) as count FROM member");
$stats['members'] = $stmt->fetch()['count'];

// Total Penelitian
$stmt = $conn->query("SELECT COUNT(*) as count FROM penelitian");
$stats['penelitian'] = $stmt->fetch()['count'];

// Total Visitors
$stmt = $conn->query("SELECT SUM(visit_count) as total FROM visitor");
$stats['visitors'] = $stmt->fetch()['total'] ?? 0;

// Recent News
$stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC LIMIT 5");
$recent_news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - CMS InLET</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="admin.css">

<style>

    .welcome-box {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        padding: 2rem;
        border-radius: 18px;
        color: white;
        margin-bottom: 2rem;
    }

    .welcome-box h2 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }

    /* STAT CARDS */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        transition: 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h3 {
        color: var(--gray);
        font-size: 0.9rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
    }

    /* RECENT NEWS */
    .recent-section {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    }

    .recent-section h3 {
        margin-bottom: 1rem;
        color: var(--dark);
    }

    .recent-item {
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .recent-item h4 {
        margin-bottom: 0.3rem;
    }

    .recent-item small {
        color: var(--gray);
    }
</style>
</head>

<body>
    <?php $active_page = 'dashboard'; include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">

        <div class="welcome-box">
            <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Kelola konten website InLET dari panel ini.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Artikel</h3>
                <div class="stat-number"><?= $stats['articles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Berita</h3>
                <div class="stat-number"><?= $stats['news']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Member</h3>
                <div class="stat-number"><?= $stats['members']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Penelitian</h3>
                <div class="stat-number"><?= $stats['penelitian']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Mahasiswa</h3>
                <div class="stat-number"><?php 
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM mahasiswa");
                    echo $stmt->fetch()['count'];
                ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Alat Lab</h3>
                <div class="stat-number"><?php 
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM alat_lab");
                    echo $stmt->fetch()['count'];
                ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Mitra</h3>
                <div class="stat-number"><?php 
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM mitra");
                    echo $stmt->fetch()['count'];
                ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Kunjungan</h3>
                <div class="stat-number"><?= $stats['visitors']; ?></div>
            </div>
        </div>

        <div class="recent-section">
            <h3>Berita Terbaru</h3>

            <?php if (empty($recent_news)): ?>
                <p style="color: var(--gray);">Belum ada berita</p>
            <?php else: ?>
                <?php foreach ($recent_news as $news): ?>
                    <div class="recent-item">
                        <h4><?= htmlspecialchars($news['judul']); ?></h4>
                        <small><?= date('d M Y', strtotime($news['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        </div>

    </main>

</body>
</html>