<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total Articles
$stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
$stats['articles'] = $stmt->fetch()['count'];

// Total Members
$stmt = $conn->query("SELECT COUNT(*) as count FROM member");
$stats['members'] = $stmt->fetch()['count'];

// Total Progress
$stmt = $conn->query("SELECT COUNT(*) as count FROM progress");
$stats['progress'] = $stmt->fetch()['count'];

// Total Visitors
$stmt = $conn->query("SELECT SUM(visit_count) as total FROM visitor");
$stats['visitors'] = $stmt->fetch()['total'] ?? 0;

// Get recent articles (for research section)
$stmt = $conn->query("SELECT * FROM artikel ORDER BY tahun DESC, judul LIMIT 6");
$artikels = $stmt->fetchAll();

// Get members (for team section)
$stmt = $conn->query("SELECT m.*, pm.deskripsi 
                      FROM member m 
                      LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
                      ORDER BY m.nama LIMIT 4");
$members = $stmt->fetchAll();

// Get recent news (for news section)
$stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC LIMIT 3");
$recent_news = $stmt->fetchAll();

// Function to get initials from name
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return substr($initials, 0, 2); // Max 2 characters
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InLET - Information and Learning Engineering Technology</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>InLET - Information and Learning Engineering Technology</h1>
            <p>Memimpin transformasi pembelajaran bahasa melalui riset mutakhir dan rekayasa teknologi sistem pembelajaran.</p>
            <div class="cta-buttons">
                <a href="#research" class="btn btn-primary">Telusuri Riset</a>
                <a href="#contact" class="btn btn-secondary">Bergabung</a>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['articles'] > 0 ? $stats['articles'] . '+' : '50+'; ?></div>
                <div class="stat-label">Proyek Rekayasa Sistem</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['members'] > 0 ? $stats['members'] . '+' : '15+'; ?></div>
                <div class="stat-label">Ahli Rekayasa Pembelajaran</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['progress'] > 0 ? $stats['progress'] . '+' : '100+'; ?></div>
                <div class="stat-label">Publikasi Terindeks</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['visitors'] > 0 ? $stats['visitors'] : '100+'; ?></div>
                <div class="stat-label">Kemitraan Industri & Akademik</div>
            </div>
        </div>
    </section>

    <section class="features" id="about">
        <div class="section-title">
            <h2>APA YANG KAMI REKAYASA</h2>
            <p>Memimpin transformasi edukasi bahasa melalui riset dan pengembangan sistem digital mutakhir.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🎓</div>
                <h3>Rekayasa Kurikulum & Metode</h3>
                <p>Mengembangkan dan **merekayasa metodologi** pembelajaran mutakhir yang mengintegrasikan teknologi untuk mencapai hasil edukasi yang **optimal dalam pembelajaran bahasa**.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>Pembelajaran Didukung AI</h3>
                <p>Memanfaatkan **kecerdasan buatan** dan algoritma adaptif untuk menciptakan pengalaman belajar bahasa yang personal dan **menyesuaikan diri** dengan kebutuhan setiap pengguna.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Implementasi Solusi Mobile</h3>
                <p>**Merancang dan membangun aplikasi mobile** inovatif untuk mendukung pembelajaran bahasa **di mana saja dan kapan saja** dengan fitur interaktif yang efektif.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Analitika Rekayasa Pembelajaran</h3>
                <p>Menggunakan **analitika data komprehensif** untuk mengukur, mengevaluasi, dan meningkatkan efektivitas pembelajaran bahasa serta performa siswa secara sistematis.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌐</div>
                <h3>Asesmen Digital Terstruktur</h3>
                <p>Menciptakan **perangkat digital komprehensif** untuk secara akurat mengevaluasi kemahiran bahasa (*proficiency*) dan melacak kemajuan belajar (*learning progress*) dengan terstruktur.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎮</div>
                <h3>Sistem Motivasi Berbasis Game</h3>
                <p>Menerapkan strategi pembelajaran berbasis permainan (*game-based learning*) dan elemen **gamifikasi** untuk meningkatkan keterlibatan, motivasi, dan hasil belajar dalam edukasi bahasa.</p>
            </div>
        </div>
    </section>

    <section class="research" id="research">
        <div class="research-container">
            <div class="section-title">
                <h2>AREA FOKUS RISET INLET</h2>
                <p>Memelopori riset mendalam di bidang Information and Learning Engineering Technology (InLET) untuk pendidikan bahasa.</p>
            </div>
            <div class="research-grid">
                <?php if (empty($artikels)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; color: var(--gray);">
                        <p style="font-size: 1.2rem; margin-bottom: 1rem;">Belum ada artikel penelitian yang dipublikasikan.</p>
                        <p style="font-size: 0.9rem;">Silakan login sebagai admin untuk menambahkan artikel melalui CMS.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($artikels as $artikel): ?>
                        <div class="research-item">
                            <h4><?php echo htmlspecialchars($artikel['judul']); ?></h4>
                            <?php if ($artikel['tahun']): ?>
                                <p style="color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;">Tahun: <?php echo $artikel['tahun']; ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($artikel['konten']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="team" id="team">
        <div class="section-title">
            <h2>TIM AHLI REKAYASA KAMI</h2>
            <p>Temui para ahli yang mendorong inovasi Information and Learning Engineering Technology.</p>
        </div>
        <div class="team-grid">
            <?php if (empty($members)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; color: var(--gray);">
                    <p style="font-size: 1.2rem; margin-bottom: 1rem;">Belum ada member yang terdaftar.</p>
                    <p style="font-size: 0.9rem;">Silakan login sebagai admin untuk menambahkan member melalui CMS.</p>
                </div>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <div class="team-card">
                        <div class="team-avatar"><?php echo getInitials($member['nama']); ?></div>
                        <h4><?php echo htmlspecialchars($member['nama']); ?></h4>
                        <?php if ($member['jabatan']): ?>
                            <p style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($member['jabatan']); ?></p>
                        <?php endif; ?>
                        <?php if ($member['deskripsi']): ?>
                            <p><?php echo htmlspecialchars(substr($member['deskripsi'], 0, 100)) . '...'; ?></p>
                        <?php elseif ($member['email']): ?>
                            <p><?php echo htmlspecialchars($member['email']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($recent_news)): ?>
    <section class="research" style="background: white; padding: 6rem 2rem;">
        <div class="research-container">
            <div class="section-title">
                <h2>BERITA TERBARU</h2>
                <p>Update terbaru dari InLET</p>
            </div>
            <div class="research-grid">
                <?php foreach ($recent_news as $news): ?>
                    <div class="research-item">
                        <?php if ($news['gambar_thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" alt="<?php echo htmlspecialchars($news['judul']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($news['judul']); ?></h4>
                        <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <?php echo date('d M Y', strtotime($news['created_at'])); ?>
                        </p>
                        <p><?php echo htmlspecialchars(substr($news['konten'], 0, 200)) . '...'; ?></p>
                        <a href="news.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Baca selengkapnya →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

</body>

</html>
