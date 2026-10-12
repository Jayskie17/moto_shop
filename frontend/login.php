<?php
require_once __DIR__ . '/../backend/includes/functions.php';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sign In – R'A Motor Parts and Accesories</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container-split">
        <!-- Left Column: Form -->
        <div class="login-form-panel">
            <div class="brand-header">
                <span class="brand-name">R'A Motor Parts</span>
                <span class="brand-tagline">and Accessories Shop</span>
            </div>

            <div class="welcome-heading">
                <h2>Welcome back!</h2>
                <p>Pick up where you left off, and manage your shop.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="login-alert-error">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="/motor_shop/backend/modules/auth/login_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary btn-login-submit">Log In &rarr;</button>
            </form>
        </div>

        <!-- Right Column: Visual Logo Banner -->
        <div class="login-banner-panel">
            <div class="banner-badge">
               <img src="assets/images/logo motorshop.png" alt="R'A Motor Parts" class="banner-img">

        </div>
    </div>
</body>

