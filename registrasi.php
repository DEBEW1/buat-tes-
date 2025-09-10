<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #e8f5e8 0%, #d4e6d4 100%);
            font-family: 'Poppins', sans-serif;
        }
        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }
        .card-title {
            color: #6b8e6b;
            font-weight: bold;
        }
        .form-control {
            border-radius: 15px;
            border: 2px solid #a3c1a3;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #6b8e6b !important;
            box-shadow: 0 0 0 0.2rem rgba(107, 142, 107, 0.25) !important;
        }
        .form-label {
            color: #5a7a5a;
            font-weight: 600;
        }
        .btn {
            border-radius: 15px;
            font-weight: 500;
            padding: 12px;
            transition: all 0.3s ease-in-out;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }
        .btn-success {
            background-color: #6b8e6b;
            border-color: #6b8e6b;
        }
        .btn-secondary {
            background-color: #8a9b8a;
            border-color: #8a9b8a;
        }
        .btn-primary {
            background-color: #7a987a;
            border-color: #7a987a;
        }
        .alert {
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-9 col-md-7 col-lg-5">
                <div class="card border-0 shadow-lg animate__animated animate__fadeInDown">
                    <div class="card-body p-4 p-sm-5">
                        <h3 class="card-title text-center mb-4">Registrasi Akun</h3>
                        
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="config/aksi_registrasi.php" method="POST">
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" name="nik" id="nik" 
                                       placeholder="Masukan NIK 16 digit" required autocomplete="off" 
                                       autofocus maxlength="16" pattern="[0-9]{16}"
                                       value="<?php echo isset($_SESSION['old']['nik']) ? $_SESSION['old']['nik'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" 
                                       placeholder="Masukan Nama Lengkap" required autocomplete="off" 
                                       maxlength="35"
                                       value="<?php echo isset($_SESSION['old']['nama_lengkap']) ? $_SESSION['old']['nama_lengkap'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" 
                                       placeholder="Masukan Username" required autocomplete="off" 
                                       maxlength="25"
                                       value="<?php echo isset($_SESSION['old']['username']) ? $_SESSION['old']['username'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" 
                                       placeholder="Masukan Password (minimal 6 karakter)" required 
                                       autocomplete="off" minlength="6">
                            </div>
                            <div class="mb-4">
                                <label for="telp" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" name="telp" id="telp" 
                                       placeholder="Masukan Nomor Telepon" required autocomplete="off" 
                                       maxlength="13" pattern="[0-9]+"
                                       value="<?php echo isset($_SESSION['old']['telp']) ? $_SESSION['old']['telp'] : ''; ?>">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" name="kirim" class="btn btn-success">DAFTAR</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="index.php" class="btn btn-secondary w-100">Kembali</a>
                                </div>
                                <div class="col-6">
                                    <a href="index.php?page=login" class="btn btn-primary w-100">Login</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi NIK hanya angka
        document.getElementById('nik').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });
        
        // Validasi nomor telepon hanya angka
        document.getElementById('telp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });
        
        // Validasi username hanya huruf, angka, underscore
        document.getElementById('username').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
        });
    </script>
</body>
</html>

<?php
// Hapus session old data setelah digunakan
if (isset($_SESSION['old'])) {
    unset($_SESSION['old']);
}
?>