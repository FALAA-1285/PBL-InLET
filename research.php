<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Pagination setup for articles
$items_per_page = 9; // 9 items per page for grid layout
$current_page_artikel = isset($_GET['page_artikel']) ? max(1, intval($_GET['page_artikel'])) : 1;
$offset_artikel = ($current_page_artikel - 1) * $items_per_page;

// Get total count for articles
$stmt = $conn->query("SELECT COUNT(*) FROM artikel");
$total_items_artikel = $stmt->fetchColumn();
$total_pages_artikel = ceil($total_items_artikel / $items_per_page);

// Get articles with pagination
$stmt = $conn->prepare("SELECT * FROM artikel ORDER BY tahun DESC, judul LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_artikel, PDO::PARAM_INT);
$stmt->execute();
$artikels = $stmt->fetchAll();

// Pagination setup for progress
$current_page_progress = isset($_GET['page_progress']) ? max(1, intval($_GET['page_progress'])) : 1;
$offset_progress = ($current_page_progress - 1) * $items_per_page;

// Get total count for progress
$stmt = $conn->query("SELECT COUNT(*) FROM progress");
$total_items_progress = $stmt->fetchColumn();
$total_pages_progress = ceil($total_items_progress / $items_per_page);

// Get progress with pagination
$stmt = $conn->prepare("SELECT p.*, a.judul as artikel_judul, m.nama as mahasiswa_nama, mem.nama as member_nama 
                      FROM progress p 
                      LEFT JOIN artikel a ON p.id_artikel = a.id_artikel 
                      LEFT JOIN mahasiswa m ON p.id_mhs = m.id_mhs 
                      LEFT JOIN member mem ON p.id_member = mem.id_member 
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
$stmt->execute();
$progress_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research - Innovation in Language & Educational Technology</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            
            <!-- Pagination for Articles -->
            <?php if ($total_pages_artikel > 1): ?>
                <nav aria-label="Articles pagination" style="margin-top: 3rem;">
                    <ul class="pagination justify-content-center" style="gap: 0.5rem;">
                        <?php if ($current_page_artikel > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_artikel=<?php echo $current_page_artikel - 1; ?>#focus-areas" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&laquo; Previous</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page_artikel - 2);
                        $end_page = min($total_pages_artikel, $current_page_artikel + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_artikel=1#focus-areas">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page_artikel) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_artikel=<?php echo $i; ?>#focus-areas"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages_artikel): ?>
                            <?php if ($end_page < $total_pages_artikel - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_artikel=<?php echo $total_pages_artikel; ?>#focus-areas"><?php echo $total_pages_artikel; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($current_page_artikel < $total_pages_artikel): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_artikel=<?php echo $current_page_artikel + 1; ?>#focus-areas" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">Next &raquo;</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="text-center mt-3" style="color: var(--gray);">
                        Menampilkan <?php echo ($offset_artikel + 1); ?> - <?php echo min($offset_artikel + $items_per_page, $total_items_artikel); ?> dari <?php echo $total_items_artikel; ?> artikel
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($progress_list)): ?>
    <section id="progress" class="research" style="background: white; padding: 6rem 2rem;">
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
            
            <!-- Pagination for Progress -->
            <?php if ($total_pages_progress > 1): ?>
                <nav aria-label="Progress pagination" style="margin-top: 3rem;">
                    <ul class="pagination justify-content-center" style="gap: 0.5rem;">
                        <?php if ($current_page_progress > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_progress=<?php echo $current_page_progress - 1; ?>#progress" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&laquo; Previous</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page_progress - 2);
                        $end_page = min($total_pages_progress, $current_page_progress + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_progress=1#progress">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page_progress) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_progress=<?php echo $i; ?>#progress"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages_progress): ?>
                            <?php if ($end_page < $total_pages_progress - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_progress=<?php echo $total_pages_progress; ?>#progress"><?php echo $total_pages_progress; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($current_page_progress < $total_pages_progress): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_progress=<?php echo $current_page_progress + 1; ?>#progress" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">Next &raquo;</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="text-center mt-3" style="color: var(--gray);">
                        Menampilkan <?php echo ($offset_progress + 1); ?> - <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> dari <?php echo $total_items_progress; ?> progress
                    </div>
                </nav>
            <?php endif; ?>
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
