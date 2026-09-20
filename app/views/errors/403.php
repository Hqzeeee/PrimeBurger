<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Access Denied · PrimeBurger IMS</title>
<link rel="stylesheet" href="<?= defined('APP_URL') ? APP_URL : '' ?>/assets/css/app.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card" style="text-align:center;">
    <div style="font-size:40px;">🚫</div>
    <h1 class="auth-title">Access Denied</h1>
    <p class="auth-sub">Your account role does not have permission to view this page.</p>
    <a class="btn btn-primary btn-block" href="<?= defined('APP_URL') ? APP_URL : '' ?>/dashboard.php">Back to Dashboard</a>
  </div>
</div>
</body>
</html>
