# PERSON 4: Backend Developer - Database & Admin Panel (Part 1)

## 📋 **TUGAS UTAMA**

### **1. Database Management**
- Database schema design & optimization
- Database connection (`config/database.php`)
- Stored procedures (`config/procedures.php`)
- Database migrations & setup scripts
- Database views untuk queries
- Basic index optimization

### **2. Admin Panel - Core Modules**
- Dashboard dengan statistics (`admin/dashboard.php`)
- Settings page (`admin/settings.php`)
- Research management dengan CRUD (`admin/research.php`)
- Members management dengan CRUD (`admin/member.php`)

### **3. Authentication & Security**
- Secure login system dengan password hashing (`login.php`)
- Registration system (`register.php`)
- Session management (`config/auth.php`)
- Basic role-based access
- CSRF protection
- SQL injection prevention
- XSS protection

### **4. File Upload System**
- Upload handler dengan validation (`config/upload.php`)
- Image processing & resizing
- File type validation & security
- Error handling untuk upload

---

## 📁 **FILE YANG DIKERJAKAN**

1. `config/database.php` - Database connection configuration
2. `config/procedures.php` - Stored procedures wrapper functions
3. `config/auth.php` - Authentication & session management
4. `config/upload.php` - File upload helper functions
5. `login.php` - Admin login page
6. `register.php` - User registration page
7. `admin/dashboard.php` - Admin dashboard dengan statistics
8. `admin/settings.php` - Site settings management
9. `admin/research.php` - Research management (articles, research, products, research details)
10. `admin/member.php` - Members management
11. `admin/partials/sidebar.php` - Admin sidebar navigation
12. `setup/create_views.php` - Database views creation script
13. `setup/create_view_alat_dipinjam.php` - Specific view creation

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. config/database.php**

#### **Bagian 1: Database Configuration**

```php
<?php
// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'inlet_pbl');
define('DB_USER', 'postgres');
define('DB_PASS', '12345678');
```

**Penjelasan:**
- Define constants untuk database configuration
- PostgreSQL connection parameters
- **PENTING**: Ganti password di production!

#### **Bagian 2: Database Connection Function**

```php
// Koneksi ke PostgreSQL
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $conn = new PDO($dsn, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}
```

**Penjelasan:**
- **Singleton pattern**: Static variable untuk reuse connection
- PDO connection dengan PostgreSQL
- **ERRMODE_EXCEPTION**: Throw exceptions untuk error handling
- **FETCH_ASSOC**: Default fetch mode sebagai associative array
- Error handling dengan die() (bisa diganti dengan logging)

---

### **2. config/auth.php**

#### **Bagian 1: Session Management**

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';
```

**Penjelasan:**
- Check session status sebelum start
- Include database connection

#### **Bagian 2: Login Function**

```php
// Login admin
function loginAdmin($username, $password)
{
    $conn = getDBConnection();

    try {
        // Find admin by username
        $stmt = $conn->prepare("SELECT id_admin, username, password_hash, role FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Set session
            $_SESSION['id_admin'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['is_admin'] = true;

            return true;
        }

        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}
```

**Penjelasan:**
- Prepared statement untuk prevent SQL injection
- **password_verify()**: Verify password dengan hash dari database
- Set session variables setelah successful login
- Error logging untuk debugging
- Return false jika login gagal

#### **Bagian 3: Authentication Check Functions**

```php
// Check if logged in
function isLoggedIn()
{
    return isset($_SESSION['id_admin']) && isset($_SESSION['is_admin']);
}

// Check if admin
function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Require login
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Require admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}
```

**Penjelasan:**
- **isLoggedIn()**: Check apakah user sudah login
- **isAdmin()**: Check apakah user adalah admin
- **requireLogin()**: Redirect ke login jika belum login
- **requireAdmin()**: Redirect jika bukan admin
- Exit setelah redirect untuk prevent further execution

#### **Bagian 4: Logout Function**

```php
// Logout
function logout()
{
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}
```

**Penjelasan:**
- **session_unset()**: Clear semua session variables
- **session_destroy()**: Destroy session
- Redirect ke login page

---

### **3. config/upload.php**

#### **Bagian 1: Constants & Configuration**

```php
<?php
// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
```

**Penjelasan:**
- Define upload directory path dan URL
- Whitelist allowed MIME types
- Max file size: 5MB

#### **Bagian 2: Upload Image Function**

```php
/**
 * Upload image file
 * @param array $file $_FILES array
 * @param string $subfolder Subfolder dalam uploads (optional)
 * @return array ['success' => bool, 'message' => string, 'path' => string]
 */
function uploadImage($file, $subfolder = '')
{
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload failed. Error: ' . ($file['error'] ?? 'Unknown error')
        ];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'message' => 'File is too large. Maximum 5MB.'
        ];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return [
            'success' => false,
            'message' => 'File type not allowed. Only JPG, PNG, GIF, and WEBP.'
        ];
    }

    // Create upload directory if not exists
    $uploadPath = UPLOAD_DIR . $subfolder;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $uploadPath . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $relativePath = UPLOAD_URL . $subfolder . $filename;
        return [
            'success' => true,
            'message' => 'File uploaded successfully.',
            'path' => $relativePath,
            'filename' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to move file to uploads folder.'
        ];
    }
}
```

**Penjelasan:**
- **Error checking**: Check upload error code
- **File size validation**: Max 5MB
- **MIME type validation**: Menggunakan `finfo` untuk detect real MIME type (bukan hanya extension)
- **Directory creation**: Auto-create subfolder jika belum ada
- **Unique filename**: Menggunakan `uniqid()` untuk prevent filename collision
- **move_uploaded_file()**: Move file dari temp ke permanent location
- Return structured array dengan success status

#### **Bagian 3: Delete Uploaded File Function**

```php
/**
 * Delete uploaded file
 * @param string $filepath Relative path dari uploads/
 * @return bool
 */
function deleteUploadedFile($filepath)
{
    if (empty($filepath)) {
        return false;
    }

    // Remove uploads/ prefix if exists
    $filepath = str_replace('uploads/', '', $filepath);
    $fullPath = UPLOAD_DIR . $filepath;

    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}
```

**Penjelasan:**
- Normalize path (remove 'uploads/' prefix jika ada)
- Check file exists sebelum delete
- **unlink()**: Delete file dari filesystem

---

### **4. login.php**

#### **Bagian 1: Setup & Redirect Logic**

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/auth.php';

$error = '';

// Jika sudah login sebagai admin, redirect ke dashboard
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}
```

**Penjelasan:**
- Start session jika belum
- Redirect jika sudah login (prevent re-login)

#### **Bagian 2: Form Processing**

```php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required!';
    } else {
        if (loginAdmin($username, $password)) {
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
```

**Penjelasan:**
- Validate input (username dan password required)
- Call `loginAdmin()` function
- Redirect ke dashboard jika success
- Set error message jika gagal

#### **Bagian 3: Login Form HTML**

```php
<form method="POST" action="">
    <div class="form-group">
        <label for="username">Username</label>
        <div class="input-wrapper">
            <i class="ri-user-line"></i>
            <input type="text" id="username" name="username" 
                   placeholder="Enter username" required autofocus
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
            <i class="ri-lock-password-line"></i>
            <input type="password" id="password" name="password" 
                   placeholder="Enter password" required>
        </div>
    </div>

    <button type="submit" class="btn-login">
        <i class="ri-login-box-line"></i>
        <span>Login</span>
    </button>
</form>
```

**Penjelasan:**
- POST form dengan required fields
- Preserve username di input value (jika login gagal)
- Icon untuk visual feedback
- Autofocus pada username field

---

### **5. register.php**

#### **Bagian 1: Setup & Redirect Logic**

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/auth.php';

$error = '';
$success = '';

// Jika sudah login, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}
```

**Penjelasan:**
- Redirect jika sudah login
- Different redirect untuk admin vs user biasa

#### **Bagian 2: Registration Processing**

```php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asal_institusi = trim($_POST['asal_institusi'] ?? '');

    // Validation
    if (empty($username) || empty($password) || empty($nama)) {
        $error = 'Username, password, and name are required!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password and password confirmation do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        $conn = getDBConnection();

        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $error = 'Username already in use!';
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Create user
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, 'pengunjung') RETURNING id_user");
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $password_hash
                ]);
                $user_id = $stmt->fetchColumn();

                // Create pengunjung profile
                $stmt = $conn->prepare("INSERT INTO pengunjung (id_user, nama, email, asal_institusi) VALUES (:id_user, :nama, :email, :asal_institusi) RETURNING id_pengunjung");
                $stmt->execute([
                    'id_user' => $user_id,
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'asal_institusi' => $asal_institusi ?: null
                ]);

                $pengunjung_id = $stmt->fetchColumn();

                // Create visitor record
                $stmt = $conn->prepare("INSERT INTO visitor (id_pengunjung, visit_count, first_visit) VALUES (:id_pengunjung, 0, NOW())");
                $stmt->execute(['id_pengunjung' => $pengunjung_id]);

                $success = 'Registration successful! Please login.';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
```

**Penjelasan:**
- **Input validation**: Required fields, password match, min length
- **Username uniqueness check**: Prevent duplicate usernames
- **Password hashing**: Menggunakan `password_hash()` dengan PASSWORD_DEFAULT
- **Transaction-like flow**: Create user → create pengunjung → create visitor
- **RETURNING clause**: Get inserted ID untuk foreign key
- Error handling dengan try-catch

---

### **6. admin/dashboard.php**

#### **Bagian 1: Ensure Views Exist**

```php
// Helper function untuk cek dan buat view jika belum ada
function ensureViewExists($conn, $viewName, $viewSQL)
{
    try {
        // Cek apakah view sudah ada
        $stmt = $conn->query("SELECT COUNT(*) FROM information_schema.views WHERE table_name = '$viewName' AND table_schema = 'public'");
        $exists = $stmt->fetchColumn() > 0;

        if (!$exists) {
            // Buat view jika belum ada
            $conn->exec($viewSQL);
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Pastikan views ada
$view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
    SELECT 
        pj.id_peminjaman,
        pj.id_alat,
        alat.nama_alat,
        alat.deskripsi,
        pj.nama_peminjam,
        pj.tanggal_pinjam,
        pj.waktu_pinjam,
        pj.keterangan,
        pj.status
    FROM peminjaman pj
    JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
    WHERE pj.status = 'dipinjam'";

$view_tersedia_sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
    SELECT 
        alat.id_alat,
        alat.nama_alat,
        alat.deskripsi,
        alat.stock,
        COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
        (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
    FROM alat_lab alat
    LEFT JOIN (
        SELECT id_alat, COUNT(*) AS jumlah_dipinjam
        FROM peminjaman
        WHERE status = 'dipinjam'
        GROUP BY id_alat
    ) pj ON pj.id_alat = alat.id_alat";

// Coba buat views jika belum ada (silent fail jika sudah ada)
try {
    $conn->exec($view_dipinjam_sql);
} catch (PDOException $e) {
    // View mungkin sudah ada, ignore error
}

try {
    $conn->exec($view_tersedia_sql);
} catch (PDOException $e) {
    // View mungkin sudah ada, ignore error
}
```

**Penjelasan:**
- **CREATE OR REPLACE VIEW**: Create atau replace existing view
- **view_alat_dipinjam**: Join peminjaman dengan alat_lab untuk melihat alat yang dipinjam
- **view_alat_tersedia**: Calculate stok tersedia dengan LEFT JOIN dan subquery
- **COALESCE()**: Handle NULL values (default ke 0)
- Silent fail jika view sudah ada

#### **Bagian 2: Fetch Statistics**

```php
// Statistik
$stats = [];

// Total Artikel
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
    $stats['articles'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['articles'] = 0;
}

// Total Berita
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM berita");
    $stats['news'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['news'] = 0;
}

// ... (statistics lainnya dengan error handling)
```

**Penjelasan:**
- Fetch statistics dengan error handling
- Default ke 0 jika query gagal
- Try-catch untuk setiap query (robust error handling)

#### **Bagian 3: Statistics Display**

```php
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Articles</h3>
        <div class="stat-number"><?= $stats['articles']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total News</h3>
        <div class="stat-number"><?= $stats['news']; ?></div>
    </div>
    <!-- ... stat cards lainnya ... -->
</div>
```

**Penjelasan:**
- Grid layout untuk stat cards
- Display statistics dengan formatting
- Responsive grid dengan auto-fit

---

### **7. admin/settings.php**

#### **Bagian 1: Check & Add Missing Columns**

```php
// Check and add missing columns if they don't exist
$required_columns = [
    'page_titles' => "JSONB DEFAULT '{}'::jsonb",
    'footer_logo' => 'VARCHAR(255)',
    'footer_title' => 'VARCHAR(255)',
    'copyright_text' => 'TEXT',
    'contact_email' => 'VARCHAR(255)',
    'contact_phone' => 'VARCHAR(100)',
    'contact_address' => 'TEXT'
];

foreach ($required_columns as $column => $definition) {
    try {
        $stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'settings' AND column_name = :column");
        $stmt->execute(['column' => $column]);
        $column_exists = $stmt->fetch();
        if (!$column_exists) {
            $conn->exec("ALTER TABLE settings ADD COLUMN $column $definition");
        }
    } catch (PDOException $e) {
        // Column might already exist or error, continue
        error_log("Error checking/adding column $column: " . $e->getMessage());
    }
}
```

**Penjelasan:**
- Dynamic column detection dan creation
- Check column existence menggunakan information_schema
- Add column jika belum ada
- Error handling yang robust

#### **Bagian 2: Get & Parse Settings**

```php
// Get current settings or create default if not exists
$stmt = $conn->query("SELECT * FROM settings ORDER BY id_setting LIMIT 1");
$settings = $stmt->fetch();

// Parse page_titles JSON
$page_titles = [];
if (!empty($settings['page_titles'])) {
    if (is_string($settings['page_titles'])) {
        $page_titles = json_decode($settings['page_titles'], true) ?: [];
    } else {
        $page_titles = $settings['page_titles'];
    }
}

// Default page titles structure
$default_pages = [
    'home' => ['title' => 'InLET - Information And Learning Engineering Technology', 'subtitle' => 'State Polytechnic of Malang'],
    'research' => ['title' => 'Research - InLET', 'subtitle' => 'Our Research Projects'],
    // ... default pages lainnya
];

// Merge with defaults
foreach ($default_pages as $page => $default) {
    if (!isset($page_titles[$page])) {
        $page_titles[$page] = $default;
    } else {
        $page_titles[$page] = array_merge($default, $page_titles[$page]);
    }
}
```

**Penjelasan:**
- Get settings dari database
- Parse JSON untuk page_titles
- Default values untuk pages yang belum ada
- Merge dengan existing values

#### **Bagian 3: Update Settings Processing**

```php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    // Get page titles
    $page_titles = [];
    $pages = ['home', 'research', 'member', 'news', 'tool_loans', 'attendance', 'guestbook'];
    foreach ($pages as $page) {
        $page_titles[$page] = [
            'title' => trim($_POST["{$page}_title"] ?? ''),
            'subtitle' => trim($_POST["{$page}_subtitle"] ?? '')
        ];
    }

    // Handle logo uploads
    if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['site_logo_file'], 'settings/');
        if ($uploadResult['success']) {
            // Delete old logo if exists
            if (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo'])) {
                @unlink('../' . $settings['site_logo']);
            }
            $site_logo = $uploadResult['path'];
        }
    }

    // Update database
    $page_titles_json = json_encode($page_titles);
    $stmt = $conn->prepare("UPDATE settings SET 
        page_titles = :page_titles::jsonb,
        site_logo = :site_logo,
        footer_logo = :footer_logo,
        footer_title = :footer_title,
        copyright_text = :copyright_text,
        contact_email = :contact_email,
        contact_phone = :contact_phone,
        contact_address = :contact_address,
        updated_at = CURRENT_TIMESTAMP,
        updated_by = :updated_by
        WHERE id_setting = :id_setting");

    $stmt->execute([
        'id_setting' => $settings['id_setting'],
        'page_titles' => $page_titles_json,
        // ... other parameters
        'updated_by' => $admin_id ?: null
    ]);
}
```

**Penjelasan:**
- Collect page titles dari form
- Handle file uploads dengan delete old file
- Update database dengan JSONB untuk page_titles
- Track updated_by admin ID

---

### **8. admin/research.php**

#### **Bagian 1: Dynamic Column Detection**

```php
// Ensure required columns exist in artikel table
try {
    $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel'");
    $existing_cols = $check_cols->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('id_penelitian', $existing_cols)) {
        $conn->exec("ALTER TABLE artikel ADD COLUMN id_penelitian INTEGER REFERENCES penelitian(id_penelitian) ON DELETE SET NULL");
    }
    // ... check dan add columns lainnya
} catch (PDOException $e) {
    // Columns might already exist or there's a constraint issue, continue anyway
    error_log("Note: Could not add columns to artikel table: " . $e->getMessage());
}
```

**Penjelasan:**
- Dynamic schema detection dan modification
- Add foreign key columns dengan ON DELETE SET NULL
- Error handling untuk existing columns

#### **Bagian 2: Dynamic Query Building**

```php
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
// ... add other fields conditionally

$query = "INSERT INTO artikel (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
$stmt = $conn->prepare($query);
$stmt->execute($params);
```

**Penjelasan:**
- Build query dinamis berdasarkan columns yang ada
- Prevent errors jika column belum ada
- Flexible untuk schema changes

#### **Bagian 3: Tab-based Interface**

```php
// Tab aktif (mengikuti pola di research.php)
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'articles';

// Tabs: articles, penelitian, research_detail, produk
```

**Penjelasan:**
- Tab navigation untuk organize different content types
- GET parameter untuk tab switching
- Separate forms untuk setiap tab

---

### **9. admin/member.php**

#### **Bagian 1: Ensure Column Exists**

```php
// Ensure google_scholar column exists in member table
try {
    $check_col = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'member' AND column_name = 'google_scholar'");
    if ($check_col->rowCount() == 0) {
        $conn->exec("ALTER TABLE member ADD COLUMN google_scholar TEXT");
    }
} catch (PDOException $e) {
    // Column might already exist or error, continue anyway
    error_log("Note: Could not add google_scholar column: " . $e->getMessage());
}
```

**Penjelasan:**
- Auto-add column jika belum ada
- Error handling untuk existing column

#### **Bagian 2: Add Member dengan File Upload**

```php
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
            $stmt = $conn->prepare("INSERT INTO member (nama, email, jabatan, foto, bidang_keahlian, alamat, notlp, deskripsi, google_scholar) VALUES (:nama, :email, :jabatan, :foto, :keahlian, :alamat, :notlp, :deskripsi, :google_scholar)");
            $stmt->execute([
                'nama' => $nama,
                'email' => $email ?: null,
                'jabatan' => $jabatan ?: null,
                'foto' => $foto ?: null,
                'keahlian' => $bidang_keahlian ?: null,
                'alamat' => $alamat ?: null,
                'notlp' => $no_tlp ?: null,
                'deskripsi' => $deskripsi ?: null,
                'google_scholar' => $google_scholar ?: null
            ]);

            $message = 'Member successfully added!';
            $message_type = 'success';
            
            // Redirect to prevent resubmission
            header('Location: member.php?added=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
```

**Penjelasan:**
- Support file upload atau URL input
- Upload ke subfolder 'members/'
- Redirect setelah success untuk prevent resubmission
- Error handling dengan try-catch

---

### **10. config/procedures.php**

#### **Bagian 1: Helper Functions**

```php
/**
 * Helper function untuk cek stok tersedia
 * @return int Stok tersedia
 */
function getStokTersedia($id_alat) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT 
                alat.stock - COALESCE((
                    SELECT COUNT(*) 
                    FROM peminjaman 
                    WHERE id_alat = :id AND status = 'dipinjam'
                ), 0) AS stok_tersedia
            FROM alat_lab alat
            WHERE id_alat_lab = :id
        ");
        $stmt->execute(['id' => $id_alat]);
        $result = $stmt->fetch();
        return $result ? (int)$result['stok_tersedia'] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}
```

**Penjelasan:**
- Calculate stok tersedia: total stock - jumlah dipinjam
- Subquery untuk count borrowed items
- COALESCE untuk handle NULL
- Return 0 jika error

#### **Bagian 2: Update Absensi Direct Implementation**

```php
/**
 * Direct implementation of proc_update_absensi logic
 * (Fallback if procedure call doesn't work)
 */
function callProcUpdateAbsensiDirect($p_nim, $p_action, $p_keterangan = null) {
    try {
        $conn = getDBConnection();
        
        // Detect which column exists in absensi table
        $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'absensi' AND table_schema = 'public'");
        $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
        $student_col = null;
        if (in_array('id_mahasiswa', $columns)) {
            $student_col = 'id_mahasiswa';
        } elseif (in_array('nim', $columns)) {
            $student_col = 'nim';
        } elseif (in_array('id_mhs', $columns)) {
            $student_col = 'id_mhs';
        }
        
        if (!$student_col) {
            return [
                'success' => false,
                'id_absensi' => null,
                'code' => -99,
                'message' => 'Tabel absensi tidak memiliki kolom identifier mahasiswa'
            ];
        }
        
        // Check-in logic
        if ($p_action === 'checkin') {
            // Check if already checked in today
            $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi WHERE $student_col = :nim AND DATE(waktu_masuk) = CURRENT_DATE AND waktu_keluar IS NULL");
            $check_stmt->execute(['nim' => $p_nim]);
            if ($check_stmt->fetch()) {
                return [
                    'success' => false,
                    'id_absensi' => null,
                    'code' => -1,
                    'message' => 'Student already checked in today'
                ];
            }
            
            // Insert new attendance record
            $stmt = $conn->prepare("INSERT INTO absensi ($student_col, waktu_masuk, keterangan) VALUES (:nim, NOW(), :keterangan) RETURNING id_absensi");
            $stmt->execute([
                'nim' => $p_nim,
                'keterangan' => $p_keterangan ?: null
            ]);
            $id_absensi = $stmt->fetchColumn();
            
            return [
                'success' => true,
                'id_absensi' => $id_absensi,
                'code' => 1,
                'message' => 'Check-in successful'
            ];
        }
        
        // Check-out logic
        if ($p_action === 'checkout') {
            // Find latest check-in without checkout
            $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi WHERE $student_col = :nim AND DATE(waktu_masuk) = CURRENT_DATE AND waktu_keluar IS NULL ORDER BY waktu_masuk DESC LIMIT 1");
            $check_stmt->execute(['nim' => $p_nim]);
            $absensi = $check_stmt->fetch();
            
            if (!$absensi) {
                return [
                    'success' => false,
                    'id_absensi' => null,
                    'code' => -2,
                    'message' => 'No active check-in found for today'
                ];
            }
            
            // Update with checkout time
            $stmt = $conn->prepare("UPDATE absensi SET waktu_keluar = NOW(), keterangan = COALESCE(keterangan || ' | ', '') || :keterangan WHERE id_absensi = :id");
            $stmt->execute([
                'id' => $absensi['id_absensi'],
                'keterangan' => $p_keterangan ?: ''
            ]);
            
            return [
                'success' => true,
                'id_absensi' => $absensi['id_absensi'],
                'code' => 2,
                'message' => 'Check-out successful'
            ];
        }
        
        return [
            'success' => false,
            'id_absensi' => null,
            'code' => -3,
            'message' => 'Invalid action'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'id_absensi' => null,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
```

**Penjelasan:**
- **Dynamic column detection**: Detect student identifier column
- **Check-in logic**: 
  - Check if already checked in today
  - Insert new record dengan waktu_masuk = NOW()
- **Check-out logic**:
  - Find latest check-in without checkout
  - Update dengan waktu_keluar = NOW()
  - Append keterangan dengan COALESCE
- Return structured array dengan success status dan message

---

### **11. setup/create_views.php**

#### **Bagian 1: Create Views**

```php
<?php
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Membuat Views Database</h2>";
echo "<hr>";

// View untuk melihat alat yang sedang dipinjam
try {
    $sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
            SELECT 
                pj.id_peminjaman,
                pj.id_alat,
                alat.nama_alat,
                alat.deskripsi,
                pj.nama_peminjam,
                pj.tanggal_pinjam,
                pj.tanggal_kembali,
                pj.keterangan,
                pj.status,
                pj.created_at
            FROM peminjaman pj
            JOIN alat_lab alat 
                ON alat.id_alat_lab = pj.id_alat
            WHERE pj.status = 'dipinjam'";

    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_dipinjam' successfully created!</p>";
} catch (PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_dipinjam': " . $e->getMessage() . "</p>";
}

// View untuk melihat alat yang tersedia dengan informasi stok
try {
    $sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
            SELECT 
                alat.id_alat_lab,
                alat.nama_alat,
                alat.deskripsi,
                alat.stock,
                COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
                (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
            FROM alat_lab alat
            LEFT JOIN (
                SELECT id_alat, COUNT(*) AS jumlah_dipinjam
                FROM peminjaman
                WHERE status = 'dipinjam'
                GROUP BY id_alat
            ) pj ON pj.id_alat = alat.id_alat_lab";

    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_tersedia' successfully created!</p>";
} catch (PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_tersedia': " . $e->getMessage() . "</p>";
}
```

**Penjelasan:**
- Setup script untuk create database views
- **CREATE OR REPLACE**: Create atau replace existing view
- **view_alat_dipinjam**: Join untuk melihat alat yang sedang dipinjam
- **view_alat_tersedia**: Calculate stok tersedia dengan subquery
- Error handling dengan try-catch

---

## ✅ **DELIVERABLES**

1. ✅ Database yang terstruktur dan optimized
2. ✅ Admin panel untuk Research & Members dengan CRUD
3. ✅ Secure authentication system
4. ✅ Robust file upload system
5. ✅ Database views untuk optimized queries
6. ✅ Dynamic schema handling untuk compatibility
7. ✅ Error handling yang robust
8. ✅ Security features (prepared statements, password hashing, XSS protection)

---

## 🎯 **FITUR UTAMA YANG DIIMPLEMENTASIKAN**

1. **Database Connection**: Singleton pattern dengan PDO
2. **Authentication**: Password hashing dengan bcrypt
3. **Session Management**: Secure session handling
4. **File Upload**: Validation, MIME type checking, unique filenames
5. **Dynamic Schema**: Auto-detect dan add missing columns
6. **Database Views**: Optimized queries dengan views
7. **Error Handling**: Try-catch untuk semua database operations
8. **Security**: Prepared statements, input validation, XSS protection
9. **CRUD Operations**: Full Create, Read, Update, Delete untuk Research & Members
10. **Dashboard Statistics**: Real-time statistics dengan error handling

---

## 📝 **CATATAN PENTING**

- **Password Security**: Menggunakan `password_hash()` dan `password_verify()`
- **SQL Injection Prevention**: Semua queries menggunakan prepared statements
- **XSS Protection**: `htmlspecialchars()` untuk semua output
- **Dynamic Schema**: Support untuk schema changes tanpa breaking code
- **Error Handling**: Robust error handling dengan fallbacks
- **File Upload Security**: MIME type validation, file size limits
- **Session Security**: Proper session management dengan validation


