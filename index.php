<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>APEM | Aplikasi Pengaduan Masyarakat</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Argon Dashboard CSS -->
    <link id="pagestyle" href="assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />

    <style>
        /* Media query untuk layar desktop */
        @media screen and (min-width: 768px) {
            .footer {
                position: fixed;
                left: 0;
                bottom: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-light">
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];

        switch ($page) {
            case 'login':
                include 'login.php';
                break;
            case 'registrasi':
                include 'registrasi.php';
                break;

            default:
                echo "HALAMAN TAK TERSEDIA";
                break;
        }
    } else {
        include 'home.php';
    }
    ?>

    <!-- Scripts -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>