<?php
// load-gallery.php
// Endpoint sederhana: mengembalikan JSON berisi $limit gambar per page.

// Dummy 100 images (replace with real DB query in production)
$gallery = [];
for ($i = 1; $i <= 100; $i++) {
    $w = rand(300, 450);
    $h = rand(250, 500);
    $gallery[] = [
        "img" => "https://picsum.photos/seed/$i/{$w}/{$h}"
    ];
}

$limit = 12;
$page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
$start = ($page - 1) * $limit;

$data = array_slice($gallery, $start, $limit);

// JSON response
header('Content-Type: application/json; charset=utf-8');
echo json_encode(array_values($data));
exit;
