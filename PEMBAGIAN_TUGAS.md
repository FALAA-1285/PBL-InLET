# PEMBAGIAN TUGAS PROGRAM PBL-InLET
## Untuk 6 Orang Anggota Tim

---

## 📋 **OVERVIEW PROYEK**
Website InLET (Information and Learning Engineering Technology) dengan fitur:
- **Frontend**: Home, Research, Members, News
- **Admin Panel**: Dashboard, CRUD untuk berbagai modul
- **Backend**: Database, Authentication, File Upload

---

## 👥 **PEMBAGIAN TUGAS**

> **📊 TINGKAT KESULITAN:**
> - ⚖️ **SEIMBANG**: Semua Person memiliki tingkat kesulitan yang sama

---

### **PERSON 1: Frontend Developer - Home Page & UI Components** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **Home Page (index.php)**
  - Hero section dengan dynamic title/subtitle (dari database)
  - Research section dengan read more/less functionality
  - Research Fields section dengan pagination
  - Video section dengan YouTube integration
  - Products section dengan card layout
  - Partners section dengan Swiper slider
  - Team section dengan Swiper slider
  - Gallery section dengan masonry layout & infinite scroll

- ✅ **UI Components & Styling**
  - Header & Navigation (includes/header.php) - fully responsive
  - Footer (includes/footer.php) - complete layout
  - CSS styling (css/style-home.css) - responsive design
  - Responsive design untuk mobile/tablet/desktop
  - JavaScript untuk interaktifitas (swiper, masonry, read more, infinite scroll)

**File yang Dikerjakan:**
- `index.php`
- `includes/header.php`
- `includes/footer.php`
- `css/style-home.css`
- JavaScript di `index.php`

**Deliverables:**
- Home page yang fully functional dan responsive
- UI components yang reusable
- Smooth animations dan transitions
- Gallery dengan masonry layout & infinite scroll

---

### **PERSON 2: Frontend Developer - Research & Members Pages** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **Research Page (research.php)**
  - Display research fields dengan detail
  - Search dan filter functionality (kategori, tahun, dll)
  - Pagination dengan page numbers
  - Read more/less untuk deskripsi panjang
  - Sorting options (by name, date, dll)

- ✅ **Members Page (member.php)**
  - Team members listing dengan filter
  - Alphabet index navigation
  - Search functionality dengan real-time filtering
  - Member profile cards dengan foto
  - Google Scholar integration dengan link
  - Filter by role/position

- ✅ **Styling**
  - `css/style-research.css` - fully responsive
  - `css/style-member.css` - fully responsive
  - Responsive layouts untuk semua breakpoints
  - Smooth transitions dan animations

**File yang Dikerjakan:**
- `research.php`
- `member.php`
- `css/style-research.css`
- `css/style-member.css`

**Deliverables:**
- Research page dengan search & filter yang functional
- Members page dengan alphabet navigation
- Responsive design untuk kedua halaman
- Interactive filtering dan search

---

### **PERSON 3: Frontend Developer - News Page & Gallery** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **News Page (news.php)**
  - News listing dengan pagination
  - News detail page dengan full content
  - Search functionality
  - Category filter (jika ada)
  - Date sorting (newest/oldest)
  - Related news section

- ✅ **Gallery Enhancement**
  - Gallery display dengan lightbox/modal
  - Image upload preview (jika ada)
  - Image optimization & lazy loading
  - Gallery categories/filtering
  - Smooth image transitions

- ✅ **Styling**
  - `css/style-news.css` - fully responsive
  - Gallery styling dengan lightbox
  - Responsive image handling

**File yang Dikerjakan:**
- `news.php`
- `css/style-news.css`
- Gallery related files

**Deliverables:**
- News page dengan full CRUD display
- Gallery dengan lightbox functionality
- Image optimization & lazy loading
- Responsive design untuk semua halaman

---

### **PERSON 4: Backend Developer - Database & Admin Panel (Part 1)** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **Database Management**
  - Database schema design & optimization
  - Database connection (`config/database.php`)
  - Stored procedures (`config/procedures.php`)
  - Database migrations & setup scripts
  - Database views untuk queries
  - Basic index optimization

- ✅ **Admin Panel - Core Modules**
  - Dashboard dengan statistics (`admin/dashboard.php`)
    - Basic statistics display
    - Data summary cards
  - Settings page (`admin/settings.php`)
    - Site settings management
    - Basic configuration
  - Research management dengan CRUD (`admin/research.php`)
    - Full CRUD operations
    - Search & filter
  - Members management dengan CRUD (`admin/member.php`)
    - Full CRUD operations
    - Search & filter

- ✅ **Authentication & Security**
  - Secure login system dengan password hashing (`login.php`)
  - Registration system (`register.php`)
  - Session management (`config/auth.php`)
  - Basic role-based access
  - CSRF protection
  - SQL injection prevention
  - XSS protection
  - Password reset functionality

- ✅ **File Upload System**
  - Upload handler dengan validation (`config/upload.php`)
  - Image processing & resizing
  - File type validation & security
  - Error handling untuk upload

**File yang Dikerjakan:**
- `config/database.php`
- `config/procedures.php`
- `config/auth.php`
- `config/upload.php`
- `login.php`
- `register.php`
- `admin/dashboard.php`
- `admin/settings.php`
- `admin/research.php`
- `admin/member.php`
- `admin/partials/sidebar.php`
- `setup/create_views.php`
- `setup/create_view_alat_dipinjam.php`

**Deliverables:**
- Database yang terstruktur dan optimized
- Admin panel untuk Research & Members dengan CRUD
- Secure authentication system
- Robust file upload system

---

### **PERSON 5: Backend Developer - Admin Panel (Part 2) & Services** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **Admin Panel - Additional Modules**
  - News management dengan CRUD (`admin/news.php`)
    - Rich text editor integration
    - Image upload & management
    - Full CRUD operations
  - Lab Partners management (`admin/mitra.php`)
    - Logo upload & management
    - Full CRUD operations
  - Students management (`admin/mahasiswa.php`)
    - Student data management
    - Full CRUD operations
  - Gallery management (`admin/gallery.php`)
    - Image upload & management
    - Gallery categories
    - Full CRUD operations

- ✅ **Lab Management System**
  - Lab Tools management (`admin/alat_lab.php`)
    - Tool inventory management
    - Tool condition tracking
    - Full CRUD operations
  - Lab Room management (`admin/ruang_lab.php`)
    - Room management
    - Full CRUD operations
  - Tool Loan system (`admin/peminjaman.php`)
    - Loan request & approval
    - Due date tracking
    - Return verification
    - Loan history
  - Attendance system (`admin/absensi.php`)
    - Attendance recording
    - Attendance reports
    - Attendance statistics
  - Guestbook (`admin/buku_tamu.php`)
    - Guest registration
    - Visit tracking
    - Full CRUD operations

- ✅ **Service Layer**
  - API endpoints untuk AJAX (`service/absen.php`)
    - JSON response formatting
    - Error handling & validation
  - Guestbook service (`service/buku_tamu.php`)
    - Guest registration API
    - Data validation
  - Loan service (`service/peminjaman.php`)
    - Loan request API
    - Approval workflow API
    - Return processing API

- ✅ **Configuration & Settings Management**
  - Settings management (`config/settings.php`)
  - Site configuration
  - Dynamic logo & title management

**File yang Dikerjakan:**
- `admin/news.php`
- `admin/mitra.php`
- `admin/mahasiswa.php`
- `admin/gallery.php`
- `admin/alat_lab.php`
- `admin/ruang_lab.php`
- `admin/peminjaman.php`
- `admin/absensi.php`
- `admin/buku_tamu.php`
- `service/absen.php`
- `service/buku_tamu.php`
- `service/peminjaman.php`
- `config/settings.php`
- `css/style-absensi.css`
- `css/style-peminjaman.css`
- `css/style-buku-tamu.css`

**Deliverables:**
- Complete admin panel untuk semua modul
- Lab management system yang functional
- Service layer untuk AJAX operations
- All CRUD operations working

---

### **PERSON 6: Full-Stack Developer - Integration & File Management** ⚖️ SEIMBANG
**Tugas Utama:**
- ✅ **File Upload System**
  - Upload handler dengan validation (`config/upload.php`)
  - Image processing & validation
  - File storage management
  - Error handling untuk upload
  - Multiple file upload support

- ✅ **Configuration & Settings**
  - Settings management (`config/settings.php`)
  - Site configuration
  - Dynamic logo & title management
  - Environment configuration

- ✅ **Integration & Testing**
  - Integration testing antar modul
  - Cross-browser compatibility testing
  - Performance optimization
  - Bug fixing & debugging
  - Code review & quality assurance

- ✅ **Documentation**
  - Code documentation
  - API documentation
  - User manual (jika diperlukan)
  - Setup instructions
  - Deployment guide

- ✅ **Setup Scripts**
  - Admin creation (`setup/create_admin.php`)
  - Database views (`setup/create_views.php`)
  - Reset utilities (`setup/reset_admin.php`)
  - Database migration scripts

**File yang Dikerjakan:**
- `config/upload.php`
- `config/settings.php`
- `setup/create_admin.php`
- `setup/create_views.php`
- `setup/reset_admin.php`
- `setup/create_view_alat_dipinjam.php`
- Testing files (`test_db.php`, `test_joins.php`, `test_pgsql.php`)
- Documentation files

**Deliverables:**
- Robust file upload system
- Complete configuration management
- Well-integrated system
- Complete documentation
- Setup scripts working

---

## 🔄 **WORKFLOW & COLLABORATION**

### **Phase 1: Setup & Planning (Week 1)**
- Person 4: Setup database & advanced configuration
- Person 1, 2, 3: Design mockups & wireframes (simple)
- Person 5: Plan admin panel structure & API design
- Person 6: Setup development environment

### **Phase 2: Core Development (Week 2-3)**
- Person 1: Home page development dengan fitur lengkap
- Person 2: Research & Members pages dengan filter & search
- Person 3: News & Gallery dengan lightbox
- Person 4: Database & core admin modules
- Person 5: Additional admin modules & services
- Person 6: File upload & configuration system

### **Phase 3: Integration (Week 4)**
- All: Integration testing
- Person 6: Bug fixing & optimization, code review
- Person 1-3: Frontend polish (jika ada waktu)
- Person 4-5: Backend optimization & advanced features completion

### **Phase 4: Testing & Deployment (Week 5)**
- All: Final testing
- Person 6: Documentation
- Person 4-5: Final backend features & bug fixes
- All: Deployment preparation

---

## 📝 **CHECKLIST PER PERSON**

### **Person 1 Checklist:** ⚖️ SEIMBANG
- [ ] Home page semua section functional
- [ ] Responsive design untuk semua breakpoints
- [ ] Swiper sliders working (videos, partners, team)
- [ ] Gallery masonry layout & infinite scroll
- [ ] Read more/less functionality
- [ ] Header & footer responsive
- [ ] Smooth animations & transitions

### **Person 2 Checklist:** ⚖️ SEIMBANG
- [ ] Research page dengan search & filter
- [ ] Members page dengan alphabet navigation
- [ ] Pagination working dengan page numbers
- [ ] Responsive layouts
- [ ] Google Scholar integration
- [ ] Sorting & filtering options

### **Person 3 Checklist:** ⚖️ SEIMBANG
- [ ] News listing & detail page
- [ ] Search functionality
- [ ] Gallery lightbox/modal
- [ ] Image optimization & lazy loading
- [ ] Date sorting
- [ ] Category filter

### **Person 4 Checklist:** ⚖️ SEIMBANG
- [ ] Database schema complete
- [ ] Authentication system secure
- [ ] Dashboard functional dengan statistics
- [ ] Settings page working
- [ ] Research & Members admin CRUD
- [ ] File upload system dengan validation
- [ ] Security features implemented (CSRF, XSS protection)
- [ ] Password reset functionality

### **Person 5 Checklist:** ⚖️ SEIMBANG
- [ ] All admin modules functional
- [ ] Lab management system complete
- [ ] Tool loan system working
- [ ] Attendance system functional
- [ ] Service layer APIs working
- [ ] All CRUD operations working
- [ ] Guestbook system functional

### **Person 6 Checklist:** ⚖️ SEIMBANG
- [ ] File upload system robust
- [ ] Configuration management complete
- [ ] All integrations tested
- [ ] Documentation complete
- [ ] Setup scripts working
- [ ] Code review completed

---

## 🛠️ **TECHNICAL STACK**

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Swiper.js
- **Backend**: PHP, PDO
- **Database**: PostgreSQL (berdasarkan test_pgsql.php)
- **Server**: Apache (Laragon)

---

## 📞 **COMMUNICATION PROTOCOL**

1. **Daily Standup**: Setiap hari untuk update progress
2. **Git Workflow**: 
   - Main branch untuk production
   - Feature branches untuk setiap person
   - Pull requests untuk code review
3. **Issue Tracking**: Gunakan GitHub Issues atau Trello
4. **Code Review**: Setiap person review code person lain sebelum merge

---

## ⚠️ **IMPORTANT NOTES**

1. **Code Standards**: 
   - Follow PSR-12 coding standards
   - Comment complex logic
   - Use meaningful variable names

2. **Security**:
   - Always use prepared statements
   - Validate & sanitize user input
   - Implement CSRF protection

3. **Performance**:
   - Optimize database queries
   - Minimize HTTP requests
   - Compress images

4. **Testing**:
   - Test on multiple browsers
   - Test responsive design
   - Test all CRUD operations

---

## 🎯 **SUCCESS CRITERIA**

✅ Semua halaman frontend functional dan responsive  
✅ Admin panel untuk semua modul working  
✅ File upload system robust  
✅ Database optimized  
✅ Security best practices implemented  
✅ Code well-documented  
✅ No critical bugs  
✅ Performance optimized  

---

**Good Luck Team! 🚀**

