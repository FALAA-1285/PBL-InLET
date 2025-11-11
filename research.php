<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Get all articles
$stmt = $conn->query("SELECT * FROM artikel ORDER BY tahun DESC, judul");
$artikels = $stmt->fetchAll();

// Get all progress
$stmt = $conn->query("SELECT p.*, a.judul as artikel_judul, m.nama as mahasiswa_nama, mem.nama as member_nama 
                      FROM progress p 
                      LEFT JOIN artikel a ON p.id_artikel = a.id_artikel 
                      LEFT JOIN mahasiswa m ON p.id_mhs = m.id_mhs 
                      LEFT JOIN member mem ON p.id_member = mem.id_member 
                      ORDER BY p.created_at DESC");
$progress_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research - Innovation in Language & Educational Technology</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero research-hero-page" id="research-top">
        <div class="hero-content">
            <h1>Our Global Research Initiatives</h1>
            <p>Pioneering advancements in Language and Educational Technology to shape the future of learning.</p>
            <div class="cta-buttons">
                <a href="#focus-areas" class="btn btn-primary">View Focus Areas</a>
            </div>
        </div>
    </section>

    <section class="research" id="focus-areas">
        <div class="research-container">
            <div class="section-title">
                <h2>Core Research Focus Areas</h2>
                <p>A deep dive into the six pillars of our innovation.</p>
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

    <?php if (!empty($progress_list)): ?>
    <section class="research" style="background: white; padding: 6rem 2rem;">
        <div class="research-container">
            <div class="section-title">
                <h2>Research Progress</h2>
                <p>Latest updates on our research projects.</p>
            </div>
            <div class="research-grid">
                <?php foreach ($progress_list as $progress): ?>
                    <div class="research-item">
                        <h4><?php echo htmlspecialchars($progress['judul']); ?></h4>
                        <?php if ($progress['tahun']): ?>
                            <p style="color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;">Tahun: <?php echo $progress['tahun']; ?></p>
                        <?php endif; ?>
                        <?php if ($progress['deskripsi']): ?>
                            <p><?php echo htmlspecialchars($progress['deskripsi']); ?></p>
                        <?php endif; ?>
                        <?php if ($progress['artikel_judul'] || $progress['mahasiswa_nama'] || $progress['member_nama']): ?>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.9rem; color: var(--gray);">
                                <?php if ($progress['artikel_judul']): ?>
                                    <p><strong>Artikel:</strong> <?php echo htmlspecialchars($progress['artikel_judul']); ?></p>
                                <?php endif; ?>
                                <?php if ($progress['mahasiswa_nama']): ?>
                                    <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($progress['mahasiswa_nama']); ?></p>
                                <?php endif; ?>
                                <?php if ($progress['member_nama']): ?>
                                    <p><strong>Member:</strong> <?php echo htmlspecialchars($progress['member_nama']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="research-cta" style="padding: 4rem 2rem; text-align: center; background: var(--light);">
        <div class="research-container">
            <div class="section-title" style="margin-bottom: 2rem;">
                <h2 style="color: var(--primary-dark);">Join Our Mission in Innovation</h2>
                <p>Tertarik untuk berkolaborasi, menjadi mahasiswa riset, atau mendapatkan informasi lebih lanjut?</p>
            </div>
            <a href="index.php#contact" class="btn btn-secondary" style="background: var(--primary); border: 2px solid var(--primary); color: white;">Contact Our Team</a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
