<?php
/**
 * Variables provided by research.php
 *
 * @var array $page_info
 * @var array $progress_list
 * @var array $articles_by_research
 * @var float $total_pages_progress
 * @var int $current_page_progress
 * @var int $offset_progress
 * @var int $items_per_page
 * @var int $total_items_progress
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Research - InLET'); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-research.css">
    <link rel="stylesheet" href="assets/css/site.css?v=<?= filemtime(__DIR__ . '/../assets/css/site.css') ?>">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="page-main">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Research - Information And Learning Engineering Technology'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Pioneering advancements in Language and Educational Technology to shape the future of learning</p>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($progress_list)): ?>
            <section id="progress" class="research progress-white">
                <div class="container">
                    <div class="section-title">
                        <h2>Current projects</h2>
                    </div>
                    <div class="research-progress-list">
                        <?php foreach ($progress_list as $progress): ?>
                            <div class="research-progress-card">
                                <div class="research-progress-header">
                                    <div class="research-progress-title-section">
                                        <h3 class="research-progress-title"><?php echo htmlspecialchars($progress['judul'] ?? 'Research Project'); ?></h3>
                                    </div>
                                    <div class="research-progress-period">
                                        <?php 
                                        $tgl_mulai = $progress['tgl_mulai'] ?? null;
                                        $tgl_selesai = $progress['tgl_selesai'] ?? null;
                                        
                                        if ($tgl_mulai) {
                                            $date_mulai = date('F Y', strtotime($tgl_mulai));
                                            if ($tgl_selesai) {
                                                $date_selesai = date('F Y', strtotime($tgl_selesai));
                                                $period_text = $date_mulai . ' - ' . $date_selesai;
                                            } else {
                                                $period_text = $date_mulai . ' - Present';
                                            }
                                        } else {
                                            $period_text = 'January 2021 - Present';
                                        }
                                        ?>
                                        <span class="period-text"><?php echo htmlspecialchars($period_text); ?></span>
                                    </div>
                                </div>
                                
                                <div class="research-progress-description">
                                    <p><?php echo htmlspecialchars($progress['deskripsi'] ?? 'Research description not available.'); ?></p>
                                </div>
                                
                                <?php if (!empty($progress['video_url'])): ?>
                                    <?php $embedUrl = getYoutubeEmbedUrl($progress['video_url']); ?>
                                    <?php if ($embedUrl): ?>
                                        <div class="research-progress-video">
                                            <div class="video-wrapper">
                                                <iframe src="<?php echo htmlspecialchars($embedUrl); ?>"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen loading="lazy"></iframe>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php 
                                // Get articles for this specific research
                                $research_articles = [];
                                if (isset($articles_by_research[$progress['id_penelitian']])) {
                                    $research_articles = $articles_by_research[$progress['id_penelitian']];
                                }
                                ?>
                                <?php if (!empty($research_articles)): ?>
                                    <div class="published-articles-section">
                                        <div class="published-articles-header">
                                            <h4 class="published-articles-title">Published article :</h4>
                                            <button class="btn-show-details" onclick="toggleArticleDetails(this, '<?php echo $progress['id_penelitian']; ?>')">
                                                <span class="btn-text">Show Details</span>
                                                <i class="fas fa-chevron-down btn-icon"></i>
                                            </button>
                                        </div>
                                        <div class="published-articles-content" id="articles-<?php echo $progress['id_penelitian']; ?>" style="display: none;">
                                            <table class="published-articles-table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Title</th>
                                                        <th>Year</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $article_no = 1;
                                                    foreach ($research_articles as $artikel): 
                                                        $article_url = extractUrlFromContent($artikel['konten'] ?? '');
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $article_no++; ?></td>
                                                            <td>
                                                                <?php if ($article_url): ?>
                                                                    <a href="<?php echo htmlspecialchars($article_url); ?>" target="_blank" rel="noopener noreferrer" class="article-link">
                                                                        <?php echo htmlspecialchars($artikel['judul']); ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="article-title"><?php echo htmlspecialchars($artikel['judul']); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="article-year"><?php echo htmlspecialchars($artikel['tahun'] ?? '-'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages_progress > 1): ?>
                        <nav aria-label="Progress pagination" class="mt-5">
                            <ul class="pagination justify-content-center pagination-gap">
                                <?php if ($current_page_progress > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $current_page_progress - 1; ?>#progress"
                                            aria-label="Previous">
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
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $total_pages_progress; ?>#progress"><?php echo $total_pages_progress; ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($current_page_progress < $total_pages_progress): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $current_page_progress + 1; ?>#progress"
                                            aria-label="Next">
                                            <span aria-hidden="true">Next &raquo;</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-hidden="true">Next &raquo;</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div class="text-center mt-3 text-muted">
                                Showing <?php echo ($offset_progress + 1); ?> -
                                <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> of
                                <?php echo $total_items_progress; ?> progress updates
                            </div>
                        </nav>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="research-cta">
            <div class="container">
                <div class="section-title">
                    <h2>Work with InLET</h2>
                    <p>Interested in collaborating, joining as a research student, or learning more? Get in touch with any of our members.</p>
                </div>
                <a href="member.php" class="btn btn-primary">Contact a member</a>
            </div>
        </section>

        <?php include __DIR__ . '/partials/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleDetails(button) {
                const card = button.closest('.research-progress-card');
                const details = card.querySelector('.research-progress-details');
                const btnText = button.querySelector('.btn-text');
                
                if (details.style.display === 'none') {
                    details.style.display = 'block';
                    btnText.textContent = 'Hide details';
                } else {
                    details.style.display = 'none';
                    btnText.textContent = 'Show details';
                }
            }
            
            function toggleArticleDetails(button, researchId) {
                const content = document.getElementById('articles-' + researchId);
                const btnText = button.querySelector('.btn-text');
                const btnIcon = button.querySelector('.btn-icon');
                
                if (!content) return;
                
                if (content.style.display === 'none' || content.style.display === '') {
                    // Show details
                    content.style.display = 'block';
                    content.style.maxHeight = '0';
                    content.style.opacity = '0';
                    content.style.overflow = 'hidden';
                    content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
                    
                    // Force reflow
                    content.offsetHeight;
                    
                    // Animate to full height
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    
                    btnText.textContent = 'Hide Details';
                    btnIcon.classList.remove('fa-chevron-down');
                    btnIcon.classList.add('fa-chevron-up');
                    button.classList.add('active');
                    
                    // Remove max-height after animation to allow natural height
                    setTimeout(() => {
                        content.style.maxHeight = 'none';
                        content.style.overflow = 'visible';
                    }, 300);
                } else {
                    // Hide details
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.overflow = 'hidden';
                    
                    // Force reflow
                    content.offsetHeight;
                    
                    // Animate to collapsed
                    content.style.maxHeight = '0';
                    content.style.opacity = '0';
                    
                    btnText.textContent = 'Show Details';
                    btnIcon.classList.remove('fa-chevron-up');
                    btnIcon.classList.add('fa-chevron-down');
                    button.classList.remove('active');
                    
                    // Hide after animation
                    setTimeout(() => {
                        content.style.display = 'none';
                    }, 300);
                }
            }
        </script>
    </body>

</html>