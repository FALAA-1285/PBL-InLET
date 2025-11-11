<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total Articles
$stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
$stats['articles'] = $stmt->fetch()['count'];

// Total News
$stmt = $conn->query("SELECT COUNT(*) as count FROM berita");
$stats['news'] = $stmt->fetch()['count'];

// Total Members
$stmt = $conn->query("SELECT COUNT(*) as count FROM member");
$stats['members'] = $stmt->fetch()['count'];

// Total Progress
$stmt = $conn->query("SELECT COUNT(*) as count FROM progress");
$stats['progress'] = $stmt->fetch()['count'];

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
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: var(--light);
        }
        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .admin-header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            color: var(--primary);
            font-size: 1.5rem;
        }
        .admin-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .admin-nav a {
            color: var(--dark);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .admin-nav a:hover {
            background: var(--light);
            color: var(--primary);
        }
        .admin-nav .btn-logout {
            background: #ef4444;
            color: white;
        }
        .admin-nav .btn-logout:hover {
            background: #dc2626;
        }
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }
        .welcome-hero {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
        }
        .welcome-hero h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        .stat-card .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: var(--dark);
            display: block;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .action-card h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .action-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        .recent-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        .recent-section h3 {
            color: var(--dark);
            margin-bottom: 1.5rem;
        }
        .recent-item {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-item h4 {
            color: var(--dark);
            margin-bottom: 0.3rem;
        }
        .recent-item small {
            color: var(--gray);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-header-content">
            <h1>CMS Dashboard - InLET</h1>
            <div class="admin-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="research.php">Research</a>
                <a href="member.php">Member</a>
                <a href="news.php">News</a>
                <a href="../index.php" target="_blank">View Site</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="welcome-hero">
            <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Kelola konten website InLET dari sini</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Artikel</h3>
                <div class="stat-number"><?php echo $stats['articles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Berita</h3>
                <div class="stat-number"><?php echo $stats['news']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Member</h3>
                <div class="stat-number"><?php echo $stats['members']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Progress</h3>
                <div class="stat-number"><?php echo $stats['progress']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Kunjungan</h3>
                <div class="stat-number"><?php echo $stats['visitors']; ?></div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="research.php" class="action-card">
                <h3>📚 Kelola Research</h3>
                <p>Tambah, edit, atau hapus artikel dan progress penelitian</p>
            </a>
            <a href="member.php" class="action-card">
                <h3>👥 Kelola Member</h3>
                <p>Kelola data anggota tim dan profil mereka</p>
            </a>
            <a href="news.php" class="action-card">
                <h3>📰 Kelola News</h3>
                <p>Publikasikan berita dan update terbaru</p>
            </a>
        </div>

        <div class="recent-section">
            <h3>Berita Terbaru</h3>
            <?php if (empty($recent_news)): ?>
                <p style="color: var(--gray);">Belum ada berita</p>
            <?php else: ?>
                <?php foreach ($recent_news as $news): ?>
                    <div class="recent-item">
                        <h4><?php echo htmlspecialchars($news['judul']); ?></h4>
                        <small><?php echo date('d M Y', strtotime($news['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

