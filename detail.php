<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendakian - Gunung Butak</title>
    <link rel="icon" type="image/x-icon" href="assets/images/LOGO_WEB.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="bg-white">
    <!-- Main Content -->
    <div class="flex-col main-content">
        <!-- Header -->
        <header class="header-shadow bg-primary text-white py-4 px-6 static">
            <div class="container auto-margin">
                <div class="flex-display items-center justify-between">
                    <div class="flex-display items-center">
                        <img src="assets/images/LOGO_WEB.png" alt="Logo Gunung Butak" class="w-10 h-10 mr-3">
                        <h1 class="text-xl font-bold" data-translate-key="header_title">Gunung Butak | Reservasi Pendakian</h1>
                    </div>
                    <div class="flex-display items-center space-x-4">
                        <button id="lang-toggle" class="text-xl transition-default" title="Ganti Bahasa">
                            <i class="fas fa-language"></i>
                        </button>
                        <button id="theme-toggle" class="text-xl transition-default" title="Ganti Tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                        <div id="auth-container">
                            <a href="auth/login.php" id="auth-link" class="border border-white px-4 py-2 rounded-full text-sm hover-bg-white text-white transition-default" data-translate-key="login_button">
                                Login
                            </a>
                            <a href="#" id="logout-link" class="border border-white px-4 py-2 rounded-full text-sm hover-bg-white text-white transition-default hidden" data-translate-key="logout_button">
                                Logout
                            </a>
                        </div>
                        <div id="user-greeting" class="hidden flex-display items-center space-x-3">
                            <span id="greeting-text" class="text-sm" data-translate-key="greeting_text">Selamat datang,</span>
                            <span id="user-fullname" class="font-semibold text-sm"></span>
                            <a href="#" id="logout-link-header" class="border border-white px-3 py-1 rounded-full text-xs hover-bg-white text-white transition-default" data-translate-key="logout_button_header">
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Detail Section -->
        <section class="py-20 bg-gradient-br-gray-white">
            <div class="container auto-margin px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="detail_title">Detail Pendakian Gunung Butak</h2>
                    <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed" data-translate-key="detail_subtitle">
                        Informasi lengkap tentang pendakian Gunung Butak melalui jalur Kucur
                    </p>
                </div>

                <div class="max-w-6xl auto-margin">
                    <div class="grid-cols-1 lg-grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="lg-col-span-2 space-y-12">
                            <!-- Overview -->
                            <div class="feature-badge card-hover bg-white rounded-3xl shadow-2xl p-8">
                                <h3 class="text-2xl font-bold mb-6 text-primary" data-translate-key="overview_title">Gambaran Umum</h3>
                                <div class="grid-cols-2 gap-8 mb-8">
                                    <div>
                                        <h4 class="text-lg font-bold mb-3" data-translate-key="mountain_height_label">Ketinggian Gunung</h4>
                                        <p class="text-3xl font-bold gradient-text">2.868 mdpl</p>
                                        <p class="text-gray-600" data-translate-key="masl">Meter di Atas Permukaan Laut</p>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold mb-3" data-translate-key="difficulty_label">Tingkat Kesulitan</h4>
                                        <p class="text-3xl font-bold text-orange" data-translate-key="difficulty_level">Sulit</p>
                                        <p class="text-gray-600" data-translate-key="difficulty_description">Cocok untuk pendaki berpengalaman</p>
                                    </div>
                                </div>
                                
                                <p class="text-gray-700 text-lg leading-relaxed mb-6" data-translate-key="overview_desc">
                                    Gunung Butak adalah destinasi pendakian yang menawarkan keindahan alam yang luar biasa dan pengalaman mendaki yang menantang. 
                                    Terletak di Jawa Timur, gunung ini memiliki ketinggian 2.868 meter di atas permukaan laut dengan berbagai ekosistem yang berbeda 
                                    dari hutan hingga sabana.
                                </p>
                                
                                <div class="flex-display flex-wrap gap-4 mt-8">
                                    <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium">Gunung Terbaik</span>
                                    <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium" data-translate-key="tag_savanna">Sabana Luas</span>
                                    <span class="bg-purple-100 text-purple-800 px-4 py-2 rounded-full text-sm font-medium" data-translate-key="tag_biodiversity">Keberagaman Hayati</span>
                                    <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-medium" data-translate-key="tag_sunrise">Sunrise Spot</span>
                                </div>
                            </div>

                            <!-- Route Details -->
                            <div class="feature-badge card-hover bg-white rounded-3xl shadow-2xl p-8">
                                <h3 class="text-2xl font-bold mb-6 text-primary" data-translate-key="route_details_title">Detail Jalur Pendakian</h3>
                                
                                <div class="space-y-8">
                                    <!-- Basecamp -->
                                    <div class="border-l-4 border-primary pl-6 py-2">
                                        <div class="flex-display items-center mb-2">
                                            <div class="w-6 h-6 bg-primary rounded-full flex-display items-center justify-center mr-4">
                                                <i class="fas fa-map-marker-alt text-white text-xs"></i>
                                            </div>
                                            <h4 class="text-xl font-bold" data-translate-key="basecamp_title">Basecamp Kucur</h4>
                                        </div>
                                        <p class="text-gray-600 ml-10" data-translate-key="basecamp_desc">
                                            Titik awal pendakian berada di Basecamp Kucur dengan ketinggian sekitar 1.200 mdpl. 
                                            Fasilitas yang tersedia meliputi area parkir, toilet, tempat istirahat, dan penyewaan peralatan.
                                        </p>
                                    </div>

                                    <!-- Pos 1 -->
                                    <div class="border-l-4 border-primary pl-6 py-2">
                                        <div class="flex-display items-center mb-2">
                                            <div class="w-6 h-6 bg-primary rounded-full flex-display items-center justify-center mr-4">
                                                <i class="fas fa-map-marker-alt text-white text-xs"></i>
                                            </div>
                                            <h4 class="text-xl font-bold" data-translate-key="pos1_title">Pos 1: Hutan Lumut</h4>
                                        </div>
                                        <p class="text-gray-600 ml-10" data-translate-key="pos1_desc">
                                            Posisi ini berada di ketinggian sekitar 1.800 mdpl. Pendaki akan melewati hutan lumut 
                                            yang lebat dengan kelembapan tinggi. Cuaca mulai terasa dingin dan medan mulai terjal.
                                        </p>
                                    </div>

                                    <!-- Pos 2 -->
                                    <div class="border-l-4 border-primary pl-6 py-2">
                                        <div class="flex-display items-center mb-2">
                                            <div class="w-6 h-6 bg-primary rounded-full flex-display items-center justify-center mr-4">
                                                <i class="fas fa-map-marker-alt text-white text-xs"></i>
                                            </div>
                                            <h4 class="text-xl font-bold" data-translate-key="pos2_title">Pos 2: Sabana Atas</h4>
                                        </div>
                                        <p class="text-gray-600 ml-10" data-translate-key="pos2_desc">
                                            Memasuki area sabana dengan pemandangan yang luas. Ketinggian sekitar 2.300 mdpl. 
                                            Angin mulai kencang dan suhu terasa dingin. Ini adalah tempat yang ideal untuk camping.
                                        </p>
                                    </div>

                                    <!-- Puncak -->
                                    <div class="border-l-4 border-primary pl-6 py-2">
                                        <div class="flex-display items-center mb-2">
                                            <div class="w-6 h-6 bg-primary rounded-full flex-display items-center justify-center mr-4">
                                                <i class="fas fa-flag text-white text-xs"></i>
                                            </div>
                                            <h4 class="text-xl font-bold" data-translate-key="summit_title">Puncak Gunung Butak</h4>
                                        </div>
                                        <p class="text-gray-600 ml-10" data-translate-key="summit_desc">
                                            Tiba di puncak dengan ketinggian 2.868 mdpl. Nikmati pemandangan 360 derajat 
                                            yang menakjubkan dan momen matahari terbit yang tak terlupakan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- What to Bring -->
                            <div class="feature-badge card-hover bg-white rounded-3xl shadow-2xl p-8">
                                <h3 class="text-2xl font-bold mb-6 text-primary" data-translate-key="what_to_bring_title">Apa yang Harus Dibawa</h3>
                                
                                <div class="grid-cols-1 md-grid-cols-2 gap-8">
                                    <div>
                                        <h4 class="text-xl font-bold mb-4 text-green-600" data-translate-key="essential_items">Perlengkapan Wajib</h4>
                                        <ul class="space-y-3">
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_1">Tas Carrier 50-60L</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_2">Sleeping Bag (Tas Tidur)</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_3">Matras/Alas Tidur</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_4">Jaket Bulu Angsa/Down Jacket</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_5">Pakaian Hangat Lainnya</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-xl font-bold mb-4 text-blue-600" data-translate-key="additional_items">Perlengkapan Tambahan</h4>
                                        <ul class="space-y-3">
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_6">Sepatu Pendakian yang Nyaman</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_7">Lampu Senter/Headlamp</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_8">Obat-obatan Pribadi</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_9">Makanan & Minuman</span>
                                            </li>
                                            <li class="flex-display items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                                <span data-translate-key="item_10">Power Bank</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="lg-col-span-1 space-y-8">
                            <!-- Booking Card -->
                            <div class="feature-badge card-hover bg-gradient-primary-accent rounded-3xl p-8 text-white">
                                <h3 class="text-2xl font-bold mb-6" data-translate-key="ready_to_climb">Siap Mendaki?</h3>
                                <p class="mb-6 opacity-90" data-translate-key="ready_desc">
                                    Pesan sekarang untuk mendapatkan tempat di pendakian Gunung Butak bersama pemandu profesional.
                                </p>
                                <a href="booking.php" class="w-full bg-white text-primary font-bold py-3 px-6 rounded-2xl transition-default block text-center hover-scale-105 shadow-lg" data-translate-key="book_now_btn">
                                    <i class="fas fa-calendar-check mr-3"></i>Pesan Sekarang
                                </a>
                            </div>

                            <!-- Info Card -->
                            <div class="feature-badge card-hover bg-white rounded-3xl shadow-2xl p-8">
                                <h3 class="text-xl font-bold mb-4 text-primary" data-translate-key="important_info_title">Informasi Penting</h3>
                                <ul class="space-y-4">
                                    <li class="flex-display items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <span data-translate-key="info_1">Kuota pendakian terbatas tiap hari</span>
                                    </li>
                                    <li class="flex-display items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <span data-translate-key="info_2">Pendakian hanya diizinkan dengan pemandu</span>
                                    </li>
                                    <li class="flex-display items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <span data-translate-key="info_3">Harus dalam kondisi sehat jasmani & rohani</span>
                                    </li>
                                    <li class="flex-display items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <span data-translate-key="info_4">Ikuti semua SOP keselamatan</span>
                                    </li>
                                    <li class="flex-display items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <span data-translate-key="info_5">Jaga kebersihan alam</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Weather Info -->
                            <div class="feature-badge card-hover bg-white rounded-3xl shadow-2xl p-8">
                                <h3 class="text-xl font-bold mb-4 text-primary" data-translate-key="weather_title">Cuaca Terkini</h3>
                                <div class="flex-display items-center justify-between mb-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">18°C</div>
                                        <div class="text-sm text-gray-600" data-translate-key="temperature">Suhu</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-yellow-600">65%</div>
                                        <div class="text-sm text-gray-600" data-translate-key="humidity">Kelembapan</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-gray-600">7 km/h</div>
                                        <div class="text-sm text-gray-600" data-translate-key="wind">Angin</div>
                                    </div>
                                </div>
                                <div class="text-center bg-blue-50 p-4 rounded-2xl">
                                    <i class="fas fa-cloud-sun text-4xl text-blue-500 mb-2"></i>
                                    <p class="font-medium text-gray-700" data-translate-key="weather_status">Cerah Berawan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark-green text-white py-16">
            <div class="container auto-margin px-4">
                <div class="grid-cols-1 md-grid-cols-4 gap-8">
                    <!-- Logo dan Deskripsi -->
                    <div>
                        <div class="flex-display items-center mb-6">
                            <i class="fas fa-mountain text-3xl text-accent mr-3"></i>
                            <h3 class="text-2xl font-bold" data-translate-key="footer_title">Gunung Butak</h3>
                        </div>
                        <p class="text-gray-300 mb-6 leading-relaxed" data-translate-key="footer_description">
                            Platform reservasi pendakian Gunung Butak melalui jalur Kucur.
                            Membantu pendaki untuk merencanakan dan menikmati petualangan alam mereka dengan aman dan menyenangkan.
                        </p>
                        <div class="flex-display space-x-4">
                            <a href="#" class="social-badge w-12 h-12 bg-accent rounded-full flex-display items-center justify-center text-white hover:bg-white text-primary transition-default">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-badge w-12 h-12 bg-accent rounded-full flex-display items-center justify-center text-white hover:bg-white text-primary transition-default">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-badge w-12 h-12 bg-accent rounded-full flex-display items-center justify-center text-white hover:bg-white text-primary transition-default">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-badge w-12 h-12 bg-accent rounded-full flex-display items-center justify-center text-white hover:bg-white text-primary transition-default">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Navigasi -->
                    <div>
                        <h4 class="text-lg font-bold mb-6" data-translate-key="navigation_title">Navigasi</h4>
                        <ul class="space-y-3">
                            <li><a href="index.php" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_home">Beranda</a></li>
                            <li><a href="index.php#tentang" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_about_us">Tentang Kami</a></li>
                            <li><a href="index.php#lokasi" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_climbing_route">Jalur Pendakian</a></li>
                            <li><a href="booking.php" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_booking">Reservasi</a></li>
                            <li><a href="detail.php" class="nav-link text-gray-300 hover:text-accent transition-default block py-1 text-accent" data-translate-key="nav_detail">Detail Pendakian</a></li>
                        </ul>
                    </div>

                    <!-- Jalur Pendakian -->
                    <div>
                        <h4 class="text-lg font-bold mb-6" data-translate-key="climbing_info_title">Jalur Pendakian</h4>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex-display items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-2 text-accent"></i>
                                <span data-translate-key="info_route">Jalur Utama: Kucur</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-bolt mt-1 mr-2 text-accent"></i>
                                <span data-translate-key="info_difficulty">Tingkat Kesulitan: Sulit</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-clock mt-1 mr-2 text-accent"></i>
                                <span data-translate-key="info_duration">Durasi: 3-4 Hari</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-ruler-vertical mt-1 mr-2 text-accent"></i>
                                <span data-translate-key="info_height">Ketinggian: 2.868 mdpl</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-thermometer-half mt-1 mr-2 text-accent"></i>
                                <span data-translate-key="info_temperature">Suhu: 15-22°C</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Kontak Kami -->
                    <div>
                        <h4 class="text-lg font-bold mb-6" data-translate-key="contact_us_title">Kontak Kami</h4>
                        <ul class="space-y-4 text-gray-300">
                            <li class="flex-display items-start">
                                <i class="fas fa-envelope mr-3 mt-1 text-accent"></i>
                                <span>info@gunungbutak.com</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-phone mr-3 mt-1 text-accent"></i>
                                <span>+62 812 3456 7890</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-map-marker-alt mr-3 mt-1 text-accent"></i>
                                <span data-translate-key="contact_address">Basecamp Kucur, Gunung Butak, Jawa Timur</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-comments mr-3 mt-1 text-accent"></i>
                                <span data-translate-key="contact_chat">Live Chat 24/7</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                <p data-translate-key="copyright">&copy; 2025 Gunung Butak Reservasi Pendakian. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        // --- THEME TOGGLE LOGIC ---
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');

        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            }
        }

        function loadTheme() {
            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            applyTheme(savedTheme === 'dark');
        }

        themeToggle.addEventListener('click', () => {
            const isDark = document.body.classList.contains('dark-mode');
            applyTheme(!isDark);
        });

        // --- TRANSLATION LOGIC ---
        const translations = {
            'id': {
                detail_title: "Detail Pendakian Gunung Butak",
                detail_subtitle: "Informasi lengkap tentang pendakian Gunung Butak melalui jalur Kucur",
                overview_title: "Gambaran Umum",
                mountain_height_label: "Ketinggian Gunung", 
                masl: "Meter di Atas Permukaan Laut",
                difficulty_label: "Tingkat Kesulitan",
                difficulty_level: "Sulit",
                difficulty_description: "Cocok untuk pendaki berpengalaman",
                overview_desc: "Gunung Butak adalah destinasi pendakian yang menawarkan keindahan alam yang luar biasa dan pengalaman mendaki yang menantang. Terletak di Jawa Timur, gunung ini memiliki ketinggian 2.868 meter di atas permukaan laut dengan berbagai ekosistem yang berbeda dari hutan hingga sabana.",
                tag_savanna: "Sabana Luas",
                tag_biodiversity: "Keberagaman Hayati",
                tag_sunrise: "Sunrise Spot",
                route_details_title: "Detail Jalur Pendakian",
                basecamp_title: "Basecamp Kucur",
                basecamp_desc: "Titik awal pendakian berada di Basecamp Kucur dengan ketinggian sekitar 1.200 mdpl. Fasilitas yang tersedia meliputi area parkir, toilet, tempat istirahat, dan penyewaan peralatan.",
                pos1_title: "Pos 1: Hutan Lumut",
                pos1_desc: "Posisi ini berada di ketinggian sekitar 1.800 mdpl. Pendaki akan melewati hutan lumut yang lebat dengan kelembapan tinggi. Cuaca mulai terasa dingin dan medan mulai terjal.",
                pos2_title: "Pos 2: Sabana Atas",
                pos2_desc: "Memasuki area sabana dengan pemandangan yang luas. Ketinggian sekitar 2.300 mdpl. Angin mulai kencang dan suhu terasa dingin. Ini adalah tempat yang ideal untuk camping.",
                summit_title: "Puncak Gunung Butak",
                summit_desc: "Tiba di puncak dengan ketinggian 2.868 mdpl. Nikmati pemandangan 360 derajat yang menakjubkan dan momen matahari terbit yang tak terlupakan.",
                what_to_bring_title: "Apa yang Harus Dibawa",
                essential_items: "Perlengkapan Wajib",
                item_1: "Tas Carrier 50-60L",
                item_2: "Sleeping Bag (Tas Tidur)",
                item_3: "Matras/Alas Tidur",
                item_4: "Jaket Bulu Angsa/Down Jacket",
                item_5: "Pakaian Hangat Lainnya",
                additional_items: "Perlengkapan Tambahan",
                item_6: "Sepatu Pendakian yang Nyaman",
                item_7: "Lampu Senter/Headlamp",
                item_8: "Obat-obatan Pribadi",
                item_9: "Makanan & Minuman",
                item_10: "Power Bank",
                ready_to_climb: "Siap Mendaki?",
                ready_desc: "Pesan sekarang untuk mendapatkan tempat di pendakian Gunung Butak bersama pemandu profesional.",
                book_now_btn: "Pesan Sekarang",
                important_info_title: "Informasi Penting",
                info_1: "Kuota pendakian terbatas tiap hari",
                info_2: "Pendakian hanya diizinkan dengan pemandu",
                info_3: "Harus dalam kondisi sehat jasmani & rohani",
                info_4: "Ikuti semua SOP keselamatan",
                info_5: "Jaga kebersihan alam",
                weather_title: "Cuaca Terkini",
                temperature: "Suhu",
                humidity: "Kelembapan",
                wind: "Angin",
                weather_status: "Cerah Berawan",
                navigation_title: "Navigasi",
                nav_home: "Beranda", 
                nav_about_us: "Tentang Kami",
                nav_climbing_route: "Jalur Pendakian",
                nav_booking: "Reservasi",
                nav_detail: "Detail Pendakian",
                footer_title: "Gunung Butak",
                footer_description: "Platform reservasi pendakian Gunung Butak melalui jalur Kucur. Membantu pendaki untuk merencanakan dan menikmati petualangan alam mereka dengan aman dan menyenangkan.",
                climbing_info_title: "Jalur Pendakian",
                info_route: "Jalur Utama: Kucur",
                info_difficulty: "Tingkat Kesulitan: Sulit",
                info_duration: "Durasi: 3-4 Hari",
                info_height: "Ketinggian: 2.868 mdpl",
                info_temperature: "Suhu: 15-22°C",
                contact_us_title: "Kontak Kami",
                contact_address: "Basecamp Kucur, Gunung Butak, Jawa Timur",
                contact_chat: "Live Chat 24/7",
                copyright: "&copy; 2025 Gunung Butak Reservasi Pendakian. All rights reserved.",
            },
            'en': {
                detail_title: "Mount Butak Climbing Details",
                detail_subtitle: "Complete information about Mount Butak climbing via Kucur route",
                overview_title: "Overview",
                mountain_height_label: "Mountain Height", 
                masl: "Meters Above Sea Level",
                difficulty_label: "Difficulty Level",
                difficulty_level: "Difficult",
                difficulty_description: "Suitable for experienced climbers",
                overview_desc: "Mount Butak is a climbing destination that offers incredible natural beauty and a challenging climbing experience. Located in East Java, this mountain has an altitude of 2,868 meters above sea level with different ecosystems from forest to savanna.",
                tag_savanna: "Vast Savanna",
                tag_biodiversity: "Biodiversity",
                tag_sunrise: "Sunrise Spot",
                route_details_title: "Climbing Route Details",
                basecamp_title: "Kucur Basecamp",
                basecamp_desc: "The starting point of the climb is at Kucur Basecamp with an elevation of around 1,200 masl. Available facilities include parking area, toilets, rest area, and equipment rental.",
                pos1_title: "Pos 1: Moss Forest",
                pos1_desc: "This position is at an elevation of around 1,800 masl. Climbers will pass through dense moss forest with high humidity. The weather starts to feel cold and the terrain becomes steep.",
                pos2_title: "Pos 2: Upper Savanna",
                pos2_desc: "Entering the savanna area with a vast view. Elevation around 2,300 masl. Wind starts to blow hard and the temperature feels cold. This is an ideal place for camping.",
                summit_title: "Mount Butak Peak",
                summit_desc: "Arrive at the peak with an elevation of 2,868 masl. Enjoy the amazing 360-degree view and unforgettable sunrise moment.",
                what_to_bring_title: "What to Bring",
                essential_items: "Essential Equipment",
                item_1: "Carrier Bag 50-60L",
                item_2: "Sleeping Bag",
                item_3: "Mat/Bed Mat",
                item_4: "Down Jacket",
                item_5: "Other Warm Clothes",
                additional_items: "Additional Equipment",
                item_6: "Comfortable Climbing Shoes",
                item_7: "Flashlight/Headlamp",
                item_8: "Personal Medications",
                item_9: "Food & Drinks",
                item_10: "Power Bank",
                ready_to_climb: "Ready to Climb?",
                ready_desc: "Book now to get a spot in the Mount Butak climb with professional guides.",
                book_now_btn: "Book Now",
                important_info_title: "Important Information",
                info_1: "Limited climbing quota per day",
                info_2: "Climbing only allowed with a guide",
                info_3: "Must be in good physical and mental condition",
                info_4: "Follow all safety SOPs",
                info_5: "Keep the environment clean",
                weather_title: "Current Weather",
                temperature: "Temperature",
                humidity: "Humidity",
                wind: "Wind",
                weather_status: "Partly Cloudy",
                navigation_title: "Navigation",
                nav_home: "Home", 
                nav_about_us: "About Us",
                nav_climbing_route: "Climbing Route",
                nav_booking: "Reservation",
                nav_detail: "Climbing Details",
                footer_title: "Mount Butak",
                footer_description: "Mount Butak climbing reservation platform via the Kucur route. Helping climbers plan and enjoy their natural adventure safely and pleasantly.",
                climbing_info_title: "Climbing Route",
                info_route: "Main Route: Kucur",
                info_difficulty: "Difficulty Level: Difficult",
                info_duration: "Duration: 3-4 Days",
                info_height: "Height: 2,868 masl",
                info_temperature: "Temperature: 15-22°C",
                contact_us_title: "Contact Us",
                contact_address: "Kucur Basecamp, Mount Butak, East Java",
                contact_chat: "Live Chat 24/7",
                copyright: "&copy; 2025 Mount Butak Climbing Reservation. All rights reserved.",
            }
        };

        let currentLang = 'id'; // Default language

        const langToggle = document.getElementById('lang-toggle');

        function translateElement(element) {
            const key = element.getAttribute('data-translate-key');
            if (key && translations[currentLang][key]) {
                const translation = translations[currentLang][key];

                // Cek apakah elemen input/placeholder
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                    if (element.placeholder) {
                         element.placeholder = translation;
                    } else if (element.value && !['select', 'option'].includes(element.tagName.toLowerCase())) {
                         element.value = translation;
                    } else if (element.tagName === 'OPTION') {
                         element.textContent = translation;
                    } else {
                         element.textContent = translation;
                    }
                } else if (element.tagName === 'TITLE') {
                    document.title = translation;
                } else {
                    // Untuk elemen dengan konten HTML (misalnya, yang memiliki <strong>), gunakan innerHTML
                    if (translation.includes('<strong>') || translation.includes('<b>')) {
                        element.innerHTML = translation;
                    } else {
                        // Untuk teks biasa, gunakan textContent
                        element.textContent = translation;
                    }
                }
            }
        }

        function applyTranslation(lang) {
            currentLang = lang;
            document.documentElement.lang = lang;
            const elementsToTranslate = document.querySelectorAll('[data-translate-key]');

            elementsToTranslate.forEach(translateElement);

            localStorage.setItem('lang', lang);
        }

        langToggle.addEventListener('click', () => {
            const newLang = currentLang === 'id' ? 'en' : 'id';
            applyTranslation(newLang);
        });

        function loadLanguage() {
            const savedLang = localStorage.getItem('lang') || 'id';
            applyTranslation(savedLang);
        }

        // --- INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', function() {
            loadTheme();
            loadLanguage();
        });
    </script>

    <!-- Include index.js -->
    <script src="assets/js/index.js"></script>

    <!-- Include sliding-komentar.js first to ensure sliding functions are available -->
    <script src="assets/js/sliding-komentar.js"></script>

    <!-- Include main.js -->
    <script src="assets/js/main.js"></script>

    <!-- Include weather forecast -->
    <script src="assets/js/weather-forecast.js"></script>

    <!-- Include poster slider -->
    <script src="assets/js/poster-slider.js"></script>
</body>
</html>