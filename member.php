<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Pagination setup
$items_per_page = 8;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM member");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get members with profiles
$stmt = $conn->prepare("SELECT m.*, pm.alamat, pm.no_tlp, pm.deskripsi 
                      FROM member m 
                      LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
                      ORDER BY m.nama
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

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
<?php include 'includes/header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research - Information & Learning Engineering Technology</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">

    <link rel="stylesheet" href="css/style-member.css"> 
</head>

<body>

    <section class="hero d-flex align-items-center" id="home">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold">Our Experts</h1>
            <p class="lead mt-3">Driving innovation in Information and Learning Engineering Technology.</p>
        </div>
    </section>

    <section class="team-section" id="profiles">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h2 class="fw-bold">Meet the Team</h2>
                <p class="text-muted">The brilliant minds behind our research.</p>
                <div style="width: 60px; height: 3px; background: var(--primary-color, #0d6efd); margin: 15px auto;"></div>
            </div>
            
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
            
            <?php if ($total_pages > 0): ?>
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination pagination-modern justify-content-center">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }

                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; 

                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo ($current_page >= $total_pages) ? $total_pages : $current_page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                    <div class="text-center mt-3 text-muted small">
                        Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> experts
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>