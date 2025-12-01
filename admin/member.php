<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_member') {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        $foto = $_POST['foto'] ?? ''; // URL input

        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['foto_file'], 'members/');
            if ($uploadResult['success']) {
                $foto = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                $alamat = $_POST['alamat'] ?? '';
                $no_tlp = $_POST['no_tlp'] ?? '';
                $deskripsi = $_POST['deskripsi'] ?? '';
                $bidang_keahlian = $_POST['bidang_keahlian'] ?? '';
                $admin_id = $_SESSION['id_admin'] ?? null;

                $stmt = $conn->prepare("INSERT INTO member (nama, email, jabatan, foto, bidang_keahlian, alamat, notlp, deskripsi) VALUES (:nama, :email, :jabatan, :foto, :keahlian, :alamat, :notlp, :deskripsi)");
                $stmt->execute([
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'jabatan' => $jabatan ?: null,
                    'foto' => $foto ?: null,
                    'keahlian' => $bidang_keahlian ?: null,
                    'alamat' => $alamat ?: null,
                    'notlp' => $no_tlp ?: null,
                    'deskripsi' => $deskripsi ?: null
                ]);

                $message = 'Member successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_member') {
        $id = $_POST['id'] ?? 0;
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        $foto = $_POST['foto'] ?? '';

        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['foto_file'], 'members/');
            if ($uploadResult['success']) {
                $foto = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                $alamat = $_POST['alamat'] ?? '';
                $no_tlp = $_POST['no_tlp'] ?? '';
                $deskripsi = $_POST['deskripsi'] ?? '';

                $stmt = $conn->prepare("UPDATE member SET nama = :nama, email = :email, jabatan = :jabatan, foto = :foto, alamat = :alamat, notlp = :notlp, deskripsi = :deskripsi WHERE id_member = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'jabatan' => $jabatan ?: null,
                    'foto' => $foto ?: null,
                    'alamat' => $alamat ?: null,
                    'notlp' => $no_tlp ?: null,
                    'deskripsi' => $deskripsi ?: null
                ]);

                $message = 'Member successfully updated!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_member') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM member WHERE id_member = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Member successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM member");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get members with pagination (all fields are already in member table)
$stmt = $conn->prepare("SELECT * 
                      FROM member 
                      ORDER BY nama
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - CMS InLET</title>
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
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
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

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: var(--light);
            color: var(--primary);
            font-weight: 600;
        }

        tr:hover {
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
        }

        .btn-delete:hover {
            background: #dc2626;
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
        }

        .btn-edit:hover {
            background: #2563eb;
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
    </style>
</head>

<body>
    <?php $active_page = 'member';
    include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-team-line"></i> Manage Members</h1>

            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Form Section (Hidden by default) -->
                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit Member</h2>
                    <form method="POST" action="" enctype="multipart/form-data" id="edit-member-form">
                        <input type="hidden" name="action" value="update_member">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="nama" id="edit_nama" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email">
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="jabatan" id="edit_jabatan">
                        </div>
                        <div class="form-group">
                            <label>Area of Expertise</label>
                            <input type="text" name="bidang_keahlian" id="edit_bidang_keahlian"
                                placeholder="Area of expertise (optional)">
                        </div>
                        <div class="form-group">
                            <label>Upload Foto (File)</label>
                            <input type="file" name="foto_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maksimal 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Photo URL</label>
                            <input type="text" name="foto" id="edit_foto" placeholder="https://example.com/foto.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <h3 class="text-primary mt-4 mb-3">Detailed Profile (Optional)</h3>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="alamat" id="edit_alamat"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="no_tlp" id="edit_no_tlp">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="deskripsi" id="edit_deskripsi"></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Update Member</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section">
                    <h2>Add New Member</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_member">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="jabatan">
                        </div>
                        <div class="form-group">
                            <label>Area of Expertise</label>
                            <input type="text" name="bidang_keahlian" placeholder="Area of expertise (optional)">
                        </div>
                        <div class="form-group">
                            <label>Upload Photo (File)</label>
                            <input type="file" name="foto_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Photo URL</label>
                            <input type="text" name="foto" placeholder="https://example.com/foto.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <h3 class="text-primary mt-4 mb-3">Detailed Profile (Optional)</h3>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="alamat"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="no_tlp">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="deskripsi"></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Add Member</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>Member List (<?php echo count($members); ?>)</h2>
                    <?php if (empty($members)): ?>
                        <p class="text-center p-4 muted-gray">No members yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Position</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?php echo $member['id_member']; ?></td>
                                            <td><?php echo htmlspecialchars($member['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['jabatan'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['notlp'] ?? '-'); ?></td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this member?');">
                                                    <input type="hidden" name="action" value="delete_member">
                                                    <input type="hidden" name="id" value="<?php echo $member['id_member']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a>
                            <?php else: ?>
                                <span class="disabled">&laquo; Previous</span>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1): ?>
                                <a href="?page=1">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span>...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span>...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                            <?php else: ?>
                                <span class="disabled">Next &raquo;</span>
                            <?php endif; ?>
                        </div>
                        <div class="pagination-info">
                            Showing <?php echo ($offset + 1); ?> -
                            <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?>
                            members
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </main>

    <script>
        function editMember(member) {
            // Populate edit form
            document.getElementById('edit_id').value = member.id_member;
            document.getElementById('edit_nama').value = member.nama || '';
            document.getElementById('edit_email').value = member.email || '';
            document.getElementById('edit_jabatan').value = member.jabatan || '';
            document.getElementById('edit_bidang_keahlian').value = member.bidang_keahlian || '';
            document.getElementById('edit_foto').value = member.foto || '';
            document.getElementById('edit_alamat').value = member.alamat || '';
            document.getElementById('edit_no_tlp').value = member.notlp || '';
            document.getElementById('edit_deskripsi').value = member.deskripsi || '';

            // Show edit form, hide add form
            document.getElementById('edit-form-section').classList.add('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'none';

            // Scroll to edit form
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            // Hide edit form, show add form
            document.getElementById('edit-form-section').classList.remove('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'block';

            // Reset edit form
            document.getElementById('edit-member-form').reset();
        }
    </script>
</body>

</html>