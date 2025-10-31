<?php
require_once 'controllers/AuthController.php';
require_once 'utils/Session.php';

Session::start();

$error = '';
$success = '';

if (Session::isLoggedIn()) {
    header("Location: /dashboard/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama';
    } else {
        $authController = new AuthController();
        $result = $authController->register($email, $password);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Admin Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #2c3e50;
            --bs-primary-rgb: 44, 62, 80;
            --bs-primary-hover: #34495e;
        }

        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-primary:hover {
            background-color: var(--bs-primary-hover, #0b5ed7);
            border-color: var(--bs-primary-hover, #0a58ca);
            color: #fff;
        }

        .card {
            border: none;
            border-radius: 15px;
        }

        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-box-seam display-4 text-primary"></i>
                            <h3 class="mt-3 fw-bold text-primary">Registrasi Admin Gudang</h3>
                            <p class="text-muted">Buat akun baru untuk mengelola gudang</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-primary"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-primary"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control border-start-0" required minlength="6">
                                </div>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill text-primary"></i>
                                    </span>
                                    <input type="password" name="confirm_password" class="form-control border-start-0" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-person-plus me-2"></i>Daftar
                            </button>
                        </form>

                        <hr class="my-4">
                        <div class="text-center">
                            <small>Sudah punya akun?
                                <a href="/login.php" class="text-primary text-decoration-none fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Login di sini
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>