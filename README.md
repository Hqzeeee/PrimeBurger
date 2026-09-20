# PrimeBurger Inventory Management System

A complete, OOP/MVC-structured PHP + MySQL inventory management system built
for PrimeBurger, based on the elicited requirements in `Case Study 3 -
COMSCI 3100` (Product Backlog PB-01 to PB-10) plus the extended feature set
requested for a production-ready deployment.

---

## 1. Requirements

- XAMPP (or any Apache + PHP 8+ + MySQL/MariaDB stack)
- PHP 8.0 or higher, with the `pdo_mysql` extension enabled (on by default in XAMPP)
- A modern browser (Chrome/Edge recommended for the camera QR scanner)

---

## 2. Installation Guide

1. **Install XAMPP** (or use your existing Apache/MySQL/PHP setup) and start
   the **Apache** and **MySQL** services from the XAMPP Control Panel.

2. **Copy the project** into your web root:
   - Windows: `C:\xampp\htdocs\PrimeBurger`
   - macOS/Linux: `/Applications/XAMPP/htdocs/PrimeBurger` or `/opt/lampp/htdocs/PrimeBurger`

3. **Import the database.**
   - Open `http://localhost/phpmyadmin`
   - Click **New** to create a database, or simply import the file directly —
     `database/schema.sql` already contains `CREATE DATABASE IF NOT EXISTS primeburger_ims`.
   - Go to the **Import** tab, choose `database/schema.sql`, and click **Go**.
   - This creates all 8 tables, sample categories/suppliers/products, and two
     default user accounts (see below).

4. **Configure the database connection.**
   Open `config/config.php` and adjust if your MySQL setup differs from the
   XAMPP defaults:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'primeburger_ims');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
   Also update `APP_URL` to match where you placed the project, e.g.
   `http://localhost/PrimeBurger`.

5. **Run the project.**
   Visit `http://localhost/PrimeBurger` in your browser. You'll be redirected
   to the login page.

---

## 3. Default Accounts

| Role            | Username | Password      |
|-----------------|----------|---------------|
| Owner / Admin   | `admin`  | `Password123!` |
| Inventory Staff | `staff1` | `Password123!` |

**Change these passwords immediately after first login** (Settings → My
Account) if this will be used with real data.

---

## 4. Folder Structure

```
PrimeBurger/
├── app/
│   ├── controllers/      Auth, Product, Stock, User controllers (business logic)
│   ├── core/              Database, Model, Auth, Csrf, Validator, Flash (framework classes)
│   ├── models/            UserModel, ProductModel, CategoryModel, SupplierModel,
│   │                       InventoryTransactionModel, ReportModel
│   ├── services/          ActivityLogger, NotificationService
│   ├── views/layouts/      Shared header/footer (sidebar, topbar)
│   ├── views/errors/       403 Forbidden page
│   └── bootstrap.php       Loads config + classes + starts the session (included by every page)
├── api/
│   └── qr-lookup.php      JSON endpoint used by the QR scanner page
├── assets/
│   ├── css/app.css        Full application stylesheet
│   └── js/app.js          Sidebar toggle, modals, confirm dialogs, table search
├── config/
│   └── config.php          Database credentials & app settings
├── database/
│   └── schema.sql          Full schema + sample data
├── storage/
│   └── qrcodes/             (reserved for cached QR images, if you extend the system)
├── dashboard.php, products.php, stock.php, qr-scanner.php,
│   reports.php, notifications.php, users.php, settings.php,
│   login.php, logout.php, index.php, export.php
└── README.md
```

The application follows an **OOP MVC-style pattern**: each top-level `.php`
file is a thin "controller + view" page — it pulls data through a Model,
runs it past a Controller class for anything that writes to the database,
and then renders the HTML directly (kept together with the page for easy
navigation in a school project context, while `app/controllers` still holds
all of the actual read/write business logic and validation).

---

## 5. Database Structure (summary)

| Table                    | Purpose |
|---------------------------|---------|
| `users`                   | Accounts, bcrypt password hashes, roles (`owner`/`staff`) |
| `categories`               | Product categories |
| `suppliers`                | Supplier directory |
| `products`                 | Product catalog — quantity, prices, expiry, QR code, min. stock level |
| `inventory_transactions`  | Stock-in / stock-out ledger |
| `stock_history`            | Audit trail of every quantity change |
| `notifications`            | Auto-generated low-stock / expiring / expired alerts |
| `activity_logs`            | Security/audit log of logins, CRUD actions, exports |

All foreign keys, indexes, and sample data are defined in `database/schema.sql`.

---

## 6. Features

- **Authentication & RBAC** — bcrypt-hashed passwords, session-based login,
  CSRF protection on every form, brute-force login throttling, idle session
  timeout, and two roles (**Owner/Admin** vs **Inventory Staff**) enforced
  on every protected page.
- **Dashboard** — total products, total quantity, low-stock count, expiring
  count, expired count, total inventory value (owner only), a 7-day stock
  movement chart, an expiry-status doughnut chart, and recent activity /
  notifications panels.
- **Product Management** — full CRUD, auto-generated product codes and QR
  codes, search + filter by category/supplier/stock status/expiry status.
- **QR Code System** — a unique QR code is generated for every product
  (client-side rendered with QRCode.js), printable labels, and a browser
  camera scanner (via html5-qrcode) with a manual code-lookup fallback for
  devices without a working camera.
- **Stock Management** — Stock In / Stock Out forms that atomically update
  product quantity, write to the transaction ledger, and log to
  `stock_history`; full transaction history with date/type/keyword filters.
- **Expiration Monitoring** — automatic Green (safe) / Yellow (expiring
  soon, configurable window) / Red (expired) classification, surfaced on
  the dashboard, product list, and notifications.
- **Notifications** — auto-synced on every dashboard/notification page
  load for low stock, expiring, and expired products; mark-as-read support.
- **Reports** — Inventory, Stock Movement, Expiration, and Waste/Loss
  reports, each with date-range and keyword filtering, on-screen totals,
  **CSV export**, **Excel export** (`.xls`, opens natively in Microsoft
  Excel/LibreOffice — no external library required), and **Print/Save as
  PDF** via the browser's print dialog (see note below).
- **User Management** (Owner only) — create/edit staff and owner accounts,
  enable/disable accounts, reset passwords.
- **Settings** — change your own password, manage categories and suppliers.
- **Security** — PDO prepared statements everywhere (no raw SQL
  concatenation), CSRF tokens on every state-changing form, output escaping
  via `Validator::e()`, `password_hash()`/`password_verify()`, HttpOnly +
  SameSite session cookies, role-checked page access, `.htaccess` rules
  blocking direct access to `/app`, `/config`, `/database`, and `.sql`
  files.

### Note on PDF export
No PDF-generation library (e.g. TCPDF/Dompdf) is bundled, to keep the
project dependency-free and easy to run on stock XAMPP. Instead, every
report page has a **Print / Save as PDF** button that opens the browser's
native print dialog with a print-optimized stylesheet (sidebar/topbar
hidden automatically) — choosing "Save as PDF" as the destination produces
a clean PDF report. If you'd like a server-generated PDF instead, you can
drop in a library such as Dompdf via Composer and wire it into
`export.php`.

---

## 7. How to Use the System

1. **Log in** with the owner or staff account.
2. **Add categories/suppliers** first (Settings), then **add products**
   (Products → Add Product) — a product code and QR code are generated
   automatically.
3. **Print QR labels** from a product's "View" modal and attach them to
   physical stock, or rely on manual product codes.
4. When new deliveries arrive, use **Stock Management → Record Stock In**
   (or scan the QR code and use the product's quick-add).
5. As items are used/sold, use **Stock Management → Record Stock Out**.
6. Check the **Dashboard** and **Notifications** regularly for low-stock and
   expiry alerts.
7. Owners can pull **Reports** at any time, filter by date, and export to
   CSV/Excel or print to PDF for management review.

---

## 8. Known Limitations / Suggested Next Steps

- QR codes are rendered client-side (QRCode.js) rather than stored as image
  files; `storage/qrcodes/` is reserved if you'd like to add server-side QR
  image generation and persistent label files later.
- PDF export uses the browser print dialog rather than a server-side PDF
  library (see note above).
- Offline-first / PWA support was raised as a "nice to have" in the
  requirements elicitation but is out of scope for this version — the app
  currently requires an active connection to the server for every request.
