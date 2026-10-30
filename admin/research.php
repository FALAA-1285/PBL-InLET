<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Ensure required columns exist in artikel table
try {
    $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel'");
    $existing_cols = $check_cols->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('id_penelitian', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN id_penelitian INTEGER REFERENCES penelitian(id_penelitian) ON DELETE SET NULL");
    }
    if (!in_array('nim', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN nim VARCHAR(50)");
    }
    if (!in_array('id_member', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN id_member INTEGER REFERENCES member(id_member) ON DELETE SET NULL");
    }
    if (!in_array('id_produk', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN id_produk INTEGER REFERENCES produk(id_produk) ON DELETE SET NULL");
    }
    if (!in_array('id_mitra', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN id_mitra INTEGER REFERENCES mitra(id_mitra) ON DELETE SET NULL");
    }
} catch (PDOException $e) {
    // Columns might already exist or there's a constraint issue, continue anyway
    error_log("Note: Could not add columns to artikel table: " . $e->getMessage());
}

// Helper function to get student identifier column name for penelitian table
function getPenelitianStudentCol($conn) {
    static $student_col = null;
    if ($student_col === null) {
        try {
            $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'penelitian' AND table_schema = 'public'");
            $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('nim', $columns)) {
                $student_col = 'nim';
            } elseif (in_array('id_mhs', $columns)) {
                $student_col = 'id_mhs';
            } else {
                $student_col = false; // No student identifier column
            }
        } catch (PDOException $e) {
            error_log("Error checking penelitian columns: " . $e->getMessage());
            $student_col = false;
        }
    }
    return $student_col;
}

// Handle GET requests for delete (before POST handler)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_fokus_penelitian') {
    $id = $_GET['id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM fokus_penelitian WHERE id_fp = :id");
        $stmt->execute(['id' => $id]);
        header('Location: research.php?tab=research_detail&deleted=1');
        exit;
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_artikel') {
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $konten = $_POST['konten'] ?? '';
        $id_penelitian = $_POST['id_penelitian'] ?? null;
        $nim = $_POST['nim'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        $id_produk = $_POST['id_produk'] ?? null;
        $id_mitra = $_POST['id_mitra'] ?? null;

        try {
            // Check which columns exist in artikel table
            $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel'");
            $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
            $has_id_penelitian = in_array('id_penelitian', $columns);
            $has_nim = in_array('nim', $columns);
            $has_id_member = in_array('id_member', $columns);
            $has_id_produk = in_array('id_produk', $columns);
            $has_id_mitra = in_array('id_mitra', $columns);
            
            // Build dynamic query based on available columns
            $fields = ['judul', 'tahun', 'konten'];
            $values = [':judul', ':tahun', ':konten'];
            $params = [
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'konten' => $konten
            ];
            
            if ($has_id_penelitian) {
                $fields[] = 'id_penelitian';
                $values[] = ':id_penelitian';
                $params['id_penelitian'] = $id_penelitian ?: null;
            }
            if ($has_nim) {
                $fields[] = 'nim';
                $values[] = ':nim';
                $params['nim'] = $nim ?: null;
            }
            if ($has_id_member) {
                $fields[] = 'id_member';
                $values[] = ':id_member';
                $params['id_member'] = $id_member ?: null;
            }
            if ($has_id_produk) {
                $fields[] = 'id_produk';
                $values[] = ':id_produk';
                $params['id_produk'] = $id_produk ?: null;
            }
            if ($has_id_mitra) {
                $fields[] = 'id_mitra';
                $values[] = ':id_mitra';
                $params['id_mitra'] = $id_mitra ?: null;
            }
            
            $query = "INSERT INTO artikel (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $message = 'Article successfully added!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_penelitian') {
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $deskripsi = $_POST['deskripsi'] ?? '';
        $tgl_mulai = $_POST['tgl_mulai'] ?? null;
        $tgl_selesai = $_POST['tgl_selesai'] ?? null;

        try {
            $student_col = getPenelitianStudentCol($conn);
            if ($student_col) {
                $query = "INSERT INTO penelitian (judul, tahun, deskripsi, " . $student_col . ", tgl_mulai, tgl_selesai) VALUES (:judul, :tahun, :deskripsi, NULL, :tgl_mulai, :tgl_selesai)";
                $params = [
                    'judul' => $judul,
                    'tahun' => $tahun ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'tgl_mulai' => $tgl_mulai ?: null,
                    'tgl_selesai' => $tgl_selesai ?: null
                ];
            } else {
                // No student identifier column, skip it
                $query = "INSERT INTO penelitian (judul, tahun, deskripsi, tgl_mulai, tgl_selesai) VALUES (:judul, :tahun, :deskripsi, :tgl_mulai, :tgl_selesai)";
                $params = [
                    'judul' => $judul,
                    'tahun' => $tahun ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'tgl_mulai' => $tgl_mulai ?: null,
                    'tgl_selesai' => $tgl_selesai ?: null
                ];
            }
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $message = 'Research successfully added!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'update_artikel') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $konten = $_POST['konten'] ?? '';
        $id_penelitian = $_POST['id_penelitian'] ?? null;
        $nim = $_POST['nim'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        $id_produk = $_POST['id_produk'] ?? null;
        $id_mitra = $_POST['id_mitra'] ?? null;

        try {
            // Check which columns exist in artikel table
            $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel'");
            $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
            $has_id_penelitian = in_array('id_penelitian', $columns);
            $has_nim = in_array('nim', $columns);
            $has_id_member = in_array('id_member', $columns);
            $has_id_produk = in_array('id_produk', $columns);
            $has_id_mitra = in_array('id_mitra', $columns);
            
            // Build dynamic query based on available columns
            $sets = ['judul = :judul', 'tahun = :tahun', 'konten = :konten'];
            $params = [
                'id' => $id,
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'konten' => $konten
            ];
            
            if ($has_id_penelitian) {
                $sets[] = 'id_penelitian = :id_penelitian';
                $params['id_penelitian'] = $id_penelitian ?: null;
            }
            if ($has_nim) {
                $sets[] = 'nim = :nim';
                $params['nim'] = $nim ?: null;
            }
            if ($has_id_member) {
                $sets[] = 'id_member = :id_member';
                $params['id_member'] = $id_member ?: null;
            }
            if ($has_id_produk) {
                $sets[] = 'id_produk = :id_produk';
                $params['id_produk'] = $id_produk ?: null;
            }
            if ($has_id_mitra) {
                $sets[] = 'id_mitra = :id_mitra';
                $params['id_mitra'] = $id_mitra ?: null;
            }
            
            $query = "UPDATE artikel SET " . implode(', ', $sets) . " WHERE id_artikel = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $message = 'Article successfully updated!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'update_penelitian') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $deskripsi = $_POST['deskripsi'] ?? '';
        $nim = $_POST['nim'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        $id_produk = $_POST['id_produk'] ?? null;
        $id_mitra = $_POST['id_mitra'] ?? null;
        $tgl_mulai = $_POST['tgl_mulai'] ?? null;
        $tgl_selesai = $_POST['tgl_selesai'] ?? null;

        try {
            $student_col = getPenelitianStudentCol($conn);
            if ($student_col) {
                $query = "UPDATE penelitian SET judul = :judul, tahun = :tahun, deskripsi = :deskripsi, " . $student_col . " = :student_id, id_member = :id_member, id_produk = :id_produk, id_mitra = :id_mitra, tgl_mulai = :tgl_mulai, tgl_selesai = :tgl_selesai WHERE id_penelitian = :id";
                $params = [
                    'id' => $id,
                    'judul' => $judul,
                    'tahun' => $tahun ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'student_id' => $nim ?: null,
                    'id_member' => $id_member ?: null,
                    'id_produk' => $id_produk ?: null,
                    'id_mitra' => $id_mitra ?: null,
                    'tgl_mulai' => $tgl_mulai ?: null,
                    'tgl_selesai' => $tgl_selesai ?: null
                ];
            } else {
                // No student identifier column, skip it
                $query = "UPDATE penelitian SET judul = :judul, tahun = :tahun, deskripsi = :deskripsi, id_member = :id_member, id_produk = :id_produk, id_mitra = :id_mitra, tgl_mulai = :tgl_mulai, tgl_selesai = :tgl_selesai WHERE id_penelitian = :id";
                $params = [
                    'id' => $id,
                    'judul' => $judul,
                    'tahun' => $tahun ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'id_member' => $id_member ?: null,
                    'id_produk' => $id_produk ?: null,
                    'id_mitra' => $id_mitra ?: null,
                    'tgl_mulai' => $tgl_mulai ?: null,
                    'tgl_selesai' => $tgl_selesai ?: null
                ];
            }
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $message = 'Research successfully updated!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete_artikel') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM artikel WHERE id_artikel = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Article successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete_penelitian') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM penelitian WHERE id_penelitian = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Research successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_fokus_penelitian') {
        $title = $_POST['title'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $detail = $_POST['detail'] ?? '';

        // Truncate title to 200 chars to match database constraints
        $title = mb_substr($title, 0, 200);
        // Try to alter detail column to TEXT if it's still VARCHAR(150)
        try {
            $conn->exec("ALTER TABLE fokus_penelitian ALTER COLUMN detail TYPE TEXT");
        } catch (PDOException $e) {
            // Column might already be TEXT or error, continue anyway
        }

        try {
            $stmt = $conn->prepare("INSERT INTO fokus_penelitian (title, deskripsi, detail) VALUES (:title, :deskripsi, :detail)");
            $stmt->execute([
                'title' => $title,
                'deskripsi' => $deskripsi ?: null,
                'detail' => $detail ?: null
            ]);
            // Redirect to prevent resubmission and show single success message
            header('Location: research.php?tab=research_detail&added=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'update_fokus_penelitian') {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $detail = $_POST['detail'] ?? '';

        // Truncate title to 200 chars to match database constraints
        $title = mb_substr($title, 0, 200);
        // Try to alter detail column to TEXT if it's still VARCHAR(150)
        try {
            $conn->exec("ALTER TABLE fokus_penelitian ALTER COLUMN detail TYPE TEXT");
        } catch (PDOException $e) {
            // Column might already be TEXT or error, continue anyway
        }

        try {
            $stmt = $conn->prepare("UPDATE fokus_penelitian SET title = :title, deskripsi = :deskripsi, detail = :detail WHERE id_fp = :id");
            $stmt->execute([
                'id' => $id,
                'title' => $title,
                'deskripsi' => $deskripsi ?: null,
                'detail' => $detail ?: null
            ]);
            // Redirect to prevent resubmission and show single success message
            header('Location: research.php?tab=research_detail&updated=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete_fokus_penelitian') {
        $id = $_POST['id'] ?? 0;

        try {
            $stmt = $conn->prepare("DELETE FROM fokus_penelitian WHERE id_fp = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Research detail successfully deleted!';
            $message_type = 'success';
            // Redirect to prevent resubmission
            header('Location: research.php?tab=research_detail&deleted=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_produk') {
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $gambar = $_POST['gambar'] ?? ''; // URL input
        $try = trim($_POST['try'] ?? ''); // Try URL

        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'produk/');
            if ($uploadResult['success']) {
                $gambar = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($nama_produk)) {
            $message = 'Product name is required!';
            $message_type = 'error';
        } else {
            try {
                // Try to add gambar column if it doesn't exist
                try {
                    $conn->exec("ALTER TABLE produk ADD COLUMN IF NOT EXISTS gambar TEXT");
                } catch (PDOException $e) {
                    // Column might already exist, continue anyway
                }
                
                // Try to add try column if it doesn't exist
                try {
                    $conn->exec("ALTER TABLE produk ADD COLUMN IF NOT EXISTS try TEXT");
                } catch (PDOException $e) {
                    // Column might already exist, continue anyway
                }
                
                // Fix sequence if it's out of sync
                try {
                    $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_produk), 0) as max_id FROM produk");
                    $max_id = $max_id_stmt->fetch()['max_id'];
                    $conn->exec("SELECT setval('produk_id_produk_seq', " . ($max_id + 1) . ", false)");
                } catch (PDOException $seq_e) {
                    // Sequence might not exist or error, continue anyway
                }
                
                $stmt = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, gambar, try) VALUES (:nama_produk, :deskripsi, :gambar, :try)");
                $stmt->execute([
                    'nama_produk' => $nama_produk,
                    'deskripsi' => $deskripsi ?: null,
                    'gambar' => $gambar ?: null,
                    'try' => $try ?: null
                ]);
                header('Location: research.php?tab=product&added=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_produk') {
        $id = $_POST['id'] ?? 0;
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $gambar = $_POST['gambar'] ?? '';
        $current_gambar = $_POST['current_gambar'] ?? '';
        $try = trim($_POST['try'] ?? ''); // Try URL

        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'produk/');
            if ($uploadResult['success']) {
                // Delete old image if it's a local file
                if ($current_gambar && strpos($current_gambar, 'uploads/produk/') === 0) {
                    deleteUploadedFile($current_gambar);
                }
                $gambar = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        } elseif (empty($gambar) && !empty($current_gambar)) {
            // Keep current image if no new image provided
            $gambar = $current_gambar;
        }

        if (empty($nama_produk)) {
            $message = 'Product name is required!';
            $message_type = 'error';
        } else {
            try {
                // Try to add gambar column if it doesn't exist
                try {
                    $conn->exec("ALTER TABLE produk ADD COLUMN IF NOT EXISTS gambar TEXT");
                } catch (PDOException $e) {
                    // Column might already exist, continue anyway
                }
                
                // Try to add try column if it doesn't exist
                try {
                    $conn->exec("ALTER TABLE produk ADD COLUMN IF NOT EXISTS try TEXT");
                } catch (PDOException $e) {
                    // Column might already exist, continue anyway
                }
                
                // Always update all columns including try
                if ($gambar) {
                    $stmt = $conn->prepare("UPDATE produk SET nama_produk = :nama_produk, deskripsi = :deskripsi, gambar = :gambar, try = :try WHERE id_produk = :id");
                    $stmt->execute([
                        'id' => $id,
                        'nama_produk' => $nama_produk,
                        'deskripsi' => $deskripsi,
                        'gambar' => $gambar,
                        'try' => $try
                    ]);
                } else {
                    $stmt = $conn->prepare("UPDATE produk SET nama_produk = :nama_produk, deskripsi = :deskripsi, try = :try WHERE id_produk = :id");
                    $stmt->execute([
                        'id' => $id,
                        'nama_produk' => $nama_produk,
                        'deskripsi' => $deskripsi ?: null,
                        'try' => $try ?: null
                    ]);
                }
                header('Location: research.php?tab=product&updated=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_produk') {
        $id = $_POST['id'] ?? 0;

        try {
            $stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = :id");
            $stmt->execute(['id' => $id]);
            header('Location: research.php?tab=product&deleted=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup for articles
$items_per_page = 10;
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'artikel';

// Try to alter detail column to TEXT if it's still VARCHAR(150) - run once
try {
    $conn->exec("ALTER TABLE fokus_penelitian ALTER COLUMN detail TYPE TEXT");
} catch (PDOException $e) {
    // Column might already be TEXT or error, continue anyway
}

// Get fokus_penelitian data
try {
    $stmt = $conn->query("SELECT * FROM fokus_penelitian ORDER BY id_fp");
    $fokus_penelitian_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $fokus_penelitian_list = [];
}

// Get produk data - show all products, ordered by id descending (newest first)
try {
    $stmt = $conn->query("SELECT * FROM produk ORDER BY id_produk DESC");
    $produk_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $produk_list = [];
}

// Get penelitian data for dropdown (for artikel form)
try {
    $stmt = $conn->query("SELECT id_penelitian, judul FROM penelitian ORDER BY tahun DESC, judul");
    $penelitian_dropdown = $stmt->fetchAll();
} catch (PDOException $e) {
    $penelitian_dropdown = [];
}

// Get page numbers - check if we're on the correct tab
$current_page_artikel = 1;
$current_page_progress = 1;

if ($current_tab === 'artikel' && isset($_GET['page'])) {
    $current_page_artikel = max(1, intval($_GET['page']));
} elseif (isset($_GET['page_artikel'])) {
    $current_page_artikel = max(1, intval($_GET['page_artikel']));
}

if ($current_tab === 'penelitian' && isset($_GET['page'])) {
    $current_page_progress = max(1, intval($_GET['page']));
} elseif (isset($_GET['page_penelitian'])) {
    $current_page_progress = max(1, intval($_GET['page_penelitian']));
}

// Get total count for articles
$stmt = $conn->query("SELECT COUNT(*) FROM artikel");
$total_items_artikel = $stmt->fetchColumn();
$total_pages_artikel = ceil($total_items_artikel / $items_per_page);
$offset_artikel = ($current_page_artikel - 1) * $items_per_page;

// Get articles with pagination (for display) - include research info
// Check if id_penelitian column exists first
try {
    $check_col = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel' AND column_name = 'id_penelitian'");
    $has_id_penelitian = $check_col->rowCount() > 0;
} catch (PDOException $e) {
    $has_id_penelitian = false;
}

if ($has_id_penelitian) {
    $stmt = $conn->prepare("SELECT a.*, p.judul as penelitian_judul 
                            FROM artikel a 
                            LEFT JOIN penelitian p ON a.id_penelitian = p.id_penelitian 
                            ORDER BY a.tahun DESC, a.judul 
                            LIMIT :limit OFFSET :offset");
} else {
    // Fallback if column doesn't exist yet
    $stmt = $conn->prepare("SELECT a.*, NULL as penelitian_judul 
                            FROM artikel a 
                            ORDER BY a.tahun DESC, a.judul 
                            LIMIT :limit OFFSET :offset");
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_artikel, PDO::PARAM_INT);
$stmt->execute();
$artikels = $stmt->fetchAll();

// Get all articles for dropdown (no pagination)
$stmt = $conn->query("SELECT id_artikel, judul FROM artikel ORDER BY judul");
$artikels_dropdown = $stmt->fetchAll();

// Get total count for penelitian
$stmt = $conn->query("SELECT COUNT(*) FROM penelitian");
$total_items_progress = $stmt->fetchColumn();
$total_pages_progress = ceil($total_items_progress / $items_per_page);
$offset_progress = ($current_page_progress - 1) * $items_per_page;

// Get penelitian with pagination
// Check what columns exist in penelitian table for student reference
try {
    $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'penelitian' AND table_schema = 'public'");
    $penelitian_columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine which column to use for student identifier
    $student_col = null;
    if (in_array('nim', $penelitian_columns)) {
        $student_col = 'nim';
    } elseif (in_array('id_mhs', $penelitian_columns)) {
        $student_col = 'id_mhs';
    }
    
    if ($student_col) {
        // Build query with student info if we can identify the student column
        // Ensure 'nim' column is always available in result for JavaScript compatibility
        $query = "SELECT p.*, 
                      " . ($student_col === 'nim' ? "p.nim" : "p." . $student_col . " as nim") . ",
                      " . ($student_col === 'nim' 
                          ? "COALESCE((SELECT nama FROM mahasiswa WHERE nim = p.nim LIMIT 1), 'N/A') as mahasiswa_nama"
                          : "COALESCE((SELECT nama FROM mahasiswa WHERE id_mahasiswa = p.id_mhs LIMIT 1), 'N/A') as mahasiswa_nama") . ",
                      mem.nama as member_nama, 
                      pr.nama_produk, 
                      mt.nama_institusi as mitra_nama
                  FROM penelitian p
                  LEFT JOIN member mem ON p.id_member = mem.id_member
                  LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                  LEFT JOIN mitra mt ON p.id_mitra = mt.id_mitra
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
    } else {
        // No student identifier column found
        $query = "SELECT p.*, 
                      NULL as nim,
                      'N/A' as mahasiswa_nama,
                      mem.nama as member_nama, 
                      pr.nama_produk, 
                      mt.nama_institusi as mitra_nama
                  FROM penelitian p
                  LEFT JOIN member mem ON p.id_member = mem.id_member
                  LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                  LEFT JOIN mitra mt ON p.id_mitra = mt.id_mitra
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
    $stmt->execute();
    $progress_list = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback query without student info
    error_log("Error in penelitian query: " . $e->getMessage());
    $query_fallback = "SELECT p.*, 
                          NULL as nim,
                          'N/A' as mahasiswa_nama,
                          mem.nama as member_nama, 
                          pr.nama_produk, 
                          mt.nama_institusi as mitra_nama
                      FROM penelitian p
                      LEFT JOIN member mem ON p.id_member = mem.id_member
                      LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                      LEFT JOIN mitra mt ON p.id_mitra = mt.id_mitra
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query_fallback);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
    $stmt->execute();
    $progress_list = $stmt->fetchAll();
}

// Get dropdown options
// Check what columns exist in mahasiswa table
try {
    $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'mahasiswa' AND table_schema = 'public'");
    $mahasiswa_columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine which columns to use
    $id_col = 'id_mahasiswa'; // Default to id_mahasiswa
    $identifier_col = null;
    
    if (in_array('nim', $mahasiswa_columns)) {
        $identifier_col = 'nim';
    } elseif (in_array('id_mahasiswa', $mahasiswa_columns)) {
        $identifier_col = 'id_mahasiswa';
    }
    
    if ($identifier_col) {
        $query = "SELECT " . $identifier_col . " as nim, nama FROM mahasiswa ORDER BY nama";
    } else {
        // Fallback: use id_mahasiswa if available, otherwise use first column
        $query = "SELECT id_mahasiswa as nim, nama FROM mahasiswa ORDER BY nama";
    }
    
    $stmt = $conn->query($query);
    $mahasiswa_list = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching mahasiswa list: " . $e->getMessage());
    // Fallback: try with id_mahasiswa
    try {
        $stmt = $conn->query("SELECT id_mahasiswa as nim, nama FROM mahasiswa ORDER BY nama");
        $mahasiswa_list = $stmt->fetchAll();
    } catch (PDOException $e2) {
        error_log("Error in fallback query: " . $e2->getMessage());
        $mahasiswa_list = [];
    }
}

$stmt = $conn->query("SELECT id_member, nama FROM member ORDER BY nama");
$member_list = $stmt->fetchAll();

require __DIR__ . '/../views/admin/research.php';
