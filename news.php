<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Pagination setup
$items_per_page = 9; // 9 items per page for 3 columns grid
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM berita");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get news with pagination
$stmt = $conn->prepare("SELECT * FROM berita ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Information And Learning Engineering Technology</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php';?>

    <section class="hero" style="margin-top: 80px; padding: 6rem 2rem;">
        <div class="hero-content">
            <h1>News - Information And Learning Engineering Technology</h1>
            <p>Stay updated with our newest publications, activities, and breakthroughs.</p>
        </div>
    </section>

    <section id="news" class="research" style="padding: 6rem 0;"> 
        <div class="container text-center">
            <div class="section-title">
                <h2 style="font-size: 2.5rem;">Our News</h2>
                <p style="font-size: 1.1rem;">Read the recent blog posts about our research group and activities.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php if (empty($news_list)): ?>
                    <div class="col-12">
                        <p style="color: var(--gray); padding: 3rem;">Belum ada berita yang dipublikasikan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($news_list as $news): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="feature-card h-100 border-0 shadow-sm transition-hover" style="border-radius: 12px; overflow: hidden; padding: 0;">
                                <?php if ($news['gambar_thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['judul']); ?>" style="height: 220px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="height: 220px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <div class="card-body text-start" style="padding: 1.5rem 2.5rem;">
                                    <small class="text-muted">Published on <?php echo date('F d, Y', strtotime($news['created_at'])); ?></small>
                                    <h3 class="mt-2" style="font-weight: 600;"><?php echo htmlspecialchars($news['judul']); ?></h3>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($news['konten'], 0, 150)) . '...'; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="News pagination" style="margin-top: 3rem;">
                    <ul class="pagination justify-content-center" style="gap: 0.5rem;">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&laquo; Previous</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
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
                        Menampilkan <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> dari <?php echo $total_items; ?> berita
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <div class="container text-center">
            <div class="section-title">
                <h2 style="font-size: 2.5rem;">Watch Our Gallery</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Logo-polinema.png" alt="Project Launch Event" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Logo-polinema.png" alt="International Seminar" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Logo-polinema.png" alt="Team Building Day" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Logo-polinema.png" alt="Student Mentoring" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Teknik-Polinema.jpg" alt="Research Presentation" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Teknik-Polinema.jpg" alt="Student Workshop" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Teknik-Polinema.jpg" alt="Guest Lecture" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card color-blue">
                        <img src="assets/Teknik-Polinema.jpg" alt="Group Photo" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
