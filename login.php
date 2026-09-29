<?php
require_once 'config.php';
$pageTitle = 'User Login - ' . APP_NAME;
$activePage = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
            $stmt = db()->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
            try {
                $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
                $message = 'Registration complete. You can login now.';
            } catch (PDOException $e) {
                $message = 'This email is already registered.';
            }
        } else {
            $message = 'Please enter name, valid email, and password with at least 6 characters.';
        }
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $message = 'Login successful. Welcome, ' . $user['name'] . '.';
        } else {
            $message = 'Invalid user email or password.';
        }
    }
}

include 'includes/header.php';
?>
<section class="py-5 band">
    <div class="container py-4">
        <h1 class="section-title">User Login</h1>
        <div class="accent-line mb-4"></div>
        <p class="lead">Users can register and login. Admin can view registered user details.</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
        <div class="row g-4">
            <div class="col-lg-6">
                <form class="auth-box p-4" method="post">
                    <h3>Login</h3>
                    <input type="hidden" name="action" value="login">
                    <label class="form-label mt-3">Email</label>
                    <input class="form-control" type="email" name="email" required>
                    <label class="form-label mt-3">Password</label>
                    <input class="form-control" type="password" name="password" required>
                    <button class="btn btn-primary mt-4" type="submit">Login</button>
                </form>
            </div>
            <div class="col-lg-6">
                <form class="auth-box p-4" method="post">
                    <h3>Register</h3>
                    <input type="hidden" name="action" value="register">
                    <label class="form-label mt-3">Full Name</label>
                    <input class="form-control" name="name" required>
                    <label class="form-label mt-3">Email</label>
                    <input class="form-control" type="email" name="email" required>
                    <label class="form-label mt-3">Phone</label>
                    <input class="form-control" name="phone">
                    <label class="form-label mt-3">Password</label>
                    <input class="form-control" type="password" name="password" minlength="6" required>
                    <button class="btn btn-danger mt-4" type="submit">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
