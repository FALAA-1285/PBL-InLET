<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Search and filter setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$letter_filter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';

// Pagination setup
$items_per_page = 8; // 8 items per page for grid layout
$current_page_members = isset($_GET['page_members']) ? max(1, intval($_GET['page_members'])) : 1;
$offset_members = ($current_page_members - 1) * $items_per_page;

// Build WHERE clause
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $where_clauses[] = "nama ILIKE :search";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($letter_filter) && strlen($letter_filter) == 1 && ctype_alpha($letter_filter)) {
    $where_clauses[] = "UPPER(SUBSTRING(nama FROM 1 FOR 1)) = :letter";
    $params[':letter'] = $letter_filter;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Count total items
$count_sql = "SELECT COUNT(*) FROM member $where_sql";
$stmt = $conn->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items_members = (int) $stmt->fetchColumn();
$total_pages_members = max(1, ($items_per_page > 0 && $total_items_members > 0) ? (int) ceil($total_items_members / $items_per_page) : 1);
$current_page_members = min(max(1, $current_page_members), $total_pages_members);
$offset_members = ($current_page_members - 1) * $items_per_page;


// Fetch members with search and pagination
$query_sql = "SELECT * FROM member $where_sql ORDER BY nama LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_members, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

// Get first letters for index
$stmt = $conn->query("SELECT DISTINCT UPPER(SUBSTRING(nama FROM 1 FOR 1)) as first_letter FROM member WHERE nama IS NOT NULL AND nama != '' ORDER BY first_letter");
$available_letters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get initials
function getInitials($name)
{
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style-member.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Our Experts - Information And Learning Engineering Technology</h1>
                <p class="lead mt-3">Driving innovation in Information and Learning Engineering Technology.</p>
            </div>
        </section>

        <section class="team-section" id="profiles">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Meet the Team</h2>
                    <p class="text-muted">The brilliant minds behind our research.</p>
                    <div class="divider"></div>
                </div>

                <!-- Alphabet Index -->
                <?php if (!empty($available_letters) && empty($search_query)): ?>
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap justify-content-center gap-2">

                            </div>
                            <?php if (!empty($letter_filter)): ?>
                                <p class="text-center text-muted mt-2">
                                    Showing members with initial letter "<?= htmlspecialchars($letter_filter); ?>"
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
                                <p class="mb-0 text-muted">No members registered at this time.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="member-card card-surface h-100">
                                    <?php
                                    // Handle image
                                    $has_photo = false;
                                    $foto_url = '';
                                    if (!empty($member['foto'])) {
                                        $foto_url = $member['foto'];
                                        // If not HTTP URL, assume local file
                                        if (!preg_match('/^https?:\/\//', $foto_url)) {
                                            // Add uploads/ prefix if missing
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
                                                alt="<?php echo htmlspecialchars($member['nama']); ?>" class="member-img"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                            <div class="member-initials d-none">
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
                                                <i class="fas fa-envelope me-2"></i>Contact via Email
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination for Members -->
                <?php if ($total_pages_members > 1): ?>
                    <nav aria-label="Members pagination" class="mt-5">
                        <ul class="pagination justify-content-center pagination-gap">
                            <?php
                            $page_url = "?";
                            if (!empty($search_query)) {
                                $page_url .= "search=" . urlencode($search_query) . "&";
                            }
                            if (!empty($letter_filter)) {
                                $page_url .= "letter=" . urlencode($letter_filter) . "&";
                            }
                            ?>

                            <?php if ($current_page_members > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_members=<?php echo $current_page_members - 1; ?>#focus-areas"
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
                            $start_page = max(1, $current_page_members - 2);
                            $end_page = min($total_pages_members, $current_page_members + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>page_members=1#focus-areas">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page_members) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_members=<?php echo $i; ?>#focus-areas"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages_members): ?>
                                <?php if ($end_page < $total_pages_members - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_members=<?php echo $total_pages_members; ?>#focus-areas"><?php echo $total_pages_members; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($current_page_members < $total_pages_members): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_members=<?php echo $current_page_members + 1; ?>#focus-areas"
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

                    </nav>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div class="footer-wrap">
        <?php include 'includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>