<?php
// masyarakat/pengaduan.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengaduan - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2d4a3e;
            --secondary: #87a96b;
            --success: #6b8e5a;
            --warning: #c9a876;
            --info: #7ba098;
        }
        
        body {
            background-color: #f8faf9;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(135, 169, 107, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #234035 0%, #7a9960 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .file-upload {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: var(--secondary);
            background-color: rgba(135, 169, 107, 0.1);
        }
        
        .file-upload.dragover {
            border-color: var(--secondary);
            background-color: rgba(135, 169, 107, 0.1);
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Dashboard Masyarakat</h5>
                        <p class="text-white-50 mb-0">Selamat datang, <?php echo $_SESSION['nama']; ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="pengaduan.php">
                                <i class="bi bi-file-earmark-text"></i> Buat Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat.php">
                                <i class="bi bi-clock-history"></i> Riwayat Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="../config/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Buat Pengaduan Baru</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="riwayat.php" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> Lihat Riwayat
                        </a>
                    </div>
                </div>
                
                <!-- Alert Messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Form Pengaduan -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            Form Pengaduan Masyarakat
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="proses_pengaduan.php" method="POST" enctype="multipart/form-data" id="pengaduanForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tgl_pengaduan" class="form-label">
                                        <i class="bi bi-calendar3"></i> Tanggal Pengaduan
                                    </label>
                                    <input type="date" class="form-control" id="tgl_pengaduan" name="tgl_pengaduan" 
                                           value="<?php echo date('Y-m-d'); ?>" required readonly>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nik" class="form-label">
                                        <i class="bi bi-person-badge"></i> NIK Pelapor
                                    </label>
                                    <input type="text" class="form-control" id="nik" name="nik" 
                                           value="<?php echo $_SESSION['user_id']; ?>" required readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="judul_laporan" class="form-label">
                                    <i class="bi bi-chat-square-text"></i> Judul Laporan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="judul_laporan" name="judul_laporan" 
                                       placeholder="Masukkan judul singkat untuk laporan Anda..." 
                                       required maxlength="100">
                                <div class="form-text">Maksimal 100 karakter</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="isi_laporan" class="form-label">
                                    <i class="bi bi-file-text"></i> Detail Laporan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="isi_laporan" name="isi_laporan" rows="8" 
                                          placeholder="Jelaskan detail permasalahan yang ingin Anda laporkan...&#10;&#10;Contoh:&#10;- Apa yang terjadi?&#10;- Dimana lokasi kejadian?&#10;- Kapan terjadi?&#10;- Siapa yang terlibat?&#10;- Dampak yang ditimbulkan?" 
                                          required maxlength="1000"></textarea>
                                <div class="form-text">
                                    <span id="charCount">0</span>/1000 karakter
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-camera"></i> Foto Pendukung (Opsional)
                                </label>
                                <div class="file-upload" id="fileUpload">
                                    <input type="file" class="d-none" id="foto" name="foto" 
                                           accept="image/jpeg,image/jpg,image/png" onchange="previewImage(this)">
                                    <div id="uploadContent">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-3"></i>
                                        <p class="mb-2">Klik untuk memilih foto atau drag & drop</p>
                                        <small class="text-muted">Format: JPG, JPEG, PNG | Maksimal: 2MB</small>
                                    </div>
                                    <div id="imagePreview" class="mt-3" style="display: none;">
                                        <img id="preview" class="preview-image" src="" alt="Preview">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                                                <i class="bi bi-trash"></i> Hapus Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="bg-light p-3 rounded mb-4">
                                        <h6 class="text-primary mb-2">
                                            <i class="bi bi-info-circle"></i> Informasi Penting:
                                        </h6>
                                        <ul class="mb-0 small">
                                            <li>Pastikan laporan yang Anda sampaikan akurat dan faktual</li>
                                            <li>Sertakan informasi selengkap mungkin untuk mempercepat penanganan</li>
                                            <li>Foto pendukung akan membantu petugas memahami permasalahan</li>
                                            <li>Status pengaduan dapat dipantau melalui menu Riwayat Pengaduan</li>
                                            <li>Pengaduan akan ditanggapi maksimal 3x24 jam</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Pengaduan
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Form
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter
        const isiLaporan = document.getElementById('isi_laporan');
        const charCount = document.getElementById('charCount');
        
        isiLaporan.addEventListener('input', function() {
            const remaining = this.value.length;
            charCount.textContent = remaining;
            
            if (remaining > 900) {
                charCount.classList.add('text-warning');
            } else if (remaining > 950) {
                charCount.classList.remove('text-warning');
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-warning', 'text-danger');
            }
        });
        
        // File upload drag & drop
        const fileUpload = document.getElementById('fileUpload');
        const fileInput = document.getElementById('foto');
        
        fileUpload.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.classList.add('dragover');
        });
        
        fileUpload.addEventListener('dragleave', () => {
            fileUpload.classList.remove('dragover');
        });
        
        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        });
        
        // Image preview
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                // Validasi ukuran file (2MB = 2 * 1024 * 1024 bytes)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB');
                    input.value = '';
                    return;
                }
                
                // Validasi tipe file
                if (!file.type.match('image/(jpeg|jpg|png)')) {
                    alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('uploadContent').style.display = 'none';
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        function removeImage() {
            document.getElementById('foto').value = '';
            document.getElementById('uploadContent').style.display = 'block';
            document.getElementById('imagePreview').style.display = 'none';
        }
        
        // Form validation
        document.getElementById('pengaduanForm').addEventListener('submit', function(e) {
            const judulLaporan = document.getElementById('judul_laporan').value.trim();
            const isiLaporan = document.getElementById('isi_laporan').value.trim();
            
            if (judulLaporan.length < 10) {
                e.preventDefault();
                alert('Judul laporan minimal 10 karakter');
                return;
            }
            
            if (isiLaporan.length < 20) {
                e.preventDefault();
                alert('Detail laporan minimal 20 karakter');
                return;
            }
            
            // Konfirmasi sebelum submit
            if (!confirm('Apakah Anda yakin ingin mengirim pengaduan ini?')) {
                e.preventDefault();
            }
        });
        
        // Reset form handler
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mengosongkan form?')) {
                removeImage();
                charCount.textContent = '0';
                charCount.classList.remove('text-warning', 'text-danger');
            } else {
                return false;
            }
        });
    </script>
</body>
</html>