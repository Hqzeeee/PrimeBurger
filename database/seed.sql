USE primeburger_inventory;


/*
|--------------------------------------------------------------------------
| Default Admin (Owner) User
|--------------------------------------------------------------------------
|
| Username: admin
| Password: admin123
|
| The password hash below was generated with PHP's password_hash()
| using the default (bcrypt) algorithm. Change this password after
| your first login — see login.php / users table.
|
| Sample product data has been removed for now — this file only seeds
| the default owner account so the login/registration pages have
| something to test against.
|
*/

INSERT INTO users
    (username, password_hash, full_name, role)
VALUES
    (
        'admin',
        '$2y$10$hEeN94FNyLvJqf.CCjMIleaJlCht7s1JoL/fqhArGIXSjfksAnFeC',
        'PrimeBurger Owner',
        'admin'
    );
