<?php
require_once 'controllers/AuthController.php';

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $authController = new AuthController();
    $result = $authController->activateAccount($token);

    $message = $result['message'];
    $success = $result['success'];
} else {
    $message = 'Token aktivasi tidak ditemukan';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun - Admin Gudang</title>
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
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <?php if ($success): ?>
                                <i class="bi bi-check-circle display-1 text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-x-circle display-1 text-danger"></i>
                            <?php endif; ?>
                        </div>

                        <h3 class="mb-4 fw-bold text-primary">Aktivasi Akun</h3>

                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <p class="text-muted mb-4">Akun Anda telah berhasil diaktifkan. Silakan login untuk melanjutkan.</p>
                            <a href="/login.php" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                            </a>
                        <?php else: ?>
                            <div class="alert alert-danger d-flex align-items-center justify-content-center">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <p class="text-muted mb-4">Silakan hubungi administrator jika Anda mengalami masalah.</p>
                            <a href="/register.php" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                                <i class="bi bi-person-plus me-2"></i>Daftar Ulang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>