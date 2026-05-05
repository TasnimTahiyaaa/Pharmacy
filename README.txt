========================================
  NOOR PHARMACY — PHP + MySQL Version
  Group 7 | CSE 3105
========================================

REQUIREMENTS
------------
- XAMPP (or any PHP 8.0+ + MySQL/MariaDB server)
- PHP 8.0 or higher
- MySQL 5.7 / MariaDB 10.4 or higher

INSTALLATION STEPS
------------------

1. COPY PROJECT FILES
   Copy the entire "noor-pharmacy-php" folder into:
   C:\xampp\htdocs\noor-pharmacy-php\   (Windows)
   /Applications/XAMPP/htdocs/noor-pharmacy-php/  (Mac)

2. START XAMPP
   Open XAMPP Control Panel and start:
   - Apache
   - MySQL

3. IMPORT DATABASE
   a. Open your browser and go to: http://localhost/phpmyadmin
   b. Click "New" to create a new database named: noor_pharmacy
   c. Select the new database
   d. Click the "Import" tab
   e. Choose file: database.sql (from this folder)
   f. Click "Go" to import

   OR run from command line:
   mysql -u root -p < database.sql

4. CONFIGURE DATABASE (if needed)
   Edit: includes/db.php
   Change DB_USER and DB_PASS if your MySQL uses different credentials.
   Default: root / (empty password) — standard XAMPP setup.

5. OPEN IN BROWSER
   http://localhost/noor-pharmacy-php/

========================================
  DEMO ACCOUNTS
========================================

  Admin Login:
  Email:    admin@noorpharmacy.com
  Password: admin123

  Customer Login:
  Email:    customer@test.com
  Password: customer123

  Admin Panel: http://localhost/noor-pharmacy-php/admin/dashboard.php

========================================
  FEATURES
========================================

PUBLIC PAGES:
  - Homepage (hero, stats, services, featured medicines, reviews, contact)
  - Medicine Catalogue with search + category filter + pagination
  - Customer Registration & Login
  - Shopping Cart with quantity controls
  - Order Checkout (bKash / Cash on Delivery)
  - Order History & Tracking
  - Customer Profile Management

ADMIN PANEL:
  - Dashboard with KPIs and revenue charts
  - Inventory Management (stock updates, low-stock alerts)
  - Medicine CRUD (add, edit, deactivate)
  - Order Management (view all, update status, create offline orders)
  - Customer Directory
  - Payments Ledger
  - Reports & Analytics (weekly/monthly/yearly)
  - Notifications (low-stock alerts)

========================================
  TECH STACK
========================================
  Frontend : HTML5, CSS3, Vanilla JavaScript
  Backend  : PHP 8.x
  Database : MySQL (via MySQLi extension)
  Fonts    : Google Fonts (Inter)

========================================
  FILE STRUCTURE
========================================
  noor-pharmacy-php/
  ├── index.php              Homepage
  ├── login.php              Customer/Admin login
  ├── register.php           Customer registration
  ├── medicines.php          Medicine catalogue
  ├── cart.php               Shopping cart + checkout
  ├── orders.php             Customer order history
  ├── profile.php            Customer profile
  ├── logout.php             Logout handler
  ├── database.sql           Database schema + seed data
  ├── README.txt             This file
  ├── assets/
  │   ├── css/style.css      Complete design system
  │   └── js/main.js         JavaScript (cart, modals, charts)
  ├── includes/
  │   ├── db.php             Database connection + helper functions
  │   ├── header.php         Public navbar
  │   ├── footer.php         Public footer
  │   ├── admin_header.php   Admin sidebar layout
  │   └── admin_footer.php   Admin layout close
  └── admin/
      ├── dashboard.php      Admin dashboard + KPIs
      ├── inventory.php      Stock management
      ├── medicines.php      Medicine CRUD
      ├── orders.php         Order management + offline orders
      ├── customers.php      Customer directory
      ├── payments.php       Payment ledger
      ├── reports.php        Analytics + charts
      └── notifications.php  Low-stock notifications

========================================
  NOTES
========================================
  - All passwords are hashed using SHA-256 + salt
  - Cart operates via AJAX (no page reload)
  - Charts are rendered with HTML5 Canvas (no external library)
  - Responsive design works on mobile and desktop
  - Low-stock notifications auto-generated when stock <= threshold

  Group 7 | CSE 3105
========================================
