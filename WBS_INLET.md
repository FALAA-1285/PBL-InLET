# Work Breakdown Structure (WBS)
## InLET - Information and Learning Engineering Technology
## Lab Management System

---

## 1.0 PROJECT INITIATION & SETUP

### 1.1 System Configuration
- 1.1.1 Database Configuration (`config/database.php`)
  - PostgreSQL connection setup
  - Connection pooling
  - Error handling
- 1.1.2 Authentication System (`config/auth.php`)
  - Login functionality
  - Session management
  - Admin authorization
  - Logout functionality
- 1.1.3 Settings Management (`config/settings.php`)
  - Site configuration
  - Page titles management
  - Footer settings
  - Contact information
- 1.1.4 File Upload System (`config/upload.php`)
  - Image upload handler
  - File validation
  - Directory management
  - File deletion utility

### 1.2 Database Setup
- 1.2.1 Database Schema (`assets/inlet_pblrestores.sql`)
  - Table creation
  - Sequence setup
  - Foreign key constraints
  - Index creation
- 1.2.2 Database Procedures (`config/procedures.php`)
  - Stored procedure wrappers
  - Return peminjaman procedure
  - Reject request procedure
  - Update absensi procedure
- 1.2.3 Database Views
  - View alat tersedia
  - View alat dipinjam
  - View ruang dipinjam
- 1.2.4 Video Table Setup (`assets/create_video_table.sql`)
  - Video table creation
  - Sequence setup

### 1.3 Admin Setup
- 1.3.1 Admin Creation (`setup/create_admin.php`)
- 1.3.2 Admin Reset (`setup/reset_admin.php`)
- 1.3.3 View Creation (`setup/create_views.php`)

---

## 2.0 AUTHENTICATION & AUTHORIZATION

### 2.1 User Authentication
- 2.1.1 Login System (`login.php`)
  - Admin login form
  - Credential validation
  - Session creation
- 2.1.2 Registration System (`register.php`)
  - Admin registration
  - Password hashing
  - Account creation
- 2.1.3 Logout System (`admin/logout.php`)
  - Session destruction
  - Redirect to login

### 2.2 Access Control
- 2.2.1 Admin Panel Protection
  - Authentication middleware
  - Role-based access
  - Session validation

---

## 3.0 ADMIN PANEL - DASHBOARD

### 3.1 Dashboard Overview (`admin/dashboard.php`)
- 3.1.1 Statistics Display
  - Total articles count
  - Total research progress count
  - Total active loans
  - Total lab tools
  - Total lab rooms
  - Total members
  - Total students
  - Unread messages count
- 3.1.2 Quick Access Links
  - Navigation to modules
  - External site link

---

## 4.0 ADMIN PANEL - CONTENT MANAGEMENT

### 4.1 Research Management (`admin/research.php`)
- 4.1.1 Article Management Tab
  - Add article (judul, tahun, konten)
  - Edit article
  - Delete article
  - Article list with pagination
- 4.1.2 Research Progress Tab
  - Add research progress
  - Edit research progress
  - Delete research progress
  - Video URL embedding (YouTube)
  - Research progress list
- 4.1.3 Research Detail Tab
  - Add research detail (fokus_penelitian)
  - Edit research detail
  - Delete research detail
  - Title, description, detail fields
  - Bullet point formatting
- 4.1.4 Product Management Tab
  - Add product
  - Edit product
  - Delete product
  - Image upload (file/URL)
  - Product list

### 4.2 News Management (`admin/news.php`)
- 4.2.1 News Tab
  - Add news (judul, konten, gambar, tanggal)
  - Edit news
  - Delete news
  - News list with pagination
  - Date management
- 4.2.2 Video Tab
  - Add video (title, href_link)
  - Edit video
  - Delete video
  - Video file upload (max 100MB)
  - Video URL input
  - Video list

### 4.3 Member Management (`admin/member.php`)
- 4.3.1 Member CRUD
  - Add member (nama, email, jabatan, bidang_keahlian, foto, deskripsi)
  - Edit member
  - Delete member
  - Member list with pagination (10 per page)
  - Photo upload (file/URL)
- 4.3.2 Member Display
  - Member information display
  - Photo preview

### 4.4 Gallery Management (`admin/gallery.php`)
- 4.4.1 Gallery CRUD
  - Add gallery item
  - Edit gallery item
  - Delete gallery item
  - Image upload (file/URL/news thumbnail)
  - Gallery list with pagination
- 4.4.2 Image Sources
  - File upload
  - URL input
  - News thumbnail reference

---

## 5.0 ADMIN PANEL - LABORATORY MANAGEMENT

### 5.1 Lab Tools Management (`admin/alat_lab.php`)
- 5.1.1 Tool CRUD
  - Add lab tool (nama_alat, deskripsi, stock)
  - Edit lab tool
  - Delete lab tool (with borrow check)
  - Tool list with pagination (10 per page)
- 5.1.2 Stock Management
  - Available stock calculation
  - Borrowed stock tracking
  - Stock availability view

### 5.2 Lab Room Management (`admin/ruang_lab.php`)
- 5.2.1 Room CRUD
  - Add lab room (nama_ruang)
  - Edit lab room (nama_ruang, status)
  - Delete lab room (with borrow check)
  - Room list with pagination (10 per page)
- 5.2.2 Room Status Management
  - Available status
  - Maintenance status (yellow highlight)
  - Not available status
  - Currently borrowed count

### 5.3 Tool Loan Management (`admin/peminjaman.php`)
- 5.3.1 Pending Approval Requests
  - Request list with filters
  - Filter by type (tool/room)
  - Filter by item name (Cek Nama Barang)
  - Approve request
  - Reject request
  - Request details display
- 5.3.2 Active Loans
  - Active loan list
  - Filter by type (tool/room)
  - Filter by item name (Cek Nama Barang)
  - Return loan functionality
  - Loan details display
- 5.3.3 Loan Status
  - Approved loans tracking
  - Rejected loans tracking
  - Returned loans tracking

---

## 6.0 ADMIN PANEL - USER MANAGEMENT

### 6.1 Student Management (`admin/mahasiswa.php`)
- 6.1.1 Student CRUD
  - Add student (id_mahasiswa, nama, tahun, status)
  - Edit student
  - Delete student
  - Student list with pagination (10 per page)
- 6.1.2 Student Status
  - Regular status
  - Other status types

### 6.2 Partner Management (`admin/mitra.php`)
- 6.2.1 Partner CRUD
  - Add partner (nama_institusi, logo)
  - Edit partner
  - Delete partner
  - Partner list with pagination (10 per page)
- 6.2.2 Logo Management
  - Logo upload (file/URL)
  - Logo display

---

## 7.0 ADMIN PANEL - COMMUNICATION

### 7.1 Guestbook Management (`admin/buku_tamu.php`)
- 7.1.1 Message Management
  - View messages
  - Mark as read/unread
  - Delete messages
  - Message list with pagination (10 per page)
  - Filter by read/unread status
- 7.1.2 Message Display
  - View message button
  - Full message display
  - Visitor information display
  - Message toggle functionality

### 7.2 Attendance Management (`admin/absensi.php`)
- 7.2.1 Attendance Records
  - View attendance records
  - Delete attendance records
  - Attendance list with pagination (15 per page)
  - Filter by date (today/week/month/all)
- 7.2.2 Attendance Display
  - Student information
  - Date and time records
  - Attendance statistics

---

## 8.0 ADMIN PANEL - SYSTEM SETTINGS

### 8.1 Settings Management (`admin/settings.php`)
- 8.1.1 Page Titles Tab
  - Home page title/subtitle
  - News page title/subtitle
  - Member page title/subtitle
  - Research page title/subtitle
  - Guestbook page title/subtitle
  - Attendance page title/subtitle
  - Tool loans page title/subtitle
- 8.1.2 Footer Settings Tab
  - Footer logo upload
  - Footer title
  - Footer subtitle
  - Copyright text
- 8.1.3 Contact Information Tab
  - Contact email
  - Contact phone
  - Contact address
- 8.1.4 Site Settings Tab
  - Site title
  - Site subtitle
  - Site logo upload

---

## 9.0 PUBLIC-FACING PAGES

### 9.1 Home Page (`index.php`)
- 9.1.1 Hero Section
  - Dynamic title and subtitle
  - Background image
- 9.1.2 Our Research Section
  - Research title display (with capitalization fix)
  - Research description display
  - Multiple research items display
  - Bullet point formatting for detail
- 9.1.3 Research Fields Section
  - Research detail display
  - Bullet point formatting
- 9.1.4 Our Products Section
  - Product display with images
  - Product information
- 9.1.5 Our Team Section
  - Member display
  - Member photos
  - Member information
- 9.1.6 Gallery Section
  - Image gallery display
  - Masonry layout
  - Image overlay

### 9.2 News Page (`news.php`)
- 9.2.1 News Display
  - News list with pagination
  - News filtering by year
  - News thumbnail display
  - News content preview
  - News date display

### 9.3 Research Page (`research.php`)
- 9.3.1 Articles Section
  - Article list with pagination
  - Article title (clickable link to URL in konten)
  - Article year display
  - Article content (URL extraction)
- 9.3.2 Research Progress Section
  - Progress list display
  - Video embedding (YouTube)
  - Progress description
  - Progress year

### 9.4 Member Page (`member.php`)
- 9.4.1 Member Display
  - Member list
  - Member photos
  - Member information
  - Member expertise

---

## 10.0 SERVICE PAGES

### 10.1 Borrowing Service (`service/peminjaman.php`)
- 10.1.1 Tool Borrowing Tab
  - Tool selection
  - Quantity input
  - Borrow date input (from today onwards)
  - Return date input (from today onwards)
  - Date validation (no past dates)
  - Stock availability display
  - Form submission
- 10.1.2 Room Borrowing Tab
  - Room selection
  - Monthly calendar display
  - Calendar navigation (prev/next month)
  - Today badge (auto-updating)
  - Date selection (no today or past dates)
  - Time selection
  - Form submission
- 10.1.3 Date Validation
  - Real-time date validation
  - Past date prevention
  - Today date handling
  - WIB timezone support
- 10.1.4 Form Processing
  - Tool borrowing submission
  - Room borrowing submission
  - Sequence synchronization
  - Error handling
  - Success/error messages

### 10.2 Attendance Service (`service/absen.php`)
- 10.2.1 Attendance Form
  - Student ID input
  - Student validation
  - Attendance recording
  - Time tracking
- 10.2.2 Student Search
  - Search functionality
  - Student information display

### 10.3 Guestbook Service (`service/buku_tamu.php`)
- 10.3.1 Guestbook Form
  - Visitor name input
  - Email input
  - Institution input
  - Phone number input
  - Message input
  - Form submission

---

## 11.0 UI/UX COMPONENTS

### 11.1 Header Component (`includes/header.php`)
- 11.1.1 Navigation Menu
  - Home link
  - News link
  - Research link
  - Member link
  - Contact information
- 11.1.2 Responsive Design
  - Mobile menu
  - Desktop menu

### 11.2 Footer Component (`includes/footer.php`)
- 11.2.1 Footer Content
  - Footer logo
  - Footer title
  - Copyright text
  - Contact information

### 11.3 Admin Sidebar (`admin/partials/sidebar.php`)
- 11.3.1 Navigation Menu
  - Dashboard link
  - Settings link
  - Research link
  - Members link
  - News link
  - Lab Partners link
  - Students link
  - Lab Tools link
  - Lab Room link
  - Tool Loan link
  - Attendance link
  - Guestbook link
  - Gallery link
  - View Site link
  - Logout link
- 11.3.2 Branding
  - Logo display
  - Brand text

### 11.4 Styling
- 11.4.1 Admin CSS (`admin/admin.css`)
  - Theme colors
  - Logo styling
  - Sidebar styling
  - Form styling
  - Table styling
  - Button styling
  - Pagination styling
- 11.4.2 Public CSS
  - Home page styling (`css/style-home.css`)
  - News page styling (`css/style-news.css`)
  - Research page styling (`css/style-research.css`)
  - Member page styling (`css/style-member.css`)
  - Attendance page styling (`css/style-absensi.css`)
  - Guestbook page styling (`css/style-buku-tamu.css`)
  - Borrowing page styling (`css/style-peminjaman.css`)

---

## 12.0 DATABASE OPERATIONS

### 12.1 Data Retrieval
- 12.1.1 Query Execution
  - Prepared statements
  - Parameter binding
  - Result fetching
- 12.1.2 Data Filtering
  - Search functionality
  - Filter implementation
  - Sorting

### 12.2 Data Manipulation
- 12.2.1 Insert Operations
  - Sequence synchronization
  - Foreign key handling
  - Data validation
- 12.2.2 Update Operations
  - Record updates
  - Conditional updates
  - Timestamp updates
- 12.2.3 Delete Operations
  - Cascade delete handling
  - Constraint checking
  - Safe deletion

### 12.3 Database Views
- 12.3.1 View Alat Tersedia
  - Available stock calculation
  - Borrowed count
- 12.3.2 View Alat Dipinjam
  - Active loans display
  - Loan details
- 12.3.3 View Ruang Dipinjam
  - Room borrowing status
  - Room availability

---

## 13.0 FILE MANAGEMENT

### 13.1 File Upload
- 13.1.1 Image Upload
  - File validation
  - Size checking (max 5MB)
  - Type validation (JPG, PNG, GIF, WEBP)
  - Directory creation
  - Unique filename generation
- 13.1.2 Video Upload
  - File validation
  - Size checking (max 100MB)
  - Directory creation
  - Unique filename generation
- 13.1.3 Upload Directories
  - Members photos (`uploads/members/`)
  - News thumbnails (`uploads/news/`)
  - Gallery images (`uploads/gallery/`)
  - Partner logos (`uploads/mitra/`)
  - Product images (`uploads/produk/`)
  - Video files (`uploads/videos/`)

### 13.2 File Deletion
- 13.2.1 File Cleanup
  - File removal utility
  - Path validation
  - Error handling

---

## 14.0 VALIDATION & SECURITY

### 14.1 Input Validation
- 14.1.1 Form Validation
  - Required field checking
  - Data type validation
  - Length validation
  - Format validation
- 14.1.2 Date Validation
  - Past date prevention
  - Future date validation
  - Date range validation
- 14.1.3 File Validation
  - File type checking
  - File size validation
  - File extension validation

### 14.2 Security Measures
- 14.2.1 SQL Injection Prevention
  - Prepared statements
  - Parameter binding
  - Input sanitization
- 14.2.2 XSS Prevention
  - HTML escaping
  - Output encoding
- 14.2.3 Session Security
  - Session validation
  - CSRF protection
  - Session timeout

---

## 15.0 PAGINATION SYSTEM

### 15.1 Pagination Implementation
- 15.1.1 Pagination Logic
  - Items per page calculation
  - Current page detection
  - Offset calculation
  - Total pages calculation
- 15.1.2 Pagination Display
  - Previous/Next buttons
  - Page number links
  - Ellipsis for large page counts
  - Active page highlighting
  - Pagination info display
- 15.1.3 Paginated Modules
  - News list (10 per page)
  - Member list (10 per page)
  - Student list (10 per page)
  - Partner list (10 per page)
  - Lab tools list (10 per page)
  - Lab rooms list (10 per page)
  - Attendance list (15 per page)
  - Guestbook list (10 per page)
  - Gallery list (with pagination)

---

## 16.0 FEATURES & ENHANCEMENTS

### 16.1 Calendar Features
- 16.1.1 Monthly Calendar
  - Month navigation
  - Date selection
  - Today indicator (auto-updating)
  - Past date prevention
  - Clickable dates
- 16.1.2 Date Picker
  - HTML5 date input
  - Min/max date validation
  - Real-time validation
  - Dynamic attribute updates

### 16.2 Filtering & Search
- 16.2.1 Filter Implementation
  - Type filter (tool/room)
  - Item name search (Cek Nama Barang)
  - Status filter
  - Date filter
- 16.2.2 Search Functionality
  - Text search
  - Case-insensitive search
  - Partial matching

### 16.3 Tab Navigation
- 16.3.1 Tab System
  - URL parameter-based tabs
  - Tab persistence
  - Tab switching
  - Active tab highlighting

### 16.4 Status Management
- 16.4.1 Loan Status
  - Pending status
  - Approved status
  - Rejected status
  - Returned status
- 16.4.2 Room Status
  - Available status
  - Maintenance status (yellow highlight)
  - Not available status
  - Borrowed status

### 16.5 Message System
- 16.5.1 Success Messages
  - Add success
  - Update success
  - Delete success
- 16.5.2 Error Messages
  - Validation errors
  - Database errors
  - File upload errors
  - Specific error messages (room maintenance, room not available)

---

## 17.0 TESTING & DEBUGGING

### 17.1 Database Testing
- 17.1.1 Connection Testing (`config/test_pgsql.php`)
- 17.1.2 Join Testing (`test_joins.php`)
- 17.1.3 Database Testing (`test_db.php`)

### 17.2 Error Handling
- 17.2.1 Error Logging
  - PDO exception handling
  - Error message display
  - User-friendly error messages

---

## 18.0 DOCUMENTATION

### 18.1 Code Documentation
- 18.1.1 Function Documentation
- 18.1.2 Code Comments
- 18.1.3 README Files

### 18.2 Database Documentation
- 18.2.1 SQL Schema Documentation
- 18.2.2 Procedure Documentation
- 18.2.3 View Documentation

---

## SUMMARY

**Total Modules**: 18 major modules
**Total Features**: 100+ features
**Admin Pages**: 14 pages
**Public Pages**: 4 pages
**Service Pages**: 3 pages
**Database Tables**: 20+ tables
**Database Views**: 3 views
**Database Procedures**: 5+ procedures

---

*Generated: December 2025*
*Project: InLET - Information and Learning Engineering Technology*
*Lab Management System*

