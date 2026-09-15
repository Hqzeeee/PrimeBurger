# PrimeBurger
Inventory Management System with QR based tracking for spoilage

## Setup

1. Create the database and schema:
   ```
   mysql -u root -p < database/primeburger.sql
   ```
2. (Optional) Load a default admin/owner login:
   ```
   mysql -u root -p < database/seed.sql
   ```
   Sample product data has been left out for now — `seed.sql` currently
   only inserts the default admin account so the login/registration
   pages have something to test against.
3. Update `config/database.php` with your MySQL credentials if they
   differ from the defaults.
4. Serve the project with PHP (e.g. `php -S localhost:8000`) and open
   `login.php`.

## Login & Registration

The app is session-protected — `dashboard.php` redirects to `login.php`
for anyone who isn't signed in.

- **`login.php`** — sign-in page for both the owner and staff.
- **`register.php`** — lets employees create their own account. New
  accounts are always created with the `staff` role; owner/admin
  accounts are managed directly in the `users` table (see
  `database/seed.sql`).
- **`logout.php`** — ends the session.

If you ran `database/seed.sql`, you can sign in as the owner with:

- **Username:** `admin`
- **Password:** `admin123`

Change this password (or add your own user in the `users` table) before
using this in anything beyond local development.
