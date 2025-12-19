# PERSON 6: Full-Stack Developer - Integration & File Management

## 📋 **TUGAS UTAMA**

### **1. File Upload System**
- Upload handler dengan validation (`config/upload.php`)
- Image processing & validation
- File storage management
- Error handling untuk upload
- Multiple file upload support

### **2. Configuration & Settings**
- Settings management (`config/settings.php`)
- Site configuration
- Dynamic logo & title management
- Environment configuration

### **3. Integration & Testing**
- Integration testing antar modul
- Cross-browser compatibility testing
- Performance optimization
- Bug fixing & debugging
- Code review & quality assurance

### **4. Documentation**
- Code documentation
- API documentation
- User manual (jika diperlukan)
- Setup instructions
- Deployment guide

### **5. Setup Scripts**
- Admin creation (`setup/create_admin.php`)
- Database views (`setup/create_views.php`)
- Reset utilities (`setup/reset_admin.php`)
- Database migration scripts

---

## 📁 **FILE YANG DIKERJAKAN**

1. `config/upload.php` - File upload helper functions
2. `config/settings.php` - Settings helper functions
3. `setup/create_admin.php` - Admin user creation script
4. `setup/create_views.php` - Database views creation script
5. `setup/create_view_alat_dipinjam.php` - Specific view creation
6. `setup/reset_admin.php` - Admin reset utility
7. `test_db.php` - Database connection testing
8. `test_joins.php` - JOIN query testing
9. `test_pgsql.php` - PostgreSQL specific testing
10. Documentation files (README, setup guides, dll)

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. config/upload.php**

#### **Bagian 1: Constants & Configuration**

```php
<?php
/**
 * Upload Helper Functions
 */

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
```

**Penjelasan:**
- Define constants untuk upload directory
- Allowed file types untuk security
- Maximum file size 5MB
- Centralized configuration

#### **Bagian 2: Upload Image Function**

```php
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
```

**Penjelasan:**
- Validasi file upload error
- Check file size sebelum upload
- MIME type validation menggunakan finfo
- Security: hanya allow image types tertentu

#### **Bagian 3: Create Directory & Move File**

```php
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
- Auto-create directory jika tidak ada
- Generate unique filename dengan uniqid
- Preserve original extension
- Return relative path untuk database storage
- Error handling yang baik

#### **Bagian 4: Delete Uploaded File**

```php
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
- Cleanup file saat delete record
- Handle path dengan atau tanpa prefix
- Safe delete dengan file_exists check

---

### **2. config/settings.php**

#### **Bagian 1: Get Settings dengan Caching**

```php
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->query("SELECT * FROM settings ORDER BY id_setting LIMIT 1");
            $settings = $stmt->fetch();
            
            // If no settings found, initialize as empty array
            if ($settings === false) {
                $settings = [];
            }
            
            // Parse page_titles JSON if exists
            if (!empty($settings['page_titles'])) {
                if (is_string($settings['page_titles'])) {
                    $settings['page_titles'] = json_decode($settings['page_titles'], true) ?: [];
                }
            } else {
                $settings['page_titles'] = [];
            }
        } catch (PDOException $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            $settings = [];
        }
    }
    
    return $settings;
}
```

**Penjelasan:**
- **Singleton pattern**: Static variable untuk cache
- Fetch sekali, reuse berkali-kali
- Parse JSON untuk page_titles
- Error handling dengan fallback
- Performance optimization

#### **Bagian 2: Get Page Title Function**

```php
function getPageTitle($page_name) {
    $settings = getSettings();
    
    // Check if settings is empty array
    if (empty($settings)) {
        // Default fallback
        return [
            'title' => 'InLET - Information And Learning Engineering Technology',
            'subtitle' => 'State Polytechnic of Malang'
        ];
    }
    
    $page_titles = $settings['page_titles'] ?? [];
    
    if (isset($page_titles[$page_name])) {
        return [
            'title' => $page_titles[$page_name]['title'] ?? '',
            'subtitle' => $page_titles[$page_name]['subtitle'] ?? ''
        ];
    }
    
    // Fallback to default
    return [
        'title' => $settings['site_title'] ?? 'InLET - Information And Learning Engineering Technology',
        'subtitle' => $settings['site_subtitle'] ?? 'State Polytechnic of Malang'
    ];
}
```

**Penjelasan:**
- Dynamic page title dari database
- Support multiple pages dengan JSON
- Fallback ke default jika tidak ada
- Flexible configuration

#### **Bagian 3: Logo Functions**

```php
function getSiteLogo() {
    $settings = getSettings();
    if (!empty($settings['site_logo'])) {
        return $settings['site_logo'];
    }
    return 'assets/logo.png'; // Default fallback
}

function getFooterLogo() {
    $settings = getSettings();
    if (!empty($settings['footer_logo'])) {
        return $settings['footer_logo'];
    }
    return 'assets/logoPutih.png'; // Default fallback
}
```

**Penjelasan:**
- Dynamic logo dari database
- Separate function untuk header dan footer
- Default fallback jika tidak ada

---

### **3. setup/create_admin.php**

#### **Bagian 1: Setup & Configuration**

```php
<?php
/**
 * Setup Script - Create Admin User
 * 
 * Jalankan script ini sekali untuk membuat admin user pertama
 * Setelah admin dibuat, hapus atau rename file ini untuk keamanan
 */

require_once '../config/database.php';

$conn = getDBConnection();
// Default admin credentials (ubah setelah setup!)
$admin_username = 'admin';
$admin_password = 'admin123'; // Ganti dengan password yang kuat!
$admin_nama = 'Administrator';
$admin_email = 'admin@inlet.edu';
```

**Penjelasan:**
- Setup script untuk create admin pertama
- Default credentials (harus diganti!)
- Security warning di comment

#### **Bagian 2: Check Existing Admin**

```php
try {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE username = :username");
    $stmt->execute(['username' => $admin_username]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Admin user sudah ada! Username: $admin_username<br>";
        echo "<a href='../login.php'>Login di sini</a>";
        exit();
    }
```

**Penjelasan:**
- Prevent duplicate admin creation
- Check sebelum insert
- User-friendly message

#### **Bagian 3: Create Admin dengan Password Hashing**

```php
    // Hash password
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

    // Create user
    $stmt = $conn->prepare("INSERT INTO admin (username, password_hash, role) VALUES (:username, :password_hash, 'admin') RETURNING id_user");
    $stmt->execute([
        'username' => $admin_username,
        'password_hash' => $password_hash
    ]);
    $user_id = $stmt->fetchColumn();

    // Create admin profile
    $stmt = $conn->prepare("INSERT INTO admin (id_admin, nama, email) VALUES (:id_admin, :nama, :email)");
    $stmt->execute([
        'id_user' => $user_id,
        'nama' => $admin_nama,
        'email' => $admin_email
    ]);

    echo "<h2>Admin successfully created!</h2>";
    echo "<p><strong>Username:</strong> $admin_username</p>";
    echo "<p><strong>Password:</strong> $admin_password</p>";
    echo "<p style='color: red;'><strong>PENTING:</strong> Ganti password setelah login pertama kali!</p>";
    echo "<p><a href='../login.php'>Login di sini</a></p>";
    echo "<p style='color: orange;'><strong>PERINGATAN:</strong> Hapus atau rename file ini setelah setup selesai!</p>";
```

**Penjelasan:**
- Password hashing dengan PASSWORD_DEFAULT
- Secure password storage
- RETURNING untuk get ID
- Security warnings untuk user
- Clear instructions

---

### **4. setup/create_views.php**

#### **Bagian 1: View Creation Script**

```php
<?php
/**
 * Script untuk membuat views yang diperlukan di database
 * Jalankan script ini sekali untuk membuat views jika belum ada
 */

require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Membuat Views Database</h2>";
echo "<hr>";
```

**Penjelasan:**
- Setup script untuk create views
- One-time execution
- User-friendly output

#### **Bagian 2: Create view_alat_dipinjam**

```php
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
                ON alat.id_alat = pj.id_alat
            WHERE pj.status = 'dipinjam'";

    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_dipinjam' successfully created!</p>";
} catch (PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_dipinjam': " . $e->getMessage() . "</p>";
}
```

**Penjelasan:**
- CREATE OR REPLACE untuk update view
- JOIN untuk combine data
- Error handling dengan try-catch
- Visual feedback untuk user

#### **Bagian 3: Create view_alat_tersedia**

```php
// View untuk melihat alat yang tersedia dengan informasi stok
try {
    $sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
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

    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_tersedia' successfully created!</p>";
} catch (PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_tersedia': " . $e->getMessage() . "</p>";
}
```

**Penjelasan:**
- Complex view dengan subquery
- Calculate stok tersedia
- COALESCE untuk handle NULL
- Aggregate function untuk count

---

### **5. setup/create_view_alat_dipinjam.php**

#### **Bagian 1: Specific View Creation**

```php
<?php
/**
 * Script untuk membuat view view_alat_dipinjam
 * Jalankan script ini untuk membuat view jika belum ada
 */

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

echo "<h2>Membuat View view_alat_dipinjam</h2>";
echo "<hr>";
```

**Penjelasan:**
- Specific view creation script
- Standalone script untuk one view
- Clear purpose

#### **Bagian 2: Create & Verify View**

```php
try {
    // View untuk melihat alat yang sedang dipinjam
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
                ON alat.id_alat = pj.id_alat
            WHERE pj.status = 'dipinjam'";

    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_dipinjam' successfully created!</p>";

    // Verify the view was created
    $stmt = $conn->query("SELECT COUNT(*) FROM information_schema.views WHERE table_name = 'view_alat_dipinjam' AND table_schema = 'public'");
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        echo "<p style='color: green;'>✅ View successfully verified in database.</p>";

        // Test query
        $test_stmt = $conn->query("SELECT COUNT(*) FROM view_alat_dipinjam");
        $count = $test_stmt->fetchColumn();
        echo "<p>Jumlah data di view: <strong>" . $count . "</strong> alat yang sedang dipinjam.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
```

**Penjelasan:**
- Create view dengan SQL
- Verify view creation dengan information_schema
- Test query untuk verify functionality
- Show data count untuk confirmation
- Comprehensive verification

---

### **6. test_db.php**

#### **Bagian 1: Database Connection Test**

```php
<?php
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";
echo "<hr>";

try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test query
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p><strong>PostgreSQL Version:</strong> " . htmlspecialchars($version) . "</p>";
    
    // List tables
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables in database:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
```

**Penjelasan:**
- Test database connection
- Show PostgreSQL version
- List all tables
- Error handling
- Useful untuk debugging

---

### **7. test_joins.php**

#### **Bagian 1: JOIN Query Testing**

```php
<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "Testing JOIN fixes in service/peminjaman.php\n\n";

// Test 1: Check if views can be created without errors
try {
    echo "1. Testing view creation...\n";

    // View to see tools currently being borrowed
    $view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
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
            pj.created_at,
            pj.id_ruang
        FROM peminjaman pj
        LEFT JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
    echo "✓ view_alat_dipinjam created successfully\n";
```

**Penjelasan:**
- Test script untuk verify JOIN queries
- Create views untuk testing
- Verify view creation
- Useful untuk debugging JOIN issues

---

## 🔑 **FITUR UTAMA**

### **1. File Upload System**
- ✅ **MIME Type Validation**: Menggunakan finfo untuk verify file type
- ✅ **File Size Check**: Maximum 5MB
- ✅ **Unique Filename**: uniqid untuk prevent conflicts
- ✅ **Directory Auto-Create**: Auto create upload directory
- ✅ **File Deletion**: Cleanup function untuk delete files

### **2. Settings Management**
- ✅ **Caching**: Singleton pattern untuk performance
- ✅ **JSON Parsing**: Parse page_titles dari JSON
- ✅ **Dynamic Configuration**: Logo, title, subtitle dari database
- ✅ **Fallback Values**: Default values jika tidak ada

### **3. Setup Scripts**
- ✅ **Admin Creation**: One-time admin user creation
- ✅ **View Creation**: Database views setup
- ✅ **Verification**: Verify creation success
- ✅ **Security Warnings**: Remind user untuk security

### **4. Testing Scripts**
- ✅ **Database Test**: Connection & table listing
- ✅ **JOIN Test**: Verify JOIN queries
- ✅ **PostgreSQL Test**: Version & compatibility

---

## 🎯 **HIGHLIGHTS**

1. **Security First**: File type validation, password hashing, prepared statements
2. **Performance**: Caching untuk settings, singleton pattern
3. **Error Handling**: Comprehensive try-catch dengan fallback
4. **User-Friendly**: Clear messages, verification, instructions
5. **Maintainability**: Well-documented, modular functions
6. **Testing Tools**: Scripts untuk verify functionality
7. **Flexibility**: Dynamic configuration, fallback values

---

## 📝 **CATATAN PENTING**

1. **Upload Security**: 
   - MIME type validation penting untuk security
   - File size limit prevent DoS
   - Unique filename prevent overwrite

2. **Settings Caching**:
   - Static variable untuk cache
   - Fetch sekali, reuse berkali-kali
   - Performance optimization

3. **Setup Scripts**:
   - One-time execution
   - Security warnings penting
   - Verification untuk confirm success

4. **Testing**:
   - Test scripts untuk debugging
   - Verify database structure
   - Check compatibility

---

## ✅ **DELIVERABLES**

- ✅ Robust file upload system dengan validation
- ✅ Complete configuration management
- ✅ Well-integrated system
- ✅ Complete documentation
- ✅ Setup scripts working
- ✅ Testing tools untuk debugging
- ✅ Security best practices implemented

---

## 📚 **DOCUMENTATION CREATED**

1. **PENERAPAN_VIEW_DAN_PROCEDURE.md**: Dokumentasi penggunaan view dan procedure
2. **Setup Instructions**: Guide untuk setup database dan admin
3. **Code Comments**: Inline documentation di semua file
4. **API Documentation**: Service layer endpoints documentation

---

**Selesai!** 🎉

