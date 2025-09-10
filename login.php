<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #87a96b 0%, #9db87d 100%);
        font-family: 'Poppins', sans-serif;
    }
    .card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }
    .card-title {
        font-weight: 600;
        color: #333;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #ddd;
        padding: 12px;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #87a96b;
        box-shadow: 0 0 8px rgba(135, 169, 107, 0.6);
    }
    .btn {
        border-radius: 12px;
        font-weight: 500;
        padding: 10px;
        transition: all 0.3s ease-in-out;
    }
    .btn-success {
        background: linear-gradient(135deg, #87a96b, #9db87d);
        border: none;
        color: #fff;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #7a9960, #8faa70);
        transform: scale(1.05);
    }
    .btn-danger {
        background: linear-gradient(135deg, #a9876b, #b89d7d);
        border: none;
    }
    .btn-danger:hover {
        background: linear-gradient(135deg, #997960, #aa8f70);
        transform: scale(1.05);
    }
    .btn-primary {
        background: linear-gradient(135deg, #6b8a87, #7d9db8);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #607a79, #708faa);
        transform: scale(1.05);
    }
    .alert {
        border-radius: 12px;
    }
</style>

</head>
<body>
<div class="container">
    <div class="row d-flex align-items-center" style="height:100vh;">
        <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
            <div class="card border-0 shadow rounded-3 my-auto">
                <div class="card-body p-4 p-sm-5">
                    <h3 class="card-title text-center mb-3 fw-light fs-3">Log In</h3>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="config/aksi_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" class="form-control" name="username" id="username" 
                                   placeholder="Masukan Username" required autofocus autocomplete="off"
                                   value="<?php echo isset($_SESSION['old_username']) ? $_SESSION['old_username'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" class="form-control" name="password" id="password" 
                                   placeholder="Masukan Password" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="level">Login Sebagai</label>
                            <select class="form-control" name="level" id="level" required>
                                <option value="masyarakat" <?php echo (isset($_SESSION['old_level']) && $_SESSION['old_level'] == 'masyarakat') ? 'selected' : ''; ?>>Masyarakat</option>
                                <option value="petugas" <?php echo (isset($_SESSION['old_level']) && $_SESSION['old_level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="kirim" class="btn btn-success">LOGIN</button>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="d-grid">
                                    <a href="index.php" class="btn btn-danger">Kembali</a>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-grid">
                                    <a href="index.php?page=registrasi" class="btn btn-primary">Registrasi</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Hapus session old data setelah digunakan
if (isset($_SESSION['old_username'])) {
    unset($_SESSION['old_username']);
}
if (isset($_SESSION['old_level'])) {
    unset($_SESSION['old_level']);
}
?>