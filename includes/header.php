<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style-header.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">

            <!-- Logo -->
            <a href="index.php" class="navbar-brand">
                <img src="assets/logo.png" alt="Logo" style="height: 40px;">
            </a>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'research.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="research.php">Research</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'member.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="member.php">Member</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'news.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="news.php">News</a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
