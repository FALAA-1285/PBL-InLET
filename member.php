
<!-- Nanti ini error soale perlu dikonesikan sama database member biar datanya muncul -->

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
            
            <?php foreach ($members as $member): ?>
            <div class="team-card">
                <div class="team-avatar"><?php echo $member['initials']; ?></div>
                <h4><?php echo $member['name']; ?></h4>
                <p class="role-title" style="font-weight: 600; color: var(--primary-dark); margin-bottom: 0.5rem;"><?php echo $member['role']; ?></p>
                <p style="color: var(--gray); font-size: 0.95em;"><?php echo $member['specialty']; ?></p>
                <a href="profile_detail.php?id=<?php echo $member['initials']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; margin-top: 15px; font-size: 0.9em;">View Profile</a>
            </div>
            <?php endforeach; ?>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>

</html>