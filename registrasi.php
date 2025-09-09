<div class="container-fluid d-flex align-items-center justify-content-center min-vh-100" 
     style="background: linear-gradient(135deg, #e8f5e8 0%, #d4e6d4 100%);">
  <div class="row w-100 justify-content-center">
    <div class="col-sm-9 col-md-7 col-lg-5">
      <!-- Card dengan efek glass -->
      <div class="card border-0 shadow-lg rounded-4 bg-white bg-opacity-75 backdrop-blur-lg animate__animated animate__fadeInDown">
        <div class="card-body p-4 p-sm-5">
          <!-- Judul -->
          <h3 class="card-title text-center fw-bold fs-3 mb-4" style="color: #6b8e6b;">Registrasi Akun</h3>

          <!-- Form -->
          <form action="" method="POST">
            <div class="mb-3">
              <label for="nik" class="form-label fw-semibold" style="color: #5a7a5a;">NIK</label>
              <input type="number" class="form-control rounded-pill shadow-sm" name="nik" id="nik" placeholder="Masukan NIK Kamu" required autocomplete="off" autofocus style="border: 2px solid #a3c1a3; focus-border-color: #6b8e6b;">
            </div>
            <div class="mb-3">
              <label for="nama_lengkap" class="form-label fw-semibold" style="color: #5a7a5a;">Nama Lengkap</label>
              <input type="text" class="form-control rounded-pill shadow-sm" name="nama_lengkap" id="nama_lengkap" placeholder="Masukan Nama Lengkap" required autocomplete="off" style="border: 2px solid #a3c1a3;">
            </div>
            <div class="mb-3">
              <label for="username" class="form-label fw-semibold" style="color: #5a7a5a;">Username</label>
              <input type="text" class="form-control rounded-pill shadow-sm" name="username" id="username" placeholder="Masukan Username" required autocomplete="off" style="border: 2px solid #a3c1a3;">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label fw-semibold" style="color: #5a7a5a;">Password</label>
              <input type="password" class="form-control rounded-pill shadow-sm" name="password" id="password" placeholder="Masukan Password" required autocomplete="off" style="border: 2px solid #a3c1a3;">
            </div>
            <div class="mb-4">
              <label for="telp" class="form-label fw-semibold" style="color: #5a7a5a;">Nomor Telepon</label>
              <input type="number" class="form-control rounded-pill shadow-sm" name="telp" id="telp" placeholder="Masukan Nomor Telepon" required autocomplete="off" style="border: 2px solid #a3c1a3;">
            </div>

            <!-- Tombol -->
            <div class="d-grid mb-3">
              <button type="submit" name="kirim" class="btn rounded-pill py-2 shadow-sm hover-scale" style="background-color: #6b8e6b; border-color: #6b8e6b; color: white;">DAFTAR</button>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <a href="index.php" class="btn rounded-pill w-100 shadow-sm hover-scale" style="background-color: #8a9b8a; border-color: #8a9b8a; color: white;">Kembali</a>
              </div>
              <div class="col-6">
                <a href="index.php?page=login" class="btn rounded-pill w-100 shadow-sm hover-scale" style="background-color: #7a987a; border-color: #7a987a; color: white;">Login</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Animate.css untuk animasi -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body {
    font-family: 'Poppins', sans-serif;
  }
  .hover-scale {
    transition: all 0.3s ease-in-out;
  }
  .hover-scale:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
  }
  .card {
    backdrop-filter: blur(15px);
  }
  
  /* Custom focus styles for sage green theme */
  .form-control:focus {
    border-color: #6b8e6b !important;
    box-shadow: 0 0 0 0.2rem rgba(107, 142, 107, 0.25) !important;
  }
  
  /* Hover effects for buttons */
  .btn:hover {
    opacity: 0.9;
    transform: scale(1.05);
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Simulate PHP functionality for demo
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const nik = document.getElementById('nik').value;
    const nama = document.getElementById('nama_lengkap').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const telp = document.getElementById('telp').value;
    
    // Simple validation
    if (nik && nama && username && password && telp) {
        alert('Data Berhasil Ditambahkan');
        // In real implementation: document.location.href='index.php?page=login';
    } else {
        alert('Data Gagal Ditambahkan - Mohon lengkapi semua field');
        // In real implementation: document.location.href='index.php?page=registrasi';
    }
});
</script>