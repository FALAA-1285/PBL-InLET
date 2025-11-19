<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Pagination setup
$items_per_page = 12; // 12 items per page for grid layout
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM member");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get members with profiles and pagination
$stmt = $conn->prepare("SELECT m.*, pm.alamat, pm.no_tlp, pm.deskripsi 
                      FROM member m 
                      LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
                      ORDER BY m.nama
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

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
    <?php include 'includes/header.php'; ?>

<head>
    <meta charset="UTF-8">
    <title>Research - Information & Learning Engineering Technology</title>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">
</head>
    
<section class="hero d-flex align-items-center" id="home">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold">Member - Information And Learning Engineering Technology</h1>
        <p class="lead mt-3">Meet the expert researchers and innovators driving our progress.</p>
    </div>
</section>

<section class="team" id="profiles" style="padding: 6rem 2rem; background: var(--light);">
    <div class="section-title">
        <h2>Meet the Experts</h2>
        <p>A complete list of our core team and collaborators.</p>
    </div>
    
    <div class="team-grid">
        <?php if (empty($members)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--gray);">
                <p>No members have been registered.</p>
            </div>
        <?php else: ?>
            <?php foreach ($members as $member): ?>
                <div class="team-card">
                    <?php if (!empty($member['foto'])): ?>
                        <?php 
                        $foto_url = $member['foto'];

                        if (!preg_match('/^https?:\/\//', $foto_url)) {
                            if (strpos($foto_url, 'uploads/') !== 0) {
                                $foto_url = 'uploads/' . ltrim($foto_url, '/');
                            }
                        }
                        ?>
                        <div class="team-avatar team-avatar-photo" 
                            data-foto="<?php echo htmlspecialchars($foto_url); ?>" 
                            data-initials="<?php echo htmlspecialchars(getInitials($member['nama'])); ?>" 
                            style="background-image: url('<?php echo htmlspecialchars($foto_url); ?>'); 
                                   background-size: cover; 
                                   background-position: center; 
                                   border: 3px solid var(--primary); 
                                   position: relative;">
                            <span class="avatar-fallback" style="display: none;">
                                <?php echo getInitials($member['nama']); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="team-avatar"><?php echo getInitials($member['nama']); ?></div>
                    <?php endif; ?>

                    <h4><?php echo htmlspecialchars($member['nama']); ?></h4>

                    <?php if ($member['jabatan']): ?>
                        <p class="role-title" 
                           style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.5rem;">
                           <?php echo htmlspecialchars($member['jabatan']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($member['deskripsi']): ?>
                        <p style="color: var(--gray); font-size: 0.95em;">
                            <?php echo htmlspecialchars(substr($member['deskripsi'], 0, 100)) . '...'; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($member['email']): ?>
                        <p style="color: var(--gray); font-size: 0.9em; margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($member['email']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Members pagination" style="margin-top: 3rem;">
            <ul class="pagination justify-content-center" 
                style="gap: 0.5rem; list-style: none; display: flex; flex-wrap: wrap;">

                <!-- Previous button -->
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous"
                           style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                  border-radius: 8px; text-decoration: none; 
                                  color: var(--dark); transition: all 0.3s;">
                            <span aria-hidden="true">&laquo; Previous</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link" aria-hidden="true"
                              style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                     border-radius: 8px; color: #999; opacity: 0.5;">
                            &laquo; Previous
                        </span>
                    </li>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1"
                           style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                  border-radius: 8px; text-decoration: none; color: var(--dark);">
                            1
                        </a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link" style="padding: 0.5rem 1rem; border: none; color: var(--gray);">
                                ...
                            </span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <?php if ($i == $current_page): ?>
                            <span class="page-link"
                                style="padding: 0.5rem 1rem; border: 1px solid var(--primary); 
                                       border-radius: 8px; background: var(--primary); 
                                       color: white; font-weight: 600;">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a class="page-link" href="?page=<?php echo $i; ?>"
                               style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                      border-radius: 8px; text-decoration: none; 
                                      color: var(--dark);">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link" style="padding: 0.5rem 1rem; border: none; color: var(--gray);">
                                ...
                            </span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>"
                           style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                  border-radius: 8px; text-decoration: none; 
                                  color: var(--dark);">
                            <?php echo $total_pages; ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Next button -->
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next"
                           style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                  border-radius: 8px; text-decoration: none; 
                                  color: var(--dark);">
                            <span aria-hidden="true">Next &raquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link"
                              style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; 
                                     border-radius: 8px; color: #999; opacity: 0.5;">
                            Next &raquo;
                        </span>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="text-center mt-3" style="color: var(--gray);">
                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> 
                of <?php echo $total_items; ?> members
            </div>
        </nav>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>