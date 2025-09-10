<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Masyarakat Digital</title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- External CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ========== CSS VARIABLES ========== */
        :root {
            /* Colors - Sage Green Theme */
            --primary: #2d4a3e;
            --primary-light: #3e5c50;
            --primary-lighter: #4f6d62;
            --secondary: #87a96b;
            --secondary-light: #9fb584;
            --accent: #b8c99c;
            --success: #6b8e5a;
            --danger: #d4756b;
            --warning: #c9a876;
            --info: #7ba098;
            
            /* Backgrounds */
            --bg-primary: #ffffff;
            --bg-secondary: #f8faf9;
            --bg-tertiary: #f3f6f4;
            --bg-dark: #2d4a3e;
            
            /* Text */
            --text-primary: #2d4a3e;
            --text-secondary: #5a6b60;
            --text-muted: #8a9388;
            
            /* Gradients - Sage Green */
            --gradient-primary: linear-gradient(135deg, #87a96b 0%, #6b8e5a 100%);
            --gradient-secondary: linear-gradient(135deg, #b8c99c 0%, #9fb584 100%);
            --gradient-tertiary: linear-gradient(135deg, #7ba098 0%, #6b8e5a 100%);
            --gradient-success: linear-gradient(135deg, #87a96b 0%, #a4c585 100%);
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            
            /* Spacing */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            
            /* Typography */
            --font-primary: 'Plus Jakarta Sans', system-ui, sans-serif;
            --font-secondary: 'Inter', system-ui, sans-serif;
        }
        
        /* ========== GLOBAL STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: var(--font-secondary);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* ========== UTILITY CLASSES ========== */
        .font-primary { font-family: var(--font-primary); }
        .font-secondary { font-family: var(--font-secondary); }
        
        .text-gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hover-float {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .hover-float:hover {
            transform: translateY(-10px) scale(1.02);
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(135, 169, 107, 0.4);
        }
        
        /* ========== NAVIGATION ========== */
        .navbar {
            background: rgba(45, 74, 62, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            padding: var(--space-md) 0;
        }
        
        .navbar.scrolled {
            background: rgba(45, 74, 62, 0.98);
            box-shadow: var(--shadow-lg);
        }
        
        .navbar-brand {
            font-family: var(--font-primary);
            font-weight: 700;
            font-size: 1.25rem;
            color: white !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }
        
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        
        .brand-subtitle {
            font-size: 0.75rem;
            font-weight: 400;
            opacity: 0.8;
        }
        
        /* ========== HERO SECTION ========== */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #87a96b 0%, #6b8e5a 100%);
            position: relative;
            display: flex;
            align-items: center;
            padding: 120px 0 60px;
            overflow: hidden;
        }
        
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }
        
        .hero-title {
            font-family: var(--font-primary);
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: var(--space-lg);
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: clamp(1.1rem, 2.5vw, 1.25rem);
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: var(--space-2xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* ========== BUTTONS ========== */
        .btn {
            border-radius: var(--radius-xl);
            font-weight: 600;
            font-family: var(--font-primary);
            padding: var(--space-md) var(--space-xl);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-lg);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            color: white;
        }
        
        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .btn-outline-light:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
            border-color: white;
        }
        
        .btn-lg {
            padding: var(--space-lg) var(--space-2xl);
            font-size: 1.1rem;
        }
        
        /* ========== FEATURE CARDS ========== */
        .feature-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: var(--space-2xl);
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--gradient-primary);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .feature-card:hover::before {
            transform: translateX(0);
        }
        
        .feature-card:hover {
            box-shadow: var(--shadow-2xl);
            transform: translateY(-15px) scale(1.02);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-xl);
            margin: 0 auto var(--space-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .feature-icon.primary {
            background: var(--gradient-primary);
        }
        
        .feature-icon.secondary {
            background: var(--gradient-secondary);
        }
        
        .feature-icon.tertiary {
            background: var(--gradient-tertiary);
        }
        
        .feature-icon.success {
            background: var(--gradient-success);
        }
        
        .feature-title {
            font-family: var(--font-primary);
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: var(--space-md);
            color: var(--text-primary);
        }
        
        .feature-text {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* ========== SERVICE CAROUSEL ========== */
        .service-carousel {
            margin-bottom: var(--space-2xl);
        }
        
        .carousel-item {
            transition: all 0.6s ease;
        }
        
        .service-card {
            background: var(--bg-primary);
            border-radius: var(--radius-2xl);
            padding: var(--space-2xl);
            text-align: center;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .service-icon {
            font-size: 4rem;
            margin-bottom: var(--space-lg);
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .service-title {
            font-family: var(--font-primary);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: var(--space-md);
            color: var(--text-primary);
        }
        
        .service-text {
            font-size: 1.1rem;
            color: var(--text-secondary);
        }
        
        /* ========== STATS SECTION ========== */
        .stats-section {
            background: var(--bg-secondary);
            padding: var(--space-2xl) 0;
        }
        
        .stat-card {
            text-align: center;
            padding: var(--space-xl);
        }
        
        .stat-number {
            font-family: var(--font-primary);
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: var(--space-sm);
        }
        
        .stat-label {
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }
        
        /* ========== FOOTER ========== */
        .footer {
            background: var(--bg-dark);
            color: white;
            padding: var(--space-2xl) 0 var(--space-lg);
            position: relative;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-xl);
            margin-bottom: var(--space-xl);
        }
        
        .footer-section h5 {
            font-family: var(--font-primary);
            font-weight: 600;
            margin-bottom: var(--space-md);
            color: white;
        }
        
        .footer-section p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: var(--space-sm);
        }
        
        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section a:hover {
            color: var(--secondary-light);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: var(--space-lg);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* ========== ANIMATIONS ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-on-scroll {
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }
        
        .animate-on-scroll.delay-1 { animation-delay: 0.2s; }
        .animate-on-scroll.delay-2 { animation-delay: 0.4s; }
        .animate-on-scroll.delay-3 { animation-delay: 0.6s; }
        .animate-on-scroll.delay-4 { animation-delay: 0.8s; }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 0 40px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .btn-group {
                flex-direction: column;
                gap: var(--space-md);
            }
            
            .btn-lg {
                width: 100%;
            }
            
            .feature-card {
                padding: var(--space-lg);
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            .brand-icon {
                width: 35px;
                height: 35px;
            }
        }
        
        /* ========== SCROLLBAR ========== */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 4px;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            background: var(--gradient-primary);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <div class="brand-icon">
                    <i class="bi bi-building-gear"></i>
                </div>
                <div class="brand-text">
                    <span>Pengaduan Digital</span>
                    <small class="brand-subtitle">Kelurahan Modern</small>
                </div>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list text-white fs-3"></i>
            </button>
            
            <!-- Update the navigation links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link text-white px-3" href="index.php">Beranda</a>
                    <a class="nav-link text-white px-3" href="#layanan">Layanan</a>
                    <a class="nav-link text-white px-3" href="#tentang">Tentang</a>
                    <a class="nav-link text-white px-3" href="#kontak">Kontak</a>
                    <a class="nav-link text-white px-3" href="index.php?page=login">Masuk</a>
                    <a class="nav-link text-white px-3" href="index.php?page=registrasi">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-bg"></div>
        
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <!-- Service Carousel -->
                    <div id="serviceCarousel" class="carousel slide service-carousel animate-on-scroll" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="service-card">
                                    <i class="bi bi-megaphone-fill service-icon"></i>
                                    <h3 class="service-title">Sampaikan Keluhan Anda</h3>
                                    <p class="service-text">Platform digital untuk menyampaikan pengaduan dengan mudah dan cepat</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="service-card">
                                    <i class="bi bi-clock-history service-icon"></i>
                                    <h3 class="service-title">Pantau Status Real-time</h3>
                                    <p class="service-text">Lacak progress penanganan laporan Anda secara transparan</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Carousel Indicators -->
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#serviceCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#serviceCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#serviceCarousel" data-bs-slide-to="2"></button>
                        </div>
                    </div>

                    <!-- Hero Content -->
                    <div class="hero-content animate-on-scroll delay-1">
                        <h1 class="hero-title">
                            Sistem Pengaduan Masyarakat
                            <span class="d-block">Era Digital</span>
                        </h1>
                        <p class="hero-subtitle">
                            Tingkatkan kualitas pelayanan publik melalui partisipasi aktif masyarakat dalam ekosistem digital yang terintegrasi, responsif, dan transparan.
                        </p>
                        
                        <!-- Update the hero section buttons -->
                        <div class="d-flex justify-content-center gap-3 btn-group animate-on-scroll delay-2">
                            <a href="index.php?page=registrasi" class="btn btn-primary btn-lg hover-glow">
                                <i class="bi bi-person-plus-fill me-2"></i>
                                Daftar Sekarang
                            </a>
                            <a href="index.php?page=login" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="layanan" class="py-5 bg-light">
        <div class="container">
            <!-- Section Header -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold font-primary mb-4 animate-on-scroll text-gradient">
                        Mengapa Memilih Platform Kami?
                    </h2>
                    <p class="lead text-muted animate-on-scroll delay-1">
                        Kami menyediakan solusi digital terdepan untuk meningkatkan komunikasi antara pemerintah dan masyarakat
                    </p>
                </div>
            </div>
            
            <!-- Feature Cards -->
            <div class="row g-4 justify-content-center">
                <!-- Feature 1 -->
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card hover-float animate-on-scroll delay-1">
                        <div class="feature-icon primary">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h5 class="feature-title">Respons Kilat</h5>
                        <p class="feature-text">
                            Sistem notifikasi real-time memastikan pengaduan Anda segera ditangani oleh petugas berwenang
                        </p>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card hover-float animate-on-scroll delay-2">
                        <div class="feature-icon secondary">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h5 class="feature-title">Tracking Transparan</h5>
                        <p class="feature-text">
                            Pantau progress penanganan laporan dari tahap awal hingga selesai dengan dashboard interaktif
                        </p>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card hover-float animate-on-scroll delay-3">
                        <div class="feature-icon tertiary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5 class="feature-title">Komunitas Aktif</h5>
                        <p class="feature-text">
                            Bergabung dengan ribuan warga yang telah merasakan pelayanan publik yang lebih baik
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="kontak">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h5>Alamat Kami</h5>
                    <p><i class="bi bi-geo-alt-fill me-2"></i>Jl. Pd. Jaya, Pd. Jaya, Kec. Pd. Aren, Kota Tangerang Selatan, Banten 15224</p>
                </div>
                <div class="footer-section">
                    <h5>Ikuti Kami</h5>
                    <p>
                        <a href="https://www.instagram.com/pondok.jaya?utm_source=ig_web_button_share_sheet&igsh=MThld3VmZzFnOWRiNA==" class="text-reset">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="footer-bottom">
                &copy; 2025 Pengaduan Digital. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="btn btn-primary btn-lg rounded-circle back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth',
                    offsetTop: 100
                });
            });
        });


        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });


        document.addEventListener('DOMContentLoaded', () => {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const updateCount = () => {
                    const target = +stat.getAttribute('data-target');
                    const count = +stat.innerText;
                    const increment = Math.ceil(target / 200);

                    if (count < target) {
                        stat.innerText = count + increment;
                        setTimeout(updateCount, 10);
                    } else {
                        stat.innerText = target;
                    }
                };

                updateCount();
            });
        });

        const backToTopBtn = document.querySelector('.back-to-top');
        backToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>