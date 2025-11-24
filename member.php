<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Search setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$letter_filter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';

// Pagination setup
$items_per_page = 8;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Build WHERE clause
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $where_clauses[] = "m.nama ILIKE :search";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($letter_filter) && strlen($letter_filter) == 1 && ctype_alpha($letter_filter)) {
    $where_clauses[] = "UPPER(SUBSTRING(m.nama FROM 1 FOR 1)) = :letter";
    $params[':letter'] = $letter_filter;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM member m $where_sql";
$stmt = $conn->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get members with profiles
$sql = "SELECT m.*, pm.alamat, pm.no_tlp, pm.deskripsi 
        FROM member m 
        LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
        $where_sql
        ORDER BY m.nama
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

// Get all first letters for index
$stmt = $conn->query("SELECT DISTINCT UPPER(SUBSTRING(nama FROM 1 FOR 1)) as first_letter FROM member WHERE nama IS NOT NULL AND nama != '' ORDER BY first_letter");
$available_letters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Function to get initials
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return substr($initials, 0, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Experts - Information & Learning Engineering Technology</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">
    <link rel="stylesheet" href="css/style-member.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/header.php'; ?>

    <main class="flex-fill">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Our Experts</h1>
                <p class="lead mt-3">Driving innovation in Information and Learning Engineering Technology.</p>
            </div>
        </section>

        <section class="team-section" id="profiles" style="padding-bottom: 4rem;">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Meet the Team</h2>
                    <p class="text-muted">The brilliant minds behind our research.</p>
                    <div style="width: 60px; height: 3px; background: var(--primary-color, #0d6efd); margin: 15px auto;"></div>
                </div>
                
                <!-- Search Box -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <form method="GET" action="" class="d-flex gap-2 mb-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari member berdasarkan nama..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <?php if (!empty($search_query) || !empty($letter_filter)): ?>
                                <a href="member.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </form>
                        <?php if (!empty($search_query)): ?>
                            <p class="text-muted text-center">
                                Menampilkan <?php echo $total_items; ?> hasil untuk "<?php echo htmlspecialchars($search_query); ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Alphabet Index -->
                <?php if (!empty($available_letters) && empty($search_query)): ?>
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                
                            </div>
                            <?php if (!empty($letter_filter)): ?>
                                <p class="text-center text-muted mt-2">
                                    Menampilkan member dengan huruf awal "<?= htmlspecialchars($letter_filter); ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="row g-4">
                    <?php if (empty($members)): ?>
                        <div class="col-12 text-center py-5">
                            <div class="alert alert-light shadow-sm" role="alert">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <p class="mb-0 text-muted">Belum ada member yang terdaftar saat ini.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="member-card card-surface h-100">
                                    <?php 
                                    // Image Logic
                                    $has_photo = false;
                                    $foto_url = '';
                                    if (!empty($member['foto'])) {
                                        $foto_url = $member['foto'];
                                        // Jika bukan URL http, asumsikan file lokal
                                        if (!preg_match('/^https?:\/\//', $foto_url)) {
                                            // Tambahkan prefix uploads/ jika belum ada
                                            if (strpos($foto_url, 'uploads/') !== 0) {
                                                $foto_url = 'uploads/' . ltrim($foto_url, '/');
                                            }
                                        }
                                        $has_photo = true;
                                    }
                                    ?>

                                    <div class="member-img-wrapper">
                                        <?php if ($has_photo): ?>
                                            <img src="<?php echo htmlspecialchars($foto_url); ?>" 
                                                 alt="<?php echo htmlspecialchars($member['nama']); ?>" 
                                                 class="member-img"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            
                                            <div class="member-initials" style="display: none;">
                                                <?php echo getInitials($member['nama']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="member-initials">
                                                <?php echo getInitials($member['nama']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="member-info">
                                        <h3 class="member-name"><?php echo htmlspecialchars($member['nama']); ?></h3>
                                        
                                        <div class="member-role">
                                            <?php echo htmlspecialchars($member['jabatan'] ?: 'Member'); ?>
                                        </div>

                                        <?php if ($member['deskripsi']): ?>
                                            <p class="member-desc" title="<?php echo htmlspecialchars($member['deskripsi']); ?>">
                                                <?php echo htmlspecialchars($member['deskripsi']); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="member-desc text-muted fst-italic">No description available.</p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($member['email']): ?>
                                        <div class="member-footer">
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="btn-email">
                                                <i class="fas fa-envelope me-2"></i>Hubungi via Email
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <!-- Pagination for Member -->
            <?php if ($total_pages_member > 1): ?>
                <nav aria-label="Member pagination" style="margin-top: 3rem;">
                    <ul class="pagination justify-content-center" style="gap: 0.5rem;">
                        <?php 
                        $page_url = "?";
                        if (!empty($search_name)) {
                            $page_url .= "name=" . urlencode($search_name) . "&";
                        }
                        ?>
                        
                        <?php if ($current_page_member > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $page_url ?>page_member=<?php echo $current_page_member - 1; ?>#focus-areas" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&laquo; Previous</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page_member - 2);
                        $end_page = min($total_pages_member, $current_page_member + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $page_url ?>page_member=1#focus-areas">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page_member) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= $page_url ?>page_member=<?php echo $i; ?>#focus-areas"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages_member): ?>
                            <?php if ($end_page < $total_pages_member - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $page_url ?>page_member=<?php echo $total_pages_member; ?>#focus-areas"><?php echo $total_pages_member; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($current_page_member < $total_pages_member): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $page_url ?>page_member=<?php echo $current_page_member + 1; ?>#focus-areas" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">Next &raquo;</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                </nav>
            <?php endif; ?>
        </div>
    </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>