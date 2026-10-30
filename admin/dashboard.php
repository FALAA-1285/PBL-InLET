<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

// Statistik
$stats = [];

// Total Artikel
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
    $stats['articles'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['articles'] = 0;
}

// Total Berita
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM berita");
    $stats['news'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['news'] = 0;
}

// Total Member
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM member");
    $stats['members'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['members'] = 0;
}

// Total Penelitian
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM penelitian");
    $stats['penelitian'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['penelitian'] = 0;
}

// Total Visitors
try {
    $stmt = $conn->query("SELECT SUM(visit_count) as total FROM visitor");
    $stats['visitors'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['visitors'] = 0;
}

// Unread Messages
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM buku_tamu WHERE is_read = false");
    $stats['unread_messages'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['unread_messages'] = 0;
}

// Recent News
try {
    $stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC LIMIT 5");
    $recent_news = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_news = [];
}

// Ensure all stats have default values
if (!isset($stats['articles'])) $stats['articles'] = 0;
if (!isset($stats['news'])) $stats['news'] = 0;
if (!isset($stats['members'])) $stats['members'] = 0;
if (!isset($stats['penelitian'])) $stats['penelitian'] = 0;
if (!isset($stats['visitors'])) $stats['visitors'] = 0;
if (!isset($stats['unread_messages'])) $stats['unread_messages'] = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dashboard - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">

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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .stat-card a:hover {
            text-decoration: none;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
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
    <?php $active_page = 'dashboard';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">

            <div class="welcome-box">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p>Manage InLET website content from this panel.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Articles</h3>
                    <div class="stat-number"><?= $stats['articles']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total News</h3>
                    <div class="stat-number"><?= $stats['news']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Members</h3>
                    <div class="stat-number"><?= $stats['members']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Research</h3>
                    <div class="stat-number"><?= $stats['penelitian']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-number"><?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM mahasiswa");
                    echo $stmt->fetch()['count'];
                    ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Lab Tools</h3>
                    <div class="stat-number"><?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM alat_lab");
                    echo $stmt->fetch()['count'];
                    ?></div>
                </div>
                <div class="stat-card">
                    <h3>Tools Borrowed</h3>
                    <div class="stat-number"><?php
                    try {
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM view_alat_dipinjam");
                        $result = $stmt->fetch();
                        echo $result ? $result['count'] : '0';
                    } catch (PDOException $e) {
                        // Fallback to direct query if view error
                        try {
                            $stmt = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'dipinjam' AND id_alat IS NOT NULL");
                            $result = $stmt->fetch();
                            echo $result ? $result['count'] : '0';
                        } catch (PDOException $e2) {
                            echo '0';
                        }
                    }
                    ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Partners</h3>
                    <div class="stat-number"><?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM mitra");
                    echo $stmt->fetch()['count'];
                    ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Visits</h3>
                    <div class="stat-number"><?= $stats['visitors']; ?></div>
                </div>
                <div class="stat-card">
                    <a href="buku_tamu.php?filter=unread">
                        <h3>Unread Messages</h3>
                        <div class="stat-number"><?= $stats['unread_messages']; ?></div>
                    </a>
                </div>
            </div>

            <div class="recent-section">
                <h3>Latest News</h3>

                <?php if (empty($recent_news)): ?>
                    <p class="muted-gray">No news yet</p>
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