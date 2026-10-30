<?php
/**
 * Variables provided by admin/dashboard.php
 *
 * @var array $stats
 * @var \PDO $conn
 * @var array $recent_news
 */
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

    <link rel="stylesheet" href="../assets/css/admin/dashboard.css">
</head>

<body>
    <?php $active_page = 'dashboard';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

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