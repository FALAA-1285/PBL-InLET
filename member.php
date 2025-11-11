<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Get all members with profiles
$stmt = $conn->query("SELECT m.*, pm.alamat, pm.no_tlp, pm.deskripsi 
                      FROM member m 
                      LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
                      ORDER BY m.nama");
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InLET - Our Research Members & Profiles</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero" style="margin-top: 80px; padding: 6rem 2rem;">
        <div class="hero-content">
            <h1>Our Research Members</h1>
            <p>Meet the <b>Expert Researchers</b> and innovators driving.</p>
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
                    <p>Belum ada member yang terdaftar.</p>
                </div>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <div class="team-card">
                        <div class="team-avatar"><?php echo getInitials($member['nama']); ?></div>
                        <h4><?php echo htmlspecialchars($member['nama']); ?></h4>
                        <?php if ($member['jabatan']): ?>
                            <p class="role-title" style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($member['jabatan']); ?></p>
                        <?php endif; ?>
                        <?php if ($member['deskripsi']): ?>
                            <p style="color: var(--gray); font-size: 0.95em;"><?php echo htmlspecialchars(substr($member['deskripsi'], 0, 100)) . '...'; ?></p>
                        <?php endif; ?>
                        <?php if ($member['email']): ?>
                            <p style="color: var(--gray); font-size: 0.9em; margin-top: 0.5rem;"><?php echo htmlspecialchars($member['email']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
