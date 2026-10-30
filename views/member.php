<?php
/**
 * Variables provided by member.php
 *
 * @var array $page_info
 * @var array $available_letters
 * @var string $search_query
 * @var string $letter_filter
 * @var array $members
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Members - InLET'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-member.css">
    <link rel="stylesheet" href="assets/css/site.css?v=<?= filemtime(__DIR__ . '/../assets/css/site.css') ?>">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Our Experts - Information And Learning Engineering Technology'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Driving innovation in Information and Learning Engineering Technology.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="team-section" id="profiles">
            <div class="container">
                <div class="section-title">
                    <h2>Members</h2>
                    <p>The lecturers and researchers behind InLET's projects.</p>
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

                                    <div class="member-info text-center">
                                        <h3 class="member-name"><?php echo htmlspecialchars($member['nama']); ?></h3>
                                        <div class="member-role"><?php echo htmlspecialchars($member['jabatan'] ?: 'Member'); ?></div>
                                        <?php if (!empty($member['email'])): ?>
                                            <div class="member-email">
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($member['google_scholar'])): ?>
                                            <div class="member-scholar">
                                                <a href="<?php echo htmlspecialchars($member['google_scholar']); ?>" target="_blank" title="Google Scholar">
                                                    <img src="assets/google-scholar.png" alt="Google Scholar" width="28">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($member['deskripsi'])): ?>
                                            <p class="member-desc" style="display:none;">
                                                <?php echo htmlspecialchars($member['deskripsi']); ?>
                                            </p>
                                            <a href="javascript:void(0)" class="member-toggle">
                                                More Info
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <div class="footer-wrap">
        <?php include __DIR__ . '/partials/footer.php'; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".member-toggle").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    const desc = btn.previousElementSibling;
                    if (!desc) return;

                    if (desc.style.display === "block") {
                        desc.style.display = "none";
                        btn.textContent = "More Info";
                    } else {
                        desc.style.display = "block";
                        btn.textContent = "Less Info";
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>