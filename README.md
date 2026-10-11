# Campus Lost and Found System 🎓🔍
A complete Web Development Mini Project built using **HTML5, CSS3, JavaScript, PHP, and MySQL (SQL)**.

---

## 📌 Project Overview
The **Campus Lost and Found System** is a web portal designed for colleges and universities to bridge the gap between students who lose items and those who find them across campus facilities (libraries, classrooms, sports complexes, cafeterias).

### 🌟 Key Features
- **Item Posting**: Post Lost or Found items with detailed descriptions, location, category, date, contact info, and photo attachment.
- **Instant Search & Real-time Filters**: Client-side JavaScript filtering by keyword, item type (Lost vs. Found), and category without page reload.
- **Claim & Ownership Verification System**: Allows rightful owners to submit proof of ownership (e.g., lock screen wallpaper, specific contents, serial numbers). Item posters can review, approve, or reject claims.
- **Student Dashboard**: Track your posted items, monitor claims you made, and mark items as "Resolved / Returned".
- **Security Admin Panel**: Dedicated moderation dashboard for campus security to inspect all items and view registered students.
- **Secure Authentication**: Password hashing (`password_hash` with BCRYPT), session management, and SQL injection prevention using **PHP PDO Prepared Statements**.

---

## 🛠️ Technology Stack
| Layer | Technologies |
| :--- | :--- |
| **Frontend** | HTML5, CSS3 (Modern Flexbox/Grid, Responsive Design), Vanilla JavaScript (DOM manipulation, FileReader, dynamic filters) |
| **Icons & Typography** | FontAwesome 6, Google Inter Font |
| **Backend** | PHP 7.4+ / 8.x (PDO Object-Oriented Database Layer, Session Auth) |
| **Database** | MySQL / MariaDB (Relational SQL Schema with Foreign Keys & Cascading Deletes) |
| **Server Environment** | XAMPP / WAMP / LAMP / MAMP or PHP Built-in Server |

---

## 📁 Project Directory Structure
```text
campus-lost-found/
│
├── assets/
│   ├── css/
│   │   └── style.css            # Responsive modern campus styles
│   └── js/
│       └── main.js              # Live search filter, modal controls, image preview
│
├── config/
│   └── db.php                   # Database connection (PDO) & session auth helpers
│
├── database/
│   └── schema.sql               # Complete SQL schema with tables & sample seed data
│
├── includes/
│   ├── header.php               # HTML <head> with fonts, icons, meta
│   ├── navbar.php               # Sticky navigation with auth state
│   └── footer.php               # Common footer & script attachments
│
├── uploads/                     # Storage folder for user-uploaded item photos
│
├── index.php                    # Home page with stats, filters & item cards
├── post-item.php                # Form to report Lost or Found items
├── item-details.php             # Detailed view, claim submissions & owner actions
├── my-items.php                 # User dashboard for reports & submitted claims
├── admin.php                    # Campus security admin management panel
├── login.php                    # Student / Admin login page
├── register.php                 # Student registration page
├── logout.php                   # Secure session destroyer
└── README.md                    # Project documentation
```

---

## 🚀 Setup & Installation Guide (XAMPP on Windows)

### 1. Install & Start XAMPP
1. Download and install [XAMPP](https://www.apachefriends.org/) (if not already installed).
2. Open **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

### 2. Move Project to `htdocs`
Copy this `campus-lost-found` project folder into your XAMPP web root directory:
```text
C:\xampp\htdocs\campus-lost-found
```

### 3. Import Database
1. Open your browser and navigate to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **Import** in the top navigation bar.
3. Choose the file located at:
   ```text
   C:\xampp\htdocs\campus-lost-found\database\schema.sql
   ```
4. Click **Import** (or **Go**) at the bottom.
   *(This automatically creates the database `campus_lost_found` along with all tables and pre-loaded sample data!)*

### 4. Run the Project
Open your browser and visit:
👉 **[http://localhost/campus-lost-found](http://localhost/campus-lost-found)**

---

## 🔑 Pre-seeded Demo Accounts
All sample accounts are pre-configured in `schema.sql`:

| Role | Email / Login ID | Password | Notes |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@campus.edu` *(or `ADMIN001`)* | `password123` | Access to Security Admin Panel |
| **Student 1** | `alex.j@campus.edu` *(or `STU202401`)* | `password123` | Has active lost & found listings |
| **Student 2** | `sarah.w@campus.edu` *(or `STU202402`)* | `password123` | Regular student account |
| **Student 3** | `david.l@campus.edu` *(or `STU202403`)* | `password123` | Has an active claim submitted |

---

## 🎓 Mini Project Evaluation & Viva Guide

When presenting this project to your college faculty or examiner, make sure to highlight:

1. **Architecture & Separation of Concerns**:
   - Organized includes (`header.php`, `navbar.php`, `footer.php`).
   - Centralized database configuration via PDO in `config/db.php`.
   - Asset isolation for styling (`style.css`) and logic (`main.js`).

2. **Security Best Practices Implemented**:
   - **SQL Injection Prevention**: 100% prepared statements (`$pdo->prepare()`) across every query.
   - **Password Security**: Passwords hashed using industry-standard `password_hash($pwd, PASSWORD_DEFAULT)` and verified via `password_verify()`.
   - **XSS Protection**: User inputs sanitized using `htmlspecialchars()` before outputting to HTML.
   - **File Upload Security**: Verification of MIME types and file extensions, unique filename generation (`bin2hex(random_bytes(4))`), and file size limits.

3. **Database Design**:
   - Foreign key constraints connecting `items` and `claims` to the `users` table with `ON DELETE CASCADE`.
   - Enumerated data types (`ENUM`) for item types, categories, and claim states to enforce database-level integrity.
