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

// Get produk data
try {
    $stmt = $conn->query("SELECT * FROM produk ORDER BY id_produk");
    $produk_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $produk_list = [];
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Research - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: var(--light);
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border-radius: 18px;
        }

        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .admin-header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }

        .admin-header p {
            color: var(--gray);
        }

        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .data-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background: var(--light);
            color: var(--dark);
            font-weight: 600;
        }

        .data-table tr:hover {
            background: var(--light);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            margin-right: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .data-table td:last-child {
            white-space: nowrap;
        }

        .edit-form-section {
            display: none;
        }

        .edit-form-section.active {
            display: block;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 1rem;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-info {
            text-align: center;
            color: var(--gray);
            margin-top: 1rem;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>

<body>
    <?php $active_page = 'research';
    include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-flask-line"></i> Manage Research</h1>

            <div class="cms-content">
                <?php 
                // Show message from URL parameter first (for redirects), then from $message variable
                if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                    <div class="message success">
                        Research detail successfully added!
                    </div>
                <?php elseif (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                    <div class="message success">
                        Research detail successfully updated!
                    </div>
                <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="message success">
                        Research detail successfully deleted!   
                    </div>
                <?php elseif ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="tabs">
                    <a href="?tab=artikel&page=1"
                        class="tab <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">Article</a>
                    <a href="?tab=penelitian&page=1"
                        class="tab <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">Research</a>
                    <a href="?tab=research_detail"
                        class="tab <?php echo ($current_tab === 'research_detail') ? 'active' : ''; ?>">Research Detail</a>
                    <a href="?tab=product"
                        class="tab <?php echo ($current_tab === 'product') ? 'active' : ''; ?>">Product</a>
                </div>

                <!-- Artikel Tab -->
                <div id="artikel-tab" class="tab-content <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">
                    <!-- Edit Artikel Form (Hidden by default) -->
                    <div id="edit-artikel-section" class="form-section edit-form-section">
                        <h2>Edit Article</h2>
                        <form method="POST" action="" id="edit-artikel-form">
                            <input type="hidden" name="action" value="update_artikel">
                            <input type="hidden" name="id" id="edit_artikel_id">
                            <div class="form-group">
                                <label>Article Title</label>
                                <input type="text" name="judul" id="edit_artikel_judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" id="edit_artikel_tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="konten" id="edit_artikel_konten" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Research (Optional)</label>
                                <select name="id_penelitian" id="edit_artikel_id_penelitian">
                                    <option value="">-- Select Research --</option>
                                    <?php foreach ($penelitian_dropdown as $penelitian): ?>
                                        <option value="<?php echo $penelitian['id_penelitian']; ?>">
                                            <?php echo htmlspecialchars($penelitian['judul']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Student (Optional)</label>
                                <select name="nim" id="edit_artikel_nim">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <option value="<?php echo $mhs['nim']; ?>">
                                            <?php echo htmlspecialchars($mhs['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Member (Optional)</label>
                                <select name="id_member" id="edit_artikel_id_member">
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($member_list as $mem): ?>
                                        <option value="<?php echo $mem['id_member']; ?>">
                                            <?php echo htmlspecialchars($mem['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Product (Optional)</label>
                                <select name="id_produk" id="edit_artikel_id_produk">
                                    <option value="">-- Select Product --</option>
                                    <?php
                                    $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                                    $produk_list = $produk_stmt->fetchAll();
                                    foreach ($produk_list as $prod): ?>
                                        <option value="<?php echo $prod['id_produk']; ?>">
                                            <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Partner (Optional)</label>
                                <select name="id_mitra" id="edit_artikel_id_mitra">
                                    <option value="">-- Select Partner --</option>
                                    <?php
                                    $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                                    $mitra_list = $mitra_stmt->fetchAll();
                                    foreach ($mitra_list as $mit): ?>
                                        <option value="<?php echo $mit['id_mitra']; ?>">
                                            <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit">Update Article</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditArtikel()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Article</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_artikel">
                            <div class="form-group">
                                <label>Article Title</label>
                                <input type="text" name="judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="konten" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Research (Optional)</label>
                                <select name="id_penelitian">
                                    <option value="">-- Select Research --</option>
                                    <?php foreach ($penelitian_dropdown as $penelitian): ?>
                                        <option value="<?php echo $penelitian['id_penelitian']; ?>">
                                            <?php echo htmlspecialchars($penelitian['judul']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Student (Optional)</label>
                                <select name="nim">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <option value="<?php echo $mhs['nim']; ?>">
                                            <?php echo htmlspecialchars($mhs['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Member (Optional)</label>
                                <select name="id_member">
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($member_list as $mem): ?>
                                        <option value="<?php echo $mem['id_member']; ?>">
                                            <?php echo htmlspecialchars($mem['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Product (Optional)</label>
                                <select name="id_produk">
                                    <option value="">-- Select Product --</option>
                                    <?php
                                    if (!isset($produk_list)) {
                                        $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                                        $produk_list = $produk_stmt->fetchAll();
                                    }
                                    foreach ($produk_list as $prod): ?>
                                        <option value="<?php echo $prod['id_produk']; ?>">
                                            <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Partner (Optional)</label>
                                <select name="id_mitra">
                                    <option value="">-- Select Partner --</option>
                                    <?php
                                    if (!isset($mitra_list)) {
                                        $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                                        $mitra_list = $mitra_stmt->fetchAll();
                                    }
                                    foreach ($mitra_list as $mit): ?>
                                        <option value="<?php echo $mit['id_mitra']; ?>">
                                            <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit">Add Article</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Article List (<?php echo count($artikels); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Research</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($artikels)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No articles yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($artikels as $artikel): ?>
                                        <tr>
                                            <td><?php echo $artikel['id_artikel']; ?></td>
                                            <td><?php echo htmlspecialchars($artikel['judul']); ?></td>
                                            <td><?php echo $artikel['tahun'] ?? '-'; ?></td>
                                            <td><?php echo htmlspecialchars($artikel['penelitian_judul'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                <button type="button" class="btn-edit"
                                                    onclick="editArtikel(<?php echo htmlspecialchars(json_encode($artikel)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    <input type="hidden" name="action" value="delete_artikel">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo $artikel['id_artikel']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination for Artikel -->
                        <?php if ($total_pages_artikel > 1): ?>
                            <div class="pagination">
                                <?php if ($current_page_artikel > 1): ?>
                                    <a href="?tab=artikel&page=<?php echo $current_page_artikel - 1; ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page_artikel - 2);
                                $end_page = min($total_pages_artikel, $current_page_artikel + 2);

                                if ($start_page > 1): ?>
                                    <a href="?tab=artikel&page=1">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page_artikel): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?tab=artikel&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages_artikel): ?>
                                    <?php if ($end_page < $total_pages_artikel - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a
                                        href="?tab=artikel&page=<?php echo $total_pages_artikel; ?>"><?php echo $total_pages_artikel; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page_artikel < $total_pages_artikel): ?>
                                    <a href="?tab=artikel&page=<?php echo $current_page_artikel + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset_artikel + 1); ?> -
                                <?php echo min($offset_artikel + $items_per_page, $total_items_artikel); ?> of
                                <?php echo $total_items_artikel; ?> articles
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Penelitian Tab -->
                <div id="penelitian-tab"
                    class="tab-content <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">
                    <!-- Edit Penelitian Form (Hidden by default) -->
                    <div id="edit-penelitian-section" class="form-section edit-form-section">
                        <h2>Edit Research</h2>
                        <form method="POST" action="" id="edit-penelitian-form">
                            <input type="hidden" name="action" value="update_penelitian">
                            <input type="hidden" name="id" id="edit_penelitian_id">
                            <div class="form-group">
                                <label>Research Title *</label>
                                <input type="text" name="judul" id="edit_penelitian_judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" id="edit_penelitian_tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" id="edit_penelitian_deskripsi"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="tgl_mulai" id="edit_penelitian_tgl_mulai" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="tgl_selesai" id="edit_penelitian_tgl_selesai">
                            </div>
                            <button type="submit" class="btn-submit">Update Research</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditPenelitian()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Research</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_penelitian">
                            <div class="form-group">
                                <label>Research Title *</label>
                                <input type="text" name="judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="tgl_mulai" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="tgl_selesai">
                            </div>
                            <button type="submit" class="btn-submit">Add Research</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Research List (<?php echo count($progress_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Student</th>
                                    <th>Member</th>
                                    <th>Product</th>
                                    <th>Partner</th>
                                    <th>Start Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($progress_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center muted-gray">No research yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($progress_list as $penelitian): ?>
                                        <tr>
                                            <td><?php echo $penelitian['id_penelitian']; ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['judul']); ?></td>
                                            <td><?php echo $penelitian['tahun'] ?? '-'; ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['mahasiswa_nama'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['member_nama'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['nama_produk'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['mitra_nama'] ?? '-'); ?></td>
                                            <td><?php echo $penelitian['tgl_mulai'] ? date('d M Y', strtotime($penelitian['tgl_mulai'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                <button type="button" class="btn-edit"
                                                    onclick="editPenelitian(<?php echo htmlspecialchars(json_encode($penelitian)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this research?');">
                                                    <input type="hidden" name="action" value="delete_penelitian">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo $penelitian['id_penelitian']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination for Penelitian -->
                        <?php if ($total_pages_progress > 1): ?>
                            <div class="pagination">
                                <?php if ($current_page_progress > 1): ?>
                                    <a href="?tab=penelitian&page=<?php echo $current_page_progress - 1; ?>">&laquo;
                                        Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page_progress - 2);
                                $end_page = min($total_pages_progress, $current_page_progress + 2);

                                if ($start_page > 1): ?>
                                    <a href="?tab=penelitian&page=1">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page_progress): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?tab=penelitian&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages_progress): ?>
                                    <?php if ($end_page < $total_pages_progress - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a
                                        href="?tab=penelitian&page=<?php echo $total_pages_progress; ?>"><?php echo $total_pages_progress; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page_progress < $total_pages_progress): ?>
                                    <a href="?tab=penelitian&page=<?php echo $current_page_progress + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset_progress + 1); ?> -
                                <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> of
                                <?php echo $total_items_progress; ?> research
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Research Detail Tab -->
                <div id="research_detail-tab"
                    class="tab-content <?php echo ($current_tab === 'research_detail') ? 'active' : ''; ?>">
                    <!-- Edit Research Detail Form (Hidden by default) -->
                    <div id="edit-research-detail-section" class="form-section edit-form-section">
                        <h2>Edit Research Detail</h2>
                        <form method="POST" action="" id="edit-research-detail-form">
                            <input type="hidden" name="action" value="update_fokus_penelitian">
                            <input type="hidden" name="id" id="edit_research_detail_id">
                            <div class="form-group">
                                <label>Title * (Max 200 characters)</label>
                                <input type="text" name="title" id="edit_research_detail_title" maxlength="200" required>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" id="edit_research_detail_deskripsi" rows="3"></textarea>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Detail</label>
                                <textarea name="detail" id="edit_research_detail_detail" rows="12" style="min-height: 200px;"></textarea>
                                <small class="text-muted">Used for "Research Fields" section. Use "-" at the start of each line for bullet points.</small>
                                <div class="char-counter" id="edit_detail_counter" style="text-align: right; color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
                                    <span id="edit_detail_count">0</span> characters
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Update Research Detail</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditResearchDetail()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Research Detail</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_fokus_penelitian">
                            <div class="form-group">
                                <label>Title * (Max 200 characters)</label>
                                <input type="text" name="title" maxlength="200" required>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" rows="3"></textarea>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Detail</label>
                                <textarea name="detail" id="add_research_detail_detail" rows="12" style="min-height: 200px;"></textarea>
                                <small class="text-muted">Used for "Research Fields" section. Use "-" at the start of each line for bullet points.</small>
                                <div class="char-counter" id="add_detail_counter" style="text-align: right; color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
                                    <span id="add_detail_count">0</span> characters
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Add Research Detail</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Research Detail List (<?php echo count($fokus_penelitian_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Detail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fokus_penelitian_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No research detail yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($fokus_penelitian_list as $fp): ?>
                                        <tr>
                                            <td><?php echo $fp['id_fp']; ?></td>
                                            <td><?php echo htmlspecialchars($fp['title']); ?></td>
                                            <td><?php echo htmlspecialchars($fp['deskripsi'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($fp['detail'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-edit"
                                                        onclick="editResearchDetail(<?php echo htmlspecialchars(json_encode($fp)); ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <a href="?tab=research_detail&action=delete_fokus_penelitian&id=<?php echo $fp['id_fp']; ?>"
                                                        class="btn-delete"
                                                        onclick="return confirm('Are you sure you want to delete this research detail?');">
                                                        <i class="ri-delete-bin-line"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Product Tab -->
                <div id="product-tab"
                    class="tab-content <?php echo ($current_tab === 'product') ? 'active' : ''; ?>">
                    <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                        <div class="message success">
                            Product successfully added!
                        </div>
                    <?php elseif (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                        <div class="message success">
                            Product successfully updated!
                        </div>
                    <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                        <div class="message success">
                            Product successfully deleted!
                        </div>
                    <?php elseif ($message && $current_tab === 'product'): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Edit Product Form (Hidden by default) -->
                    <div id="edit-product-section" class="form-section edit-form-section">
                        <h2>Edit Product</h2>
                        <form method="POST" id="edit-product-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_produk">
                            <input type="hidden" name="id" id="edit_product_id">
                            <input type="hidden" name="current_gambar" id="edit_product_current_gambar">
                            <div class="form-group">
                                <label for="edit_product_nama">Product Name *</label>
                                <input type="text" id="edit_product_nama" name="nama_produk" required
                                    maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="edit_product_deskripsi">Description</label>
                                <textarea id="edit_product_deskripsi" name="deskripsi" rows="5"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_gambar_file">Upload Product Image (File)</label>
                                <input type="file" id="edit_product_gambar_file" name="gambar_file"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_gambar">Or Enter Image URL</label>
                                <input type="text" id="edit_product_gambar" name="gambar"
                                    placeholder="https://example.com/image.jpg">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_try">Try URL (Optional)</label>
                                <input type="text" id="edit_product_try" name="try"
                                    placeholder="https://example.com/try-now">
                                <small class="d-block mt-2 text-muted small">URL untuk tombol "Try Now" pada produk</small>
                            </div>
                            <div id="edit_product_image_preview" class="mb-3" style="display: none;">
                                <label>Current Image:</label>
                                <div>
                                    <img id="edit_product_image_preview_img" src="" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Update Product</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditProduct()">Cancel</button>
                        </form>
                    </div>

                    <!-- Add Product Form -->
                    <div class="form-section">
                        <h2>Add New Product</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_produk">
                            <div class="form-group">
                                <label for="product_nama">Product Name *</label>
                                <input type="text" id="product_nama" name="nama_produk" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="product_deskripsi">Description</label>
                                <textarea id="product_deskripsi" name="deskripsi" rows="5"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="product_gambar_file">Upload Product Image (File)</label>
                                <input type="file" id="product_gambar_file" name="gambar_file"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="form-group">
                                <label for="product_gambar">Or Enter Image URL</label>
                                <input type="text" id="product_gambar" name="gambar"
                                    placeholder="https://example.com/image.jpg">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored</small>
                            </div>
                            <div class="form-group">
                                <label for="product_try">Try URL (Optional)</label>
                                <input type="text" id="product_try" name="try"
                                    placeholder="https://example.com/try-now">
                                <small class="d-block mt-2 text-muted small">URL untuk tombol "Try Now" pada produk</small>
                            </div>
                            <button type="submit" class="btn-submit">Add Product</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Product List (<?php echo count($produk_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produk_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No products yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($produk_list as $produk): ?>
                                        <tr>
                                            <td><?php echo $produk['id_produk']; ?></td>
                                            <td>
                                                <?php if (!empty($produk['gambar'])): ?>
                                                    <img src="<?php echo htmlspecialchars($produk['gambar']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
                                                         style="max-width: 100px; max-height: 100px; object-fit: contain; border-radius: 8px;"
                                                         onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                                            <td><?php echo htmlspecialchars($produk['deskripsi'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-edit"
                                                        onclick="editProduct(<?php 
                                                            // Convert null to empty string to prevent "undefined" in JavaScript
                                                            $produk_json = [
                                                                'id_produk' => $produk['id_produk'] ?? 0,
                                                                'nama_produk' => $produk['nama_produk'] ?? '',
                                                                'deskripsi' => $produk['deskripsi'] ?? '',
                                                                'gambar' => $produk['gambar'] ?? '',
                                                                'try' => $produk['try'] ?? ''
                                                            ];
                                                            echo htmlspecialchars(json_encode($produk_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                                                        ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                        <input type="hidden" name="action" value="delete_produk">
                                                        <input type="hidden" name="id" value="<?php echo $produk['id_produk']; ?>">
                                                        <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');

            // Redirect to first page of selected tab (only for artikel and penelitian)
            if (tabName === 'artikel' || tabName === 'penelitian') {
            window.location.href = '?tab=' + tabName + '&page=1';
            } else {
                window.location.href = '?tab=' + tabName;
            }
        }

        // Set active tab based on URL parameter
        window.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'artikel';

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            // Show selected tab
            const tabElement = document.getElementById(tab + '-tab');
            if (tabElement) {
                tabElement.classList.add('active');
            }
            const tabButtons = document.querySelectorAll('.tab');
            tabButtons.forEach(btn => {
                const href = btn.getAttribute('href') || '';
                if (href.includes('tab=' + tab)) {
                    btn.classList.add('active');
                }
            });
        });

        function editArtikel(artikel) {
            document.getElementById('edit_artikel_id').value = artikel.id_artikel;
            document.getElementById('edit_artikel_judul').value = artikel.judul || '';
            document.getElementById('edit_artikel_tahun').value = artikel.tahun || '';
            document.getElementById('edit_artikel_konten').value = artikel.konten || '';
            document.getElementById('edit_artikel_id_penelitian').value = artikel.id_penelitian || '';
            if (document.getElementById('edit_artikel_nim')) {
                document.getElementById('edit_artikel_nim').value = artikel.nim || '';
            }
            if (document.getElementById('edit_artikel_id_member')) {
                document.getElementById('edit_artikel_id_member').value = artikel.id_member || '';
            }
            if (document.getElementById('edit_artikel_id_produk')) {
                document.getElementById('edit_artikel_id_produk').value = artikel.id_produk || '';
            }
            if (document.getElementById('edit_artikel_id_mitra')) {
                document.getElementById('edit_artikel_id_mitra').value = artikel.id_mitra || '';
            }

            document.getElementById('edit-artikel-section').classList.add('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-artikel-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditArtikel() {
            document.getElementById('edit-artikel-section').classList.remove('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-artikel-form').reset();
        }

        function editPenelitian(penelitian) {
            document.getElementById('edit_penelitian_id').value = penelitian.id_penelitian;
            document.getElementById('edit_penelitian_judul').value = penelitian.judul || '';
            document.getElementById('edit_penelitian_tahun').value = penelitian.tahun || '';
            document.getElementById('edit_penelitian_deskripsi').value = penelitian.deskripsi || '';
            document.getElementById('edit_penelitian_tgl_mulai').value = penelitian.tgl_mulai || '';
            document.getElementById('edit_penelitian_tgl_selesai').value = penelitian.tgl_selesai || '';

            document.getElementById('edit-penelitian-section').classList.add('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-penelitian-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditPenelitian() {
            document.getElementById('edit-penelitian-section').classList.remove('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-penelitian-form').reset();
        }

        function editResearchDetail(researchDetail) {
            document.getElementById('edit_research_detail_id').value = researchDetail.id_fp;
            document.getElementById('edit_research_detail_title').value = researchDetail.title || '';
            document.getElementById('edit_research_detail_deskripsi').value = researchDetail.deskripsi || '';
            const detailTextarea = document.getElementById('edit_research_detail_detail');
            detailTextarea.value = researchDetail.detail || '';
            // Update character counter
            updateCharCounter('edit_research_detail_detail', 'edit_detail_count');

            document.getElementById('edit-research-detail-section').classList.add('active');
            document.querySelector('#research_detail-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-research-detail-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditResearchDetail() {
            document.getElementById('edit-research-detail-section').classList.remove('active');
            document.querySelector('#research_detail-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-research-detail-form').reset();
            // Reset counters
            updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
        }

        function editProduct(product) {
            // Populate edit form - handle null/undefined to prevent "undefined" text
            document.getElementById('edit_product_id').value = product.id_produk || '';
            document.getElementById('edit_product_nama').value = product.nama_produk || '';
            document.getElementById('edit_product_deskripsi').value = product.deskripsi || '';
            document.getElementById('edit_product_gambar').value = product.gambar || '';
            document.getElementById('edit_product_current_gambar').value = product.gambar || '';
            document.getElementById('edit_product_try').value = product.try || '';
            
            // Show current image preview if exists
            const previewDiv = document.getElementById('edit_product_image_preview');
            const previewImg = document.getElementById('edit_product_image_preview_img');
            if (product.gambar) {
                previewImg.src = product.gambar;
                previewDiv.style.display = 'block';
            } else {
                previewDiv.style.display = 'none';
            }

            document.querySelector('#product-tab .form-section:not(.edit-form-section)').style.display = 'none';
            document.getElementById('edit-product-section').style.display = 'block';
            document.getElementById('edit-product-section').classList.add('active');
            document.getElementById('edit-product-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditProduct() {
            document.querySelector('#product-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-product-section').style.display = 'none';
            document.getElementById('edit-product-section').classList.remove('active');
            document.getElementById('edit-product-form').reset();
        }

        // Character counter function
        function updateCharCounter(textareaId, counterId) {
            const textarea = document.getElementById(textareaId);
            const counter = document.getElementById(counterId);
            if (textarea && counter) {
                const length = textarea.value.length;
                counter.textContent = length.toLocaleString();
            }
        }

        // Initialize character counters
        document.addEventListener('DOMContentLoaded', function() {
            // Edit form counter
            const editDetailTextarea = document.getElementById('edit_research_detail_detail');
            if (editDetailTextarea) {
                editDetailTextarea.addEventListener('input', function() {
                    updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
                });
                // Initial count
                updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
            }

            // Add form counter
            const addDetailTextarea = document.getElementById('add_research_detail_detail');
            if (addDetailTextarea) {
                addDetailTextarea.addEventListener('input', function() {
                    updateCharCounter('add_research_detail_detail', 'add_detail_count');
                });
                // Initial count
                updateCharCounter('add_research_detail_detail', 'add_detail_count');
            }
        });
    </script>
</body>

</html>