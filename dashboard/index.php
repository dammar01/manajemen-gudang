<?php
require_once '../utils/Session.php';
require_once '../utils/helpers.php';
require_once '../config/database.php';
require_once '../models/Product.php';
require_once '../models/User.php';
require_once '../utils/Validator.php';

Session::requireLogin();

$db = (new Database())->connect();
$product = new Product($db);
$user = new User($db);
$validator = new Validator();

$currentUserId = (int) Session::get('user_id');

if (!$user->getUserById($currentUserId)) {
    Session::destroy();
    header('Location: /login.php');
    exit;
}

$message = '';
$error = '';

// Handle Product CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_product':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);

            if ($name === '') {
                $error = 'Nama produk wajib diisi';
                break;
            }
            if ($quantity === false) {
                $error = 'Jumlah produk tidak valid';
                break;
            }
            if ($price === false || $price < 0) {
                $error = 'Harga produk tidak valid';
                break;
            }

            $product->name = $name;
            $product->description = $description;
            $product->quantity = (int) $quantity;
            $product->price = round((float) $price, 2);
            $product->created_by = $currentUserId;

            if ($product->create()) {
                $message = 'Produk berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan produk';
            }
            break;

        case 'update_product':
            $productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);

            if (!$productId) {
                $error = 'Data produk tidak valid';
                break;
            }
            if (!$product->getById($productId) || (int) $product->created_by !== $currentUserId) {
                $error = 'Produk tidak ditemukan';
                break;
            }
            if ($name === '') {
                $error = 'Nama produk wajib diisi';
                break;
            }
            if ($quantity === false) {
                $error = 'Jumlah produk tidak valid';
                break;
            }
            if ($price === false || $price < 0) {
                $error = 'Harga produk tidak valid';
                break;
            }

            $product->id = $productId;
            $product->name = $name;
            $product->description = $description;
            $product->quantity = (int) $quantity;
            $product->price = round((float) $price, 2);

            if ($product->update()) {
                $message = 'Produk berhasil diperbarui';
            } else {
                $error = 'Gagal mengupdate produk';
            }
            break;

        case 'delete_product':
            $productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$productId) {
                $error = 'Data produk tidak valid';
                break;
            }
            if (!$product->getById($productId) || (int) $product->created_by !== $currentUserId) {
                $error = 'Produk tidak ditemukan';
                break;
            }
            if ($product->delete($productId)) {
                $message = 'Produk berhasil dihapus';
            } else {
                $error = 'Gagal menghapus produk';
            }
            break;

        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $tempUser = new User($db);
            $tempUser->email = $user->email;
            $tempUser->password = $current_password;

            if (!$tempUser->login()) {
                $error = 'Password lama tidak sesuai';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru dan konfirmasi tidak sama';
            } elseif (!$validator->validatePassword($new_password)) {
                $error = 'Password minimal 6 karakter';
            } else {
                $user->password = $new_password;
                if ($user->updatePassword()) {
                    $message = 'Password berhasil diubah';
                } else {
                    $error = 'Gagal mengubah password';
                }
            }
            break;

        case 'update_profile':
            $new_email = trim($_POST['email'] ?? '');
            if (!$validator->validateEmail($new_email)) {
                $error = 'Format email tidak valid';
                break;
            }
            $new_email = strtolower($new_email);

            if ($new_email === strtolower($user->email)) {
                $message = 'Tidak ada perubahan pada profil';
                break;
            }

            $emailChecker = new User($db);
            $emailChecker->email = $new_email;
            if ($emailChecker->emailExists() && (int) $emailChecker->id !== $currentUserId) {
                $error = 'Email sudah terdaftar';
                break;
            }

            if ($user->updateProfile($new_email)) {
                $user->email = $new_email;
                Session::set('user_email', $new_email);
                $message = 'Profil berhasil diperbarui';
            } else {
                $error = 'Gagal memperbarui profil';
            }
            break;
    }
}

// Get all products
$products = $product->getAll($currentUserId);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Gudang</title>
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

        .navbar-brand {
            font-weight: 700;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
            border-bottom: 3px solid var(--bs-primary);
        }

        .table th {
            border-top: none;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-box-seam me-2"></i>Admin Gudang
            </a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <a href="/logout.php" class="btn btn-outline-light btn-sm my-auto">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#products">
                    <i class="bi bi-grid me-1"></i>Manajemen Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#profile">
                    <i class="bi bi-person me-1"></i>Profil Saya
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab Produk -->
            <div id="products" class="tab-pane fade show active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-primary">
                        <i class="bi bi-grid me-2"></i>Daftar Produk
                    </h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Produk
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = $products->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill"><?php echo (int) $row['quantity']; ?></span>
                                            </td>
                                            <td class="fw-semibold text-success"><?php echo htmlspecialchars(formatRupiah($row['price']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick='editProduct(<?php echo json_encode([
                                                                                                                                        'id' => (int) $row['id'],
                                                                                                                                        'name' => $row['name'],
                                                                                                                                        'description' => $row['description'],
                                                                                                                                        'quantity' => (int) $row['quantity'],
                                                                                                                                        'price' => round((float) $row['price'], 2),
                                                                                                                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                    <input type="hidden" name="action" value="delete_product">
                                                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash me-1"></i>Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Profil -->
            <div id="profile" class="tab-pane fade">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-gear me-2"></i>Pengaturan Profil
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-envelope text-primary"></i>
                                            </span>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Terdaftar sejak: <?php echo htmlspecialchars(formatDate($user->created_at), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-shield-lock me-2"></i>Ubah Password
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Password Lama</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-lock text-primary"></i>
                                            </span>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Password Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-lock-fill text-primary"></i>
                                            </span>
                                            <input type="password" name="new_password" class="form-control" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-lock-fill text-primary"></i>
                                            </span>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat me-1"></i>Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Produk -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Produk
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_product">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="number" name="quantity" class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Harga</label>
                            <input type="number" name="price" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Produk
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Produk</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <input type="number" name="quantity" id="edit_quantity" class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Harga</label>
                            <input type="number" name="price" id="edit_price" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_quantity').value = product.quantity;
            document.getElementById('edit_price').value = product.price;
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        }
    </script>
</body>

</html>