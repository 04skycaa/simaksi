

<?php
session_start();
$is_logged_in = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$user_name = $is_logged_in ? ($_SESSION['username'] ?? 'Pendaki') : null;
$user_role = $is_logged_in ? ($_SESSION['user_peran'] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate-key="page_title">Gunung Butak </title>
    <?php
// Define base paths for flexible URL handling
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$base_url = rtrim($base_url, '/');
$asset_url = $base_url . '/assets';
?>
<link rel="icon" type="image/x-icon" href="<?php echo $asset_url; ?>/images/LOGO_WEB.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Supabase Client Library -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

    <!-- Native CSS (Relative Path) -->
    <link rel="stylesheet" href="<?php echo $asset_url; ?>/css/index.css">
</head>
<body class="bg-white">
    <!-- Main Content -->
    <div class="flex-col main-content">
        <!-- Header -->
        <header class="header-shadow bg-primary text-white py-4 px-6 static">
            <div class="container auto-margin">
                <div class="flex-display items-center justify-between">
                    <div class="flex-display items-center">
                        <img src="<?php echo $asset_url; ?>/images/LOGO_WEB.png" alt="Logo Gunung Butak" class="w-10 h-10 mr-3">
                        <h1 class="text-xl font-bold" data-translate-key="header_title">Gunung Butak</h1>
                    </div>
                    <div class="flex-display items-center space-x-4">
                        <button id="lang-toggle" class="text-xl transition-default" title="Ganti Bahasa">
                            <i class="fas fa-language"></i>
                        </button>
                        <button id="theme-toggle" class="text-xl transition-default" title="Ganti Tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>

                        <?php if ($is_logged_in && $user_name): ?>
                            <div id="auth-display">
                                <span id="greeting-text" class="text-sm" data-translate-key="greeting_text">Selamat datang,</span>
                                <span id="user-fullname" class="font-semibold text-sm"><?php echo htmlspecialchars($user_name); ?></span>
                                <?php $logout_url = $base_url . '/auth/relogin.php'; ?>
                                <a href="<?php echo $logout_url; ?>" id="logout-link-header" class="border border-white px-3 py-1 rounded-full text-xs hover-bg-white text-white transition-default" data-translate-key="logout_button_header">
                                    Logout
                                </a>
                            </div>
                        <?php else: ?>
                            <div id="auth-display">
                                <?php $login_url = $base_url . '/auth/login.php'; ?>
                                <a href="<?php echo $login_url; ?>" class="login-button border border-white px-4 py-2 rounded-full text-sm hover-bg-white text-white transition-default" data-translate-key="login_button">
                                    Login
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

    <!-- Hero Section -->
    <section class="hero-background min-height-screen flex-display items-center justify-center relative overflow-hidden">
        <div class="text-center text-white z-10 px-4 max-w-4xl auto-margin relative">
            <div class="mb-8 hero-content">
                <h1 class="text-6xl md-text-8xl font-bold mb-6 gradient-text" data-translate-key="hero_title">GUNUNG BUTAK</h1>
                <p class="text-2xl text-3xl mb-10 max-w-3xl auto-margin leading-relaxed" data-translate-key="hero_subtitle">
                    Jelajahi keindahan alam dan tantangan ekstrem di puncak tertinggi Jawa Timur
                </p>
            </div>

            <div class="button-container">
                <a href="#" class="pulse-button button-reserve">
                    <i class="fas fa-rocket"></i>Reservasi Sekarang
                </a>
                <a href="#lokasi" class="pulse-button button-location">
                    <i class="fas fa-map-marker-alt"></i>Lokasi Pendakian
                </a>
            </div>

            <div class="stats-grid">

            <!-- 1. TOTAL PENDAKI TAHUN INI -->
            <div class="glass-card card-hover">
                <i class="card-icon fas fa-users"></i>
                <!-- Default value: Loading... -->
                <div class="stat-number gradient-text" id="total-pendaki">1000+</div>
                <div class="stat-label" data-translate-key="climbers_this_year">Total Rombongan Tahun Ini</div>
            </div>

            <!-- 2. RATING PENGALAMAN -->
            <div class="glass-card card-hover">
                <i class="card-icon fas fa-star"></i>
                <!-- Default value: Loading... -->
                <div class="stat-number gradient-text" id="rating-pengalaman">3,6/5</div>
                <div class="stat-label" data-translate-key="experience_rating">Rating Pengalaman</div>
            </div>

            <!-- 3. TAHUN OPERASIONAL (Statik) -->
            <div class="glass-card card-hover">
                <i class="card-icon fas fa-calendar-check"></i>
                <div class="stat-number gradient-text">1+</div>
                <div class="stat-label" data-translate-key="years_of_operation">Tahun Operasional</div>
            </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 animate-bounce text-white">
            <i class="fas fa-chevron-down text-3xl"></i>
        </div>
    </section>

    <!-- Pengumuman Section -->
    <section class="py-16 bg-gradient-to-br from-blue-50 to-green-50">
        <div class="container auto-margin px-4">
            <div class="text-center mb-12">
                <div class="inline-block relative">
                    <h2 class="text-4xl font-bold mb-4 relative z-10"
                        data-translate-key="latest_announcements_title">Pengumuman Terbaru</h2>
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-32 h-1 bg-gradient-to-r from-green-400 to-blue-500 rounded-full"></div>
                </div>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed mt-6" data-translate-key="latest_announcements_subtitle">
                    Informasi penting terkait pendakian Gunung Butak
                </p>
            </div>

            <div id="pengumuman-container" class="max-w-6xl auto-margin">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-xl p-6 border border-gray-100">
                    <div id="pengumuman-content" class="space-y-6">
                        <div id="pengumuman-loading" class="flex flex-col items-center justify-center py-16">
                            <div class="relative">
                                <div class="w-16 h-16 border-4 border-t-green-500 border-r-blue-500 border-b-indigo-500 border-l-transparent rounded-full animate-spin"></div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                    <i class="fas fa-bullhorn text-2xl text-green-500"></i>
                                </div>
                            </div>
                            <p class="mt-6 text-gray-600 font-medium" data-translate-key="loading_announcements">Memuat pengumuman terbaru...</p>
                        </div>
                        <div id="pengumuman-empty" class="hidden flex flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-bullhorn text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-3" data-translate-key="no_announcements_title">Tidak Ada Pengumuman</h3>
                            <p class="text-gray-600 max-w-md" data-translate-key="no_announcements_subtitle">Belum ada pengumuman aktif saat ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Kunci -->
    <section class="py-20 bg-gradient-br-gray-white">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-6 animated-border auto-margin" data-translate-key="why_butak_title">Kenapa Memilih Gunung Butak?</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed" data-translate-key="why_butak_subtitle">
                    Temukan alasan mengapa Gunung Butak menjadi destinasi pendakian favorit para petualang
                </p>
            </div>

            <div class="grid-cols-1 lg-grid-cols-3 gap-10 mb-16">
                <!-- Main Stats Cards -->
                <div class="stat-card bg-white rounded-3xl shadow-2xl card-hover overflow-hidden feature-badge glass-card">
                    <div class="w-full h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
                    <div class="p-10">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-r from-green-100 to-green-200 rounded-full flex-display items-center justify-center auto-margin mb-8">
                                <i class="fas fa-mountain text-4xl text-primary"></i>
                            </div>
                            <h3 class="text-6xl font-bold mb-4 gradient-text">2,868</h3>
                            <p class="text-2xl font-semibold text-gray-800 mb-3" data-translate-key="mountain_height_label">Ketinggian Gunung</p>
                            <div class="progress-bar elevation-bar bg-gray-200 w-full auto-margin mb-3 h-4" data-value="2868" data-max="2868">
                                <div class="progress-fill bg-gradient-to-r from-green-400 to-green-600 h-full"></div>
                            </div>
                            <p class="text-lg text-gray-600" data-translate-key="masl">Meter di Atas Permukaan Laut</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-3xl shadow-2xl card-hover overflow-hidden feature-badge glass-card difficulty-section">
                    <div class="w-full h-1 bg-gradient-to-r from-orange-400 to-orange-600"></div>
                    <div class="p-10">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-r from-orange-100 to-orange-200 rounded-full flex-display items-center justify-center auto-margin mb-8">
                                <i class="fas fa-bolt text-4xl text-orange"></i>
                            </div>
                            <h3 class="text-5xl font-bold mb-4 text-orange" data-translate-key="difficulty_level">Sulit</h3>
                            <p class="text-2xl font-semibold text-gray-800 mb-4" data-translate-key="difficulty_label">Tingkat Kesulitan</p>
                            <div class="progress-bar difficulty-bar bg-gray-200 w-full auto-margin mb-4 h-8" data-value="80" data-max="100">
                                <div class="progress-fill bg-gradient-to-r from-orange-400 to-red h-full" style="--progress-width: 80%;"></div>
                            </div>
                            <p class="text-lg text-gray-600" data-translate-key="difficulty_description">Cocok untuk pendaki berpengalaman</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-white rounded-3xl shadow-2xl card-hover overflow-hidden feature-badge glass-card">
                    <div class="w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                    <div class="p-10">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex-display items-center justify-center auto-margin mb-8">
                                <i class="fas fa-clock text-4xl text-blue-600"></i>
                            </div>
                            <h3 class="text-6xl font-bold mb-4 text-accent" style="color: var(--color-accent);">3-4</h3>
                            <p class="text-2xl font-semibold text-gray-800 mb-3" data-translate-key="duration_label">Durasi Pendakian</p>
                            <p class="text-lg text-gray-600" data-translate-key="ideal_duration">Hari Pendakian Ideal</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info Cards -->
            <div class="grid-cols-1 md-grid-cols-2 lg-grid-cols-4 gap-8 mt-12">
                <div class="bg-white rounded-2xl p-8 shadow-xl card-hover text-center feature-badge glass-card border border-white border-opacity-30">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-100 to-green-200 rounded-full flex-display items-center justify-center auto-margin mb-6">
                        <i class="fas fa-temperature-high text-3xl text-green-600"></i>
                    </div>
                    <h4 class="font-bold text-xl text-gray-800 mb-3" data-translate-key="avg_temp_title">Suhu Rata-rata</h4>
                    <p class="text-2xl font-bold text-primary mb-2">15-22°C</p>
                    <p class="text-gray-600" data-translate-key="avg_temp_desc">Nyaman untuk pendakian</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-xl card-hover text-center feature-badge glass-card border border-white border-opacity-30">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex-display items-center justify-center auto-margin mb-6">
                        <i class="fas fa-wind text-3xl text-blue-600"></i>
                    </div>
                    <h4 class="font-bold text-xl text-gray-800 mb-3" data-translate-key="wind_speed_title">Kecepatan Angin</h4>
                    <p class="text-2xl font-bold text-primary mb-2">10-30 km/jam</p>
                    <p class="text-gray-600" data-translate-key="wind_speed_desc">Angin segar di puncak</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-xl card-hover text-center feature-badge glass-card border border-white border-opacity-30">
                    <div class="w-20 h-20 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-full flex-display items-center justify-center auto-margin mb-6">
                        <i class="fas fa-cloud-sun text-3xl text-yellow-600"></i>
                    </div>
                    <h4 class="font-bold text-xl text-gray-800 mb-3" data-translate-key="rainfall_title">Curah Hujan</h4>
                    <p class="text-2xl font-bold text-primary mb-2" data-translate-key="rainfall_level">Rendah</p>
                    <p class="text-gray-600" data-translate-key="rainfall_desc">Kemungkinan hujan rendah</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-xl card-hover text-center feature-badge glass-card border border-white border-opacity-30">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-100 to-purple-200 rounded-full flex-display items-center justify-center auto-margin mb-6">
                        <i class="fas fa-tree text-3xl text-purple-600"></i>
                    </div>
                    <h4 class="font-bold text-xl text-gray-800 mb-3" data-translate-key="vegetation_title">Vegetasi</h4>
                    <p class="text-2xl font-bold text-primary mb-2" data-translate-key="vegetation_zones">4 Zona</p>
                    <p class="text-gray-600" data-translate-key="vegetation_desc">Hutan-Sahara yang bervariasi</p>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="mt-20 bg-gradient-primary-accent rounded-3xl p-10 text-white">
                <div class="max-w-4xl auto-margin text-center">
                    <h3 class="text-3xl font-bold mb-6" data-translate-key="safety_facilities_title">Keamanan dan Fasilitas</h3>
                    <div class="grid-cols-1 md-grid-cols-3 gap-8">
                        <div>
                            <i class="fas fa-shield-alt text-4xl mb-4"></i>
                            <h4 class="text-xl font-bold mb-2" data-translate-key="safety_guaranteed_title">Keamanan Terjamin</h4>
                            <p data-translate-key="safety_guaranteed_desc">Pendakian aman dengan pemandu bersertifikat</p>
                        </div>
                        <div>
                            <i class="fas fa-medkit text-4xl mb-4"></i>
                            <h4 class="text-xl font-bold mb-2" data-translate-key="complete_facilities_title">Fasilitas Lengkap</h4>
                            <p data-translate-key="complete_facilities_desc">Basecamp lengkap dengan peralatan darurat</p>
                        </div>
                        <div>
                            <i class="fas fa-users text-4xl mb-4"></i>
                            <h4 class="text-xl font-bold mb-2" data-translate-key="climber_community_title">Komunitas Pendaki</h4>
                            <p data-translate-key="climber_community_desc">Ikuti komunitas pendakian aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Gunung Butak -->
    <section id="tentang" class="py-20 bg-gradient-to-br from-white to-gray-50">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="about_butak_title">Tentang Gunung Butak</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin mb-12 leading-relaxed" data-translate-key="about_butak_subtitle">
                    Kenali lebih dekat destinasi pendakian terbaik di Jawa Timur
                </p>
            </div>

            <div class="max-w-5xl auto-margin">
                <div class="grid-cols-1 md-grid-cols-2 gap-12 items-center mb-16">
                    <div class="feature-card card-hover">
                        <div class="bg-gradient-primary-accent rounded-2xl p-1">
                            <div class="bg-white rounded-2xl p-8">
                                <h3 class="text-2xl font-bold mb-6 text-primary" data-translate-key="history_title">Sejarah dan Asal Usul</h3>
                                <p class="text-gray-700 text-lg leading-relaxed mb-4" data-translate-key="history_p1">
                                    Gunung Butak adalah destinasi pendakian yang menawarkan keindahan alam yang luar biasa dan pengalaman mendaki yang menantang. Terletak di Jawa Timur, gunung ini memiliki ketinggian 2.868 meter di atas permukaan laut.
                                </p>
                                <p class="text-gray-700 text-lg leading-relaxed" data-translate-key="history_p2">
                                    Dengan berbagai ekosistem yang berbeda dari hutan hingga sabana, Gunung Butak menjadi tempat yang ideal bagi para pendaki yang mencari keindahan alam sekaligus tantangan ekstrem.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card card-hover">
                        <div class="overflow-hidden rounded-2xl shadow-xl">
                            <img src="assets/images/gunung2.jpg" alt="Pemandangan Gunung Butak" class="w-full h-80 object-cover">
                        </div>
                    </div>
                </div>

                <div class="grid-cols-1 md-grid-cols-2 gap-10">
                    <div class="bg-gradient-primary-green700 rounded-2xl p-8 text-white feature-badge">
                        <h3 class="text-2xl font-bold mb-6" data-translate-key="fun_facts_title">Fakta Menarik</h3>
                        <ul class="space-y-4">
                            <li class="flex-display items-start">
                                <i class="fas fa-star text-yellow-300 mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="fun_fact_1">Nama "Butak" berasal dari bahasa Jawa yang berarti 'telanjang', merujuk pada puncaknya yang tidak berpohon</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-star text-yellow-300 mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="fun_fact_2">Merupakan gunung tertinggi ke-3 di Jawa Timur</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-star text-yellow-300 mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="fun_fact_3">Habitat bagi berbagai spesies langka flora dan fauna</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-star text-yellow-300 mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="fun_fact_4">Lokasi hunting sunrise yang legendaris</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gradient-accent-blue500 rounded-2xl p-8 text-white feature-badge">
                        <h3 class="text-2xl font-bold mb-6" data-translate-key="our_advantages_title">Keunggulan Kami</h3>
                        <ul class="space-y-4">
                            <li class="flex-display items-start">
                                <i class="fas fa-shield-alt text-white mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="advantage_1">Sistem reservasi online terintegrasi dan aman</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-user-friends text-white mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="advantage_2">Pemandu berpengalaman bersertifikat nasional</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-home text-white mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="advantage_3">Basecamp lengkap dengan fasilitas memadai</span>
                            </li>
                            <li class="flex-display items-start">
                                <i class="fas fa-heart text-white mt-1 mr-4 text-xl"></i>
                                <span class="text-lg" data-translate-key="advantage_4">Komunitas pendaki aktif dan suportif</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mengapa Mendaki Gunung Butak -->
    <section class="py-20 bg-gradient-br-gray-white">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="why_climb_title">Mengapa Mendaki Gunung Butak?</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed" data-translate-key="why_climb_subtitle">
                    Temukan alasan mengapa Gunung Butak menjadi destinasi pendakian yang tak terlupakan
                </p>
            </div>

            <div class="grid-cols-1 lg-grid-cols-3 gap-10">
                <!-- Card 1 -->
                <div class="feature-card card-hover bg-gradient-br-green-blue rounded-3xl shadow-2xl overflow-hidden transition-default group">
                    <div class="h-72 overflow-hidden">
                        <img src="assets/images/gunung2.jpg" alt="Sabana Luas" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 opacity-0 group-hover:opacity-20 transition-default"></div>
                    </div>
                    <div class="p-8">
                        <div class="w-16 h-16 bg-green-600 rounded-full flex-display items-center justify-center mb-6 text-white auto-margin">
                            <i class="fas fa-camera text-2xl"></i>
                        </div>
                        <h3 class="text-3xl font-bold mb-4 text-center text-primary" data-translate-key="scenic_view_title">Pemandangan Indah</h3>
                        <p class="text-gray-700 text-lg leading-relaxed text-center" data-translate-key="scenic_view_desc">
                            Nikmati pemandangan alam yang luar biasa dari hutan hingga sabana yang menakjubkan di sepanjang jalur pendakian.
                            Puncak Gunung Butak menawarkan pemandangan 360 derajat yang memukau.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="feature-card card-hover bg-gradient-br-yellow-red rounded-3xl shadow-2xl overflow-hidden transition-default group">
                    <div class="h-72 overflow-hidden">
                        <img src="assets/images/flora_fauna.jpg" alt="Keberagaman Flora & Fauna" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-red-600 opacity-0 group-hover:opacity-20 transition-default"></div>
                    </div>
                    <div class="p-8">
                        <div class="w-16 h-16 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-full flex-display items-center justify-center mb-6 text-yellow-600 auto-margin">
                            <i class="fas fa-leaf text-2xl"></i>
                        </div>
                        <h3 class="text-3xl font-bold mb-4 text-center text-primary" data-translate-key="biodiversity_title">Keberagaman Flora & Fauna</h3>
                        <p class="text-gray-700 text-lg leading-relaxed text-center" data-translate-key="biodiversity_desc">
                            Jelajahi berbagai ekosistem unik dan temukan berbagai spesies langka yang hanya ditemukan di kawasan ini.
                            Habitat alami berbagai jenis burung dan satwa endemik Jawa.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="feature-card card-hover bg-gradient-br-blue-indigo rounded-3xl shadow-2xl overflow-hidden transition-default group">
                    <div class="h-72 overflow-hidden">
                        <img src="assets/images/Sabana_Butak.jpg" alt="Sabana" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 opacity-0 group-hover:opacity-20 transition-default"></div>
                    </div>
                    <div class="p-8">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-100 to-blue-200 rounded-full flex-display items-center justify-center mb-6 text-blue-600 auto-margin">
                            <i class="fas fa-heart text-2xl"></i>
                        </div>
                        <h3 class="text-3xl font-bold mb-4 text-center text-primary" data-translate-key="vast_savanna_title">Sabana Luas</h3>
                        <p class="text-gray-700 text-lg leading-relaxed text-center" data-translate-key="vast_savanna_desc">
                            Rasakan keindahan luasnya padang sabana di kaki gunung, tempat yang sangat indah untuk berkemah dan menikmati alam.
                            Sempurna untuk hunting sunrise dan stargazing.
                        </p>
                    </div>
                </div>
            </div>

            <!--untuk tampilan keuntungan mendaki-->
            <div class="benefits-section">
            <h3 class="text-4xl font-bold mb-6" data-translate-key="climb_benefits_title">Keuntungan Mendaki Bersama Kami</h3>
            <div class="cards-grid-wrapper">
                <div class="feature-badge card-hover">
                    <i class="fas fa-graduation-cap text-5xl mb-4"></i>
                    <h4 class="text-2xl font-bold mb-3" data-translate-key="certified_guides_title">Pemandu Bersertifikat</h4>
                    <p class="text-lg opacity-90" data-translate-key="certified_guides_desc">Pemandu profesional dengan sertifikasi nasional</p>
                </div>
                <div class="feature-badge card-hover">
                    <i class="fas fa-shield-alt text-5xl mb-4"></i>
                    <h4 class="text-2xl font-bold mb-3" data-translate-key="guaranteed_safety_title">Keamanan Terjamin</h4>
                    <p class="text-lg opacity-90" data-translate-key="guaranteed_safety_desc">Sistem keamanan terintegrasi dan SOP ketat</p>
                </div>
                <div class="feature-badge card-hover">
                    <i class="fas fa-campground text-5xl mb-4"></i>
                    <h4 class="text-2xl font-bold mb-3" data-translate-key="complete_basecamp_title">Fasilitas Lengkap</h4>
                    <p class="text-lg opacity-90" data-translate-key="complete_basecamp_desc">Basecamp lengkap dengan peralatan standar</p>
                </div>
            </div>
        </div>
        </div>
    </section>

    <!-- Waktu Terbaik untuk Mendaki -->
    <section class="py-20 bg-gradient-to-br from-white to-gray-100">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 animated-border auto-margin title-best-time" data-translate-key="best_time_title">Waktu Terbaik untuk Mendaki</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed" data-translate-key="best_time_subtitle">
                    Rencanakan pendakian Anda dengan informasi cuaca dan waktu yang optimal
                </p>
            </div>

            <div class="time-card-grid">
                <!-- Card 1: Musim Kemarau -->
                <div class="time-card season-card">
                    <div class="card-header text-center">
                        <div class="main-icon-container">
                            <i class="fas fa-cloud-sun"></i>
                        </div>
                        <h3>Musim Kemarau</h3>
                        <p>April - Oktober</p>
                    </div>
                    <ul class="card-list">
                        <!-- Item 1 -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper checkmark-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <p><strong>Jalur pendakian stabil</strong> tanpa hambatan genangan air</p>
                            </div>
                        </li>
                        <!-- Item 2 -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper checkmark-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <p><strong>Visibilitas sangat baik</strong> untuk menikmati pemandangan</p>
                            </div>
                        </li>
                        <!-- Item 3 -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper checkmark-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <p><strong>Suhu sejuk dan nyaman</strong> untuk pendakian</p>
                            </div>
                        </li>
                        <!-- Item 4 -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper checkmark-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <p><strong>Kondisi jalan kering</strong> memudahkan perjalanan</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Card 2: Waktu Ideal Sehari -->
                <div class="time-card daily-time-card">
                    <div class="card-header text-center">
                        <div class="main-icon-container">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Waktu Ideal Sehari</h3>
                        <p>Pendakian Harian</p>
                    </div>
                    <ul class="card-list">
                        <!-- Item 1 (Pagi Hari - Yellow) - Menggunakan fa-cloud-sun -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper time-icon yellow">
                                <i class="fas fa-cloud-sun"></i>
                            </div>
                            <div>
                                <p><strong>Pagi hari (04:00-06:00):</strong> Udara segar dan suhu nyaman</p>
                            </div>
                        </li>
                        <!-- Item 2 (Matahari Terbit - Orange) - Menggunakan fa-sun -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper time-icon orange">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <p><strong>Matahari terbit:</strong> Pemandangan menakjubkan dari puncak</p>
                            </div>
                        </li>
                        <!-- Item 3 (Hindari Siang - Red) -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper time-icon red">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <p><strong>Hindari siang (10:00-15:00):</strong> Suhu panas yang melelahkan</p>
                            </div>
                        </li>
                        <!-- Item 4 (Malam Hari - Indigo) -->
                        <li class="card-list-item">
                            <div class="list-icon-wrapper time-icon indigo">
                                <i class="fas fa-moon"></i>
                            </div>
                            <div>
                                <p><strong>Malam hari:</strong> Sempurna untuk stargazing</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Weather Forecast Section -->
            <div class="weather-container">
                <div class="main-card">
                    <header class="header">
                        <h1>Prakiraan Cuaca Mendatang</h1>
                        <p>Cuaca terkini dan perkiraan 3 hari ke depan untuk Gunung Butak</p>
                    </header>

                    <div id="weather-forecast" class="forecast-area">
                        <div class="loading">
                            <div class="spinner"></div>
                            <p>Memuat perkiraan cuaca Gunung Butak...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Peta Lokasi -->
    <section id="lokasi" class="section-location">
    <div class="container-custom auto-margin px-4">
        <div class="header-section text-center mb-16">
            <h2 class="title-main animated-border auto-margin" data-translate-key="location_title">Lokasi Gunung Butak</h2>
            <p class="subtitle-main" data-translate-key="location_subtitle">
                Temukan jalur pendakian melalui Basecamp Kucur dan rute terbaik menuju puncak
            </p>
        </div>

        <div class="card-grid-container">
            <div class="card-lokasi card-rute card-hover">
                <div class="card-inner-border">
                    <div class="card-content">
                        <h3 class="card-title text-center" data-translate-key="access_route_title">Rute Akses Menuju Basecamp</h3>

                        <div class="route-list-wrapper">

                            <div class="route-item">
                                <div class="icon-circle icon-bg-hijau">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="route-text-content">
                                    <h4 class="route-item-title" data-translate-key="basecamp_name">Basecamp Kucur</h4>
                                    <p class="route-item-desc" data-translate-key="basecamp_location">Kabupaten Malang, Jawa Timur</p>
                                </div>
                            </div>

                            <div class="route-item">
                                <div class="icon-circle icon-bg-hijau">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="route-text-content">
                                    <h4 class="route-item-title" data-translate-key="vehicle_access_title">Akses Kendaraan</h4>
                                    <p class="route-item-desc" data-translate-key="vehicle_access_desc">Dapat dijangkau dengan mobil atau motor, ketinggian 1.200 mdpl</p>
                                </div>
                            </div>

                            <div class="route-item">
                                <div class="icon-circle icon-bg-hijau">
                                    <i class="fas fa-walking"></i>
                                </div>
                                <div class="route-text-content">
                                    <h4 class="route-item-title" data-translate-key="climbing_route_title">Jalur Pendakian</h4>
                                    <p class="route-item-desc" data-translate-key="climbing_route_desc">Jalur utama sepanjang 8 km, perkiraan waktu 12-15 jam</p>
                                </div>
                            </div>

                            <div class="route-item">
                                <div class="icon-circle icon-bg-hijau">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="route-text-content">
                                    <h4 class="route-item-title" data-translate-key="basecamp_facilities_title">Fasilitas Basecamp</h4>
                                    <p class="route-item-desc" data-translate-key="basecamp_facilities_desc">Parkir, toilet, tempat istirahat, dan penyewaan peralatan</p>
                                </div>
                            </div>

                        </div>

                        <div class="travel-tips-container">
                            <h4 class="tips-title" data-translate-key="travel_tips_title">Tips Perjalanan:</h4>
                            <ul class="tips-list">
                                <li class="tip-item">
                                    <i class="fas fa-check-circle tip-icon"></i>
                                    <span data-translate-key="tip_1">Waktu tempuh dari Surabaya: 2-3 jam</span>
                                </li>
                                <li class="tip-item">
                                    <i class="fas fa-check-circle tip-icon"></i>
                                    <span data-translate-key="tip_2">Parkir aman tersedia di basecamp</span>
                                </li>
                                <li class="tip-item">
                                    <i class="fas fa-check-circle tip-icon"></i>
                                    <span data-translate-key="tip_3">Siapkan peralatan sesuai cuaca</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-lokasi card-map card-hover">
                <div class="map-inner-border">
                    <div class="map-content">
                        <div class="map-header-bar"></div>
                        <div class="map-iframe-wrapper">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.402258894248!2d112.546798!3d-7.965910!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zLTcuOTY1OTEsIDExMi41NDY3OTg!5e0!3m2!1sen!2sid!4v1650000000000!5m2!1sen!2sid"
                                width="100%"
                                height="500"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
            <!-- Interactive Map Features -->
            <div class="mt-20 grid-cols-1 md-grid-cols-3 gap-8">
                <div class="bg-gradient-br-green-blue rounded-3xl p-8 text-center feature-badge card-hover">
                    <i class="fas fa-route text-5xl text-green-600 mb-4"></i>
                    <h4 class="text-2xl font-bold text-gray-800 mb-3" data-translate-key="trekking_routes_title">Jalur Pendakian</h4>
                    <p class="text-gray-600" data-translate-key="trekking_routes_desc">Rute terbaik dengan pos-pos peristirahatan</p>
                </div>

                <div class="bg-gradient-br-orange-yellow rounded-3xl p-8 text-center feature-badge card-hover">
                    <i class="fas fa-exclamation-triangle text-5xl text-orange-600 mb-4"></i>
                    <h4 class="text-2xl font-bold text-gray-800 mb-3" data-translate-key="danger_points_title">Titik Bahaya</h4>
                    <p class="text-gray-600" data-translate-key="danger_points_desc">Area rawan longsor dan medan terjal</p>
                </div>

                <div class="bg-gradient-br-blue-indigo rounded-3xl p-8 text-center feature-badge card-hover">
                    <i class="fas fa-water text-5xl text-blue-600 mb-4"></i>
                    <h4 class="text-2xl font-bold text-gray-800 mb-3" data-translate-key="water_source_title">Sumber Air</h4>
                    <p class="text-gray-600" data-translate-key="water_source_desc">Lokasi sumber air bersih di sepanjang jalur</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimoni Pendaki -->
    <section id="testimoni" class="py-20 bg-gradient-to-br from-gray-100 to-white">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="testimonials_title">Apa Kata Mereka?</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed mb-12" data-translate-key="testimonials_subtitle">
                    Pendapat para pendaki yang telah menaklukkan puncak Gunung Butak
                </p>
            </div>

            <!-- Sliding Testimonials Section -->
            <div class="relative mb-16">
                <div class="overflow-hidden">
                    <div id="testimoni-container" class="flex-display transition-transform duration-500 ease-in-out">
                        <!-- Testimonials akan dimuat di sini oleh JavaScript -->
                        <p id="loading-message" class="text-center w-full text-gray-500">Memuat testimonial...</p>
                    </div>
                </div>

                <!-- Navigation Arrows -->
                <button id="prev-testimonial" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg z-10 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chevron-left text-primary text-xl"></i>
                </button>
                <button id="next-testimonial" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg z-10 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chevron-right text-primary text-xl"></i>
                </button>

                <!-- Pagination Dots -->
                <div id="testimonial-dots" class="flex-display justify-center mt-8 space-x-2"></div>
            </div>

            <!-- Form Komentar untuk Pengguna yang Login -->
            <div id="komentar-form-section">
                <div class="max-w-2xl auto-margin mb-16">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-primary animated-border auto-margin mb-4" data-translate-key="share_experience_title">Bagikan Pengalaman Anda</h2>
                        <p class="text-gray-600" data-translate-key="share_experience_subtitle">Tulis komentar dan beri penilaian untuk membantu pendaki lain</p>
                    </div>

                    <div class="feature-badge card-hover">
                        <div class="bg-gradient-primary-accent rounded-3xl p-1">
                            <div class="bg-white rounded-3xl shadow-2xl p-8">
                                <div id="komentar-error-message" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6"></div>
                                <div id="komentar-success-message" class="hidden bg-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6"></div>

                                <form id="komentarForm" class="space-y-6">
                                    <!-- ID Pengguna HARUS diambil dari sesi Auth Supabase di script.js -->
                                    <input type="hidden" id="user-id" value="anon_user_placeholder">
                                    <div>
                                        <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="comment_label">Komentar:</label>
                                        <textarea id="isi-komentar" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" placeholder="Tulis pengalaman Anda mendaki Gunung Butak..." data-translate-key="placeholder_comment_area"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="rating_label">Rating:</label>
                                        <div class="flex-display items-center">
                                            <div class="rating flex-display space-x-1">
                                                <input type="radio" id="star5" name="rating" value="5" class="hidden" />
                                                <label for="star5" class="text-3xl cursor-pointer text-gray-300 transition-colors">★</label>
                                                <input type="radio" id="star4" name="rating" value="4" class="hidden" />
                                                <label for="star4" class="text-3xl cursor-pointer text-gray-300 transition-colors">★</label>
                                                <input type="radio" id="star3" name="rating" value="3" class="hidden" />
                                                <label for="star3" class="text-3xl cursor-pointer text-gray-300 transition-colors">★</label>
                                                <input type="radio" id="star2" name="rating" value="2" class="hidden" />
                                                <label for="star2" class="text-3xl cursor-pointer text-gray-300 transition-colors">★</label>
                                                <input type="radio" id="star1" name="rating" value="1" class="hidden" />
                                                <label for="star1" class="text-3xl cursor-pointer text-gray-300 transition-colors">★</label>
                                            </div>
                                            <span id="rating-value" class="ml-4 text-xl text-gray-600" data-translate-key="select_rating">Pilih rating</span>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full bg-gradient-primary-accent hover:from-accent hover:to-primary text-white font-bold py-4 px-6 rounded-2xl transition-default hover-scale-105 shadow-2xl text-xl glow-button" data-translate-key="submit_comment">
                                        <i class="fas fa-paper-plane mr-3"></i>Kirim Komentar
                                    </button>
                                </form>

                                <!-- Message for non-logged in users -->
                                <div id="login-to-comment-message" class="hidden text-center py-8">
                                    <i class="fas fa-lock text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-xl font-bold text-gray-700 mb-2">Login Diperlukan</h3>
                                    <p class="text-gray-600 mb-4">Anda harus login untuk dapat memberikan komentar dan penilaian</p>
                                    <a href="auth/login.php" class="inline-block bg-gradient-primary-accent hover:from-accent hover:to-primary text-white font-bold py-3 px-6 rounded-2xl transition-default hover-scale-105 shadow-xl text-lg">
                                        Login Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="stats-container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="total-pendaki-rating">98%</div>
                        <p class="stat-label" data-translate-key="climber_satisfaction">Kepuasan Pendaki</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avg-rating">4.9/5</div>
                        <p class="stat-label" data-translate-key="avg_rating_label">Rating Rata-rata</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="total-komentar">5000+</div>
                        <p class="stat-label" data-translate-key="total_reviews">Total Ulasan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Poster Slider Section -->
    <section id="poster-slider" class="py-20 bg-gradient-to-br from-white to-gray-100">
        <div class="container auto-margin px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="promo_poster_title">Poster Promosi</h2>
                <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed mb-12" data-translate-key="promo_poster_subtitle">
                    Temukan informasi terbaru dan promosi menarik dari Gunung Butak
                </p>
            </div>

            <!-- Sliding Posters Section -->
            <div class="relative mb-16">
                <div class="overflow-hidden">
                    <div id="poster-container" class="flex-display transition-transform duration-500 ease-in-out">
                        <!-- Poster items will be dynamically loaded -->
                        <div class="poster-card animate-pulse w-full flex-shrink-0 px-4">
                            <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                                <div class="bg-gray-200 h-64 rounded-2xl"></div>
                            </div>
                        </div>
                        <div class="poster-card animate-pulse w-full flex-shrink-0 px-4">
                            <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                                <div class="bg-gray-200 h-64 rounded-2xl"></div>
                            </div>
                        </div>
                        <div class="poster-card animate-pulse w-full flex-shrink-0 px-4">
                            <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
                                <div class="bg-gray-200 h-64 rounded-2xl"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Arrows -->
                <button id="prev-poster" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg z-10 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chevron-left text-primary text-xl"></i>
                </button>
                <button id="next-poster" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-lg z-10 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chevron-right text-primary text-xl"></i>
                </button>

                <!-- Pagination Dots -->
                <div id="poster-dots" class="flex-display justify-center mt-8 space-x-2"></div>
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
                        <li><a href="#header" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_home">Beranda</a></li>
                        <li><a href="#tentang" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_about_us">Tentang Kami</a></li>
                        <li><a href="#lokasi" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_climbing_route">Jalur Pendakian</a></li>
                        <li><a href="#" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_gallery">Galeri</a></li>
                        <li><a href="#testimoni" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_contact">Kontak</a></li>
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

    <!-- JavaScript (Memasukkan logika Theme Toggle dan Translation) -->
    <script>
        // --- TRANSLATION DATA ---
        const translations = {
            'id': {
                page_title: "Gunung Butak | Reservasi Pendakian",
                header_title: "Gunung Butak | Reservasi Pendakian",
                login_button: "Login", logout_button: "Logout", logout_button_header: "Logout", greeting_text: "Selamat datang,",
                hero_title: "GUNUNG BUTAK", hero_subtitle: "Jelajahi keindahan alam dan tantangan ekstrem di puncak tertinggi Jawa Timur",
                reserve_now: "Reservasi Sekarang", location: "Lokasi Pendakian", climbers_this_year: "Pendaki Tahun Ini",
                experience_rating: "Rating Pengalaman", years_of_operation: "Tahun Operasional",
                latest_announcements_title: "Pengumuman Terbaru", latest_announcements_subtitle: "Informasi penting terkait pendakian Gunung Butak",
                loading_announcements: "Memuat pengumuman terbaru...", no_announcements_title: "Tidak Ada Pengumuman",
                no_announcements_subtitle: "Belum ada pengumuman aktif saat ini",
                why_butak_title: "Kenapa Memilih Gunung Butak?", why_butak_subtitle: "Temukan alasan mengapa Gunung Butak menjadi destinasi pendakian favorit para petualang",
                mountain_height_label: "Ketinggian Gunung", masl: "Meter di Atas Permukaan Laut", difficulty_level: "Sulit",
                difficulty_label: "Tingkat Kesulitan", difficulty_description: "Cocok untuk pendaki berpengalaman", duration_label: "Durasi Pendakian",
                ideal_duration: "Hari Pendakian Ideal", avg_temp_title: "Suhu Rata-rata", avg_temp_desc: "Nyaman untuk pendakian",
                wind_speed_title: "Kecepatan Angin", wind_speed_desc: "Angin segar di puncak", rainfall_title: "Curah Hujan",
                rainfall_level: "Rendah", rainfall_desc: "Kemungkinan hujan rendah", vegetation_title: "Vegetasi",
                vegetation_zones: "4 Zona", vegetation_desc: "Hutan-Sahara yang bervariasi",
                safety_facilities_title: "Keamanan dan Fasilitas", safety_guaranteed_title: "Keamanan Terjamin",
                safety_guaranteed_desc: "Pendakian aman dengan pemandu bersertifikat", complete_facilities_title: "Fasilitas Lengkap",
                complete_facilities_desc: "Basecamp lengkap dengan peralatan darurat", climber_community_title: "Komunitas Pendaki",
                climber_community_desc: "Ikuti komunitas pendakian aktif",
                about_butak_title: "Tentang Gunung Butak", about_butak_subtitle: "Kenali lebih dekat destinasi pendakian terbaik di Jawa Timur",
                history_title: "Sejarah dan Asal Usul",
                history_p1: "Gunung Butak adalah destinasi pendakian yang menawarkan keindahan alam yang luar biasa dan pengalaman mendaki yang menantang. Terletak di Jawa Timur, gunung ini memiliki ketinggian 2.868 meter di atas permukaan laut.",
                history_p2: "Dengan berbagai ekosistem yang berbeda dari hutan hingga sabana, Gunung Butak menjadi tempat yang ideal bagi para pendaki yang mencari keindahan alam sekaligus tantangan ekstrem.",
                fun_facts_title: "Fakta Menarik",
                fun_fact_1: 'Nama "Butak" berasal dari bahasa Jawa yang berarti \'telanjang\', merujuk pada puncaknya yang tidak berpohon',
                fun_fact_2: "Merupakan gunung tertinggi ke-3 di Jawa Timur", fun_fact_3: "Habitat bagi berbagai spesies langka flora dan fauna",
                fun_fact_4: "Lokasi hunting sunrise yang legendaris", our_advantages_title: "Keunggulan Kami",
                advantage_1: "Sistem reservasi online terintegrasi dan aman", advantage_2: "Pemandu berpengalaman bersertifikat nasional",
                advantage_3: "Basecamp lengkap dengan fasilitas memadai", advantage_4: "Komunitas pendaki aktif dan suportif",
                why_climb_title: "Mengapa Mendaki Gunung Butak?", why_climb_subtitle: "Temukan alasan mengapa Gunung Butak menjadi destinasi pendakian yang tak terlupakan",
                beautiful_scenery_title: "Pemandangan Indah", beautiful_scenery_desc: "Nikmati pemandangan alam yang luar biasa dari hutan hingga sabana yang menakjubkan di sepanjang jalur pendakian. Puncak Gunung Butak menawarkan pemandangan 360 derajat yang memukau.",
                biodiversity_title: "Keberagaman Flora & Fauna", biodiversity_desc: "Jelajahi berbagai ekosistem unik dan temukan berbagai spesies langka yang hanya ditemukan di kawasan ini. Habitat alami berbagai jenis burung dan satwa endemik Jawa.",
                vast_savanna_title: "Sabana Luas", vast_savanna_desc: "Rasakan keindahan luasnya padang sabana di kaki gunung, tempat yang sangat indah untuk berkemah dan menikmati alam. Sempurna untuk hunting sunrise dan stargazing.",
                climb_benefits_title: "Keuntungan Mendaki Bersama Kami", certified_guides_title: "Pemandu Bersertifikat",
                certified_guides_desc: "Pemandu profesional dengan sertifikasi nasional", guaranteed_safety_title: "Keamanan Terjamin",
                guaranteed_safety_desc: "Sistem keamanan terintegrasi dan SOP ketat", complete_basecamp_title: "Fasilitas Lengkap",
                complete_basecamp_desc: "Basecamp lengkap dengan peralatan standar", best_time_title: "Waktu Terbaik untuk Mendaki",
                best_time_subtitle: "Rencanakan pendakian Anda dengan informasi cuaca dan waktu yang optimal", dry_season_title: "Musim Kemarau",
                dry_season_months: "April - Oktober", dry_season_benefit_1: "<strong>Jalur pendakian stabil</strong> tanpa hambatan genangan air",
                dry_season_benefit_2: "<strong>Visibilitas sangat baik</strong> untuk menikmati pemandangan", dry_season_benefit_3: "<strong>Suhu sejuk dan nyaman</strong> untuk pendakian",
                dry_season_benefit_4: "<strong>Kondisi jalan kering</strong> memudahkan perjalanan", ideal_daily_time_title: "Waktu Ideal Sehari",
                daily_climb_label: "Pendakian Harian", daily_time_1: "<strong>Pagi hari (04:00-06:00):</strong> Udara segar dan suhu nyaman",
                daily_time_2: "<strong>Matahari terbit:</strong> Pemandangan menakjubkan dari puncak", daily_time_3: "<strong>Hindari siang (10:00-15:00):</strong> Suhu panas yang melelahkan",
                daily_time_4: "<strong>Malam hari:</strong> Sempurna untuk stargazing", weather_forecast_title: "Prakiraan Cuaca Mendatang",
                weather_forecast_subtitle: "Cuaca terkini dan perkiraan 3 hari ke depan untuk Gunung Butak", loading_weather: "Memuat perkiraan cuaca...",
                location_title: "Lokasi Gunung Butak", location_subtitle: "Temukan jalur pendakian melalui Basecamp Kucur dan rute terbaik menuju puncak",
                access_route_title: "Rute Akses Menuju Basecamp", basecamp_name: "Basecamp Kucur", basecamp_location: "Kabupaten Malang, Jawa Timur",
                vehicle_access_title: "Akses Kendaraan", vehicle_access_desc: "Dapat dijangkau dengan mobil atau motor, ketinggian 1.200 mdpl",
                climbing_route_title: "Jalur Pendakian", climbing_route_desc: "Jalur utama sepanjang 8 km, perkiraan waktu 12-15 jam",
                basecamp_facilities_title: "Fasilitas Basecamp", basecamp_facilities_desc: "Parkir, toilet, tempat istirahat, dan penyewaan peralatan",
                travel_tips_title: "Tips Perjalanan:", tip_1: "Waktu tempuh dari Surabaya: 2-3 jam", tip_2: "Parkir aman tersedia di basecamp",
                tip_3: "Siapkan peralatan sesuai cuaca", trekking_routes_title: "Jalur Pendakian", trekking_routes_desc: "Rute terbaik dengan pos-pos peristirahatan",
                danger_points_title: "Titik Bahaya", danger_points_desc: "Area rawan longsor dan medan terjal", water_source_title: "Sumber Air",
                water_source_desc: "Lokasi sumber air bersih di sepanjang jalur", testimonials_title: "Apa Kata Mereka?",
                testimonials_subtitle: "Pendapat para pendaki yang telah menaklukkan puncak Gunung Butak", share_experience_title: "Bagikan Pengalaman Anda",
                share_experience_subtitle: "Tulis komentar dan beri penilaian untuk membantu pendaki lain", comment_label: "Komentar:",
                select_rating: "Pilih rating", submit_comment: "Kirim Komentar", climber_satisfaction: "Kepuasan Pendaki",
                avg_rating_label: "Rating Rata-rata", total_reviews: "Total Ulasan", promo_poster_title: "Poster Promosi",
                promo_poster_subtitle: "Temukan informasi terbaru dan promosi menarik dari Gunung Butak", account_access_title: "Akses Akun",
                account_access_subtitle: "Login atau daftar untuk mengakses sistem pendakian", tab_login: "Login",
                tab_register: "Daftar", tab_forgot_password: "Lupa Password", email_label: "Email:",
                placeholder_enter_email: "Masukkan email Anda", password_label: "Password:", placeholder_enter_password: "Masukkan password Anda",
                remember_me: "Ingat saya", forgot_password_link: "Lupa password?", login_now_button: "Login Sekarang",
                no_account_yet: "Belum punya akun?", register_here: "Daftar di sini", full_name_label: "Nama Lengkap:",
                placeholder_enter_full_name: "Masukkan nama lengkap Anda", email_label_2: "Email:",
                placeholder_enter_email_2: "Masukkan email Anda", phone_number_label: "Nomor Telepon:",
                placeholder_enter_phone: "Masukkan nomor telepon Anda", password_label_2: "Password:",
                placeholder_enter_password_2: "Masukkan password Anda", confirm_password_label: "Konfirmasi Password:",
                placeholder_confirm_password: "Konfirmasi password Anda", register_now_button: "Daftar Sekarang",
                already_have_account: "Sudah punya akun?", login_here_2: "Login di sini", email_label_3: "Email:",
                placeholder_enter_email_3: "Masukkan email Anda", verification_code_info: "Kami akan mengirimkan kode verifikasi ke email Anda. Masukkan kode tersebut untuk mereset password Anda.",
                send_verification_button: "Kirim Kode Verifikasi", back_to_login: "Ingin kembali ke login?", login_here_3: "Login di sini",
                footer_title: "Gunung Butak", footer_description: "Platform reservasi pendakian Gunung Butak melalui jalur Kucur. Membantu pendaki untuk merencanakan dan menikmati petualangan alam mereka dengan aman dan menyenangkan.",
                navigation_title: "Navigasi", nav_home: "Beranda", nav_about_us: "Tentang Kami", nav_climbing_route: "Jalur Pendakian",
                nav_gallery: "Galeri", nav_contact: "Kontak", climbing_info_title: "Jalur Pendakian", info_route: "Jalur Utama: Kucur",
                info_difficulty: "Tingkat Kesulitan: Sulit", info_duration: "Durasi: 3-4 Hari", info_height: "Ketinggian: 2.868 mdpl",
                info_temperature: "Suhu: 15-22°C", contact_us_title: "Kontak Kami", contact_address: "Basecamp Kucur, Gunung Butak, Jawa Timur",
                contact_chat: "Live Chat 24/7", copyright: "&copy; 2025 Gunung Butak Reservasi Pendakian. All rights reserved.",
            },
            'en': {
                page_title: "Mount Butak | Climbing Reservation",
                header_title: "Mount Butak | Climbing Reservation",
                login_button: "Login", logout_button: "Logout", logout_button_header: "Logout", greeting_text: "Welcome,",
                hero_title: "MOUNT BUTAK", hero_subtitle: "Explore the natural beauty and extreme challenge on the highest peaks of East Java",
                reserve_now: "Reserve Now", location: "Climbing Location", climbers_this_year: "Climbers This Year",
                experience_rating: "Experience Rating", years_of_operation: "Years of Operation",
                latest_announcements_title: "Latest Announcements", latest_announcements_subtitle: "Important information regarding Mount Butak climbing",
                loading_announcements: "Loading latest announcements...", no_announcements_title: "No Announcements",
                no_announcements_subtitle: "No active announcements currently",
                why_butak_title: "Why Choose Mount Butak?", why_butak_subtitle: "Discover why Mount Butak is the favorite climbing destination for adventurers",
                mountain_height_label: "Mountain Height", masl: "Meters Above Sea Level", difficulty_level: "Difficult",
                difficulty_label: "Difficulty Level", difficulty_description: "Suitable for experienced climbers", duration_label: "Climbing Duration",
                ideal_duration: "Ideal Climbing Days", avg_temp_title: "Average Temperature", avg_temp_desc: "Comfortable for climbing",
                wind_speed_title: "Wind Speed", wind_speed_desc: "Fresh air at the peak", rainfall_title: "Rainfall",
                rainfall_level: "Low", rainfall_desc: "Low probability of rain", vegetation_title: "Vegetation",
                vegetation_zones: "4 Zones", vegetation_desc: "Varied Forest-Savanna",
                safety_facilities_title: "Safety and Facilities", safety_guaranteed_title: "Guaranteed Safety",
                safety_guaranteed_desc: "Safe climbing with certified guides", complete_facilities_title: "Complete Facilities",
                complete_facilities_desc: "Basecamp equipped with emergency gear", climber_community_title: "Climber Community",
                climber_community_desc: "Join the active climbing community",
                about_butak_title: "About Mount Butak", about_butak_subtitle: "Get to know the best climbing destination in East Java",
                history_title: "History and Origin",
                history_p1: "Mount Butak is a climbing destination that offers incredible natural beauty and a challenging climbing experience. Located in East Java, this mountain has an altitude of 2,868 meters above sea level.",
                history_p2: "With various distinct ecosystems from forest to savanna, Mount Butak is an ideal place for climbers seeking natural beauty and extreme challenges.",
                fun_facts_title: "Interesting Facts",
                fun_fact_1: 'The name "Butak" originates from Javanese meaning \'bare\', referring to its treeless peak',
                fun_fact_2: "It is the 3rd highest mountain in East Java", fun_fact_3: "Habitat for various rare flora and fauna species",
                fun_fact_4: "A legendary spot for sunrise hunting", our_advantages_title: "Our Advantages",
                advantage_1: "Integrated and secure online reservation system", advantage_2: "Experienced guides with national certification",
                advantage_3: "Complete basecamp with adequate facilities", advantage_4: "Active and supportive climbing community",
                why_climb_title: "Why Climb Mount Butak?", why_climb_subtitle: "Discover the reasons why Mount Butak is an unforgettable climbing destination",
                beautiful_scenery_title: "Beautiful Scenery", beautiful_scenery_desc: "Enjoy extraordinary natural views from the forest to the stunning savanna along the climbing route. Mount Butak Peak offers a captivating 360-degree panorama.",
                biodiversity_title: "Flora & Fauna Diversity", biodiversity_desc: "Explore various unique ecosystems and find rare species found only in this area. Natural habitat for various types of birds and endemic Javanese animals.",
                vast_savanna_title: "Vast Savanna", vast_savanna_desc: "Experience the vast beauty of the savanna plains at the foot of the mountain, an incredibly beautiful place for camping and enjoying nature. Perfect for sunrise hunting and stargazing.",
                climb_benefits_title: "Benefits of Climbing With Us", certified_guides_title: "Certified Guides",
                certified_guides_desc: "Professional guides with national certification", guaranteed_safety_title: "Guaranteed Safety",
                guaranteed_safety_desc: "Integrated safety system and strict SOP", complete_basecamp_title: "Complete Facilities",
                complete_basecamp_desc: "Basecamp equipped with standard gear", best_time_title: "Best Time to Climb",
                best_time_subtitle: "Plan your climb with optimal weather and time information", dry_season_title: "Dry Season",
                dry_season_months: "April - October", dry_season_benefit_1: "<strong>Stable climbing routes</strong> without waterlogging obstacles",
                dry_season_benefit_2: "<strong>Excellent visibility</strong> to enjoy the view", dry_season_benefit_3: "<strong>Cool and comfortable temperature</strong> for climbing",
                dry_season_benefit_4: "<strong>Dry road conditions</strong> ease the journey", ideal_daily_time_title: "Ideal Daily Time",
                daily_climb_label: "Daily Climb", daily_time_1: "<strong>Morning (04:00-06:00):</strong> Fresh air and comfortable temperature",
                daily_time_2: "<strong>Sunrise:</strong> Stunning views from the peak", daily_time_3: "<strong>Avoid noon (10:00-15:00):</strong> Exhausting heat",
                daily_time_4: "<strong>Night:</strong> Perfect for stargazing", weather_forecast_title: "Upcoming Weather Forecast",
                weather_forecast_subtitle: "Current weather and 3-day forecast for Mount Butak", loading_weather: "Loading weather forecast...",
                location_title: "Mount Butak Location", location_subtitle: "Find the climbing route through Kucur Basecamp and the best route to the peak",
                access_route_title: "Access Route to Basecamp", basecamp_name: "Kucur Basecamp", basecamp_location: "Malang Regency, East Java",
                vehicle_access_title: "Vehicle Access", vehicle_access_desc: "Accessible by car or motorcycle, altitude 1,200 masl",
                climbing_route_title: "Climbing Route", climbing_route_desc: "Main route along 8 km, estimated time 12-15 hours",
                basecamp_facilities_title: "Basecamp Facilities", basecamp_facilities_desc: "Parking, toilet, rest area, and equipment rental",
                travel_tips_title: "Travel Tips:", tip_1: "Travel time from Surabaya: 2-3 hours", tip_2: "Secure parking available at basecamp",
                tip_3: "Prepare gear according to weather", trekking_routes_title: "Trekking Routes", trekking_routes_desc: "Best routes with rest posts",
                danger_points_title: "Danger Points", danger_points_desc: "Landslide-prone area and steep terrain", water_source_title: "Water Sources",
                water_source_desc: "Location of clean water sources along the trail", testimonials_title: "What They Say?",
                testimonials_subtitle: "Opinions of climbers who have conquered the peak of Mount Butak", share_experience_title: "Share Your Experience",
                share_experience_subtitle: "Write a comment and give a rating to help other climbers", comment_label: "Comment:",
                select_rating: "Select rating", submit_comment: "Submit Comment", climber_satisfaction: "Climber Satisfaction",
                avg_rating_label: "Average Rating", total_reviews: "Total Reviews", promo_poster_title: "Promotional Posters",
                promo_poster_subtitle: "Find the latest information and attractive promotions from Mount Butak", account_access_title: "Account Access",
                account_access_subtitle: "Login or register to access the climbing system", tab_login: "Login",
                tab_register: "Register", tab_forgot_password: "Forgot Password", email_label: "Email:",
                placeholder_enter_email: "Enter your email", password_label: "Password:", placeholder_enter_password: "Enter your password",
                remember_me: "Remember me", forgot_password_link: "Forgot password?", login_now_button: "Login Now",
                no_account_yet: "Don't have an account yet?", register_here: "Register here", full_name_label: "Full Name:",
                placeholder_enter_full_name: "Enter your full name", email_label_2: "Email:",
                placeholder_enter_email_2: "Enter your email", phone_number_label: "Phone Number:",
                placeholder_enter_phone: "Enter your phone number", password_label_2: "Password:",
                placeholder_enter_password_2: "Enter your password", confirm_password_label: "Confirm Password:",
                placeholder_confirm_password: "Confirm your password", register_now_button: "Register Now",
                already_have_account: "Already have an account?", login_here_2: "Login here", email_label_3: "Email:",
                placeholder_enter_email_3: "Enter your email", verification_code_info: "We will send a verification code to your email. Enter the code to reset your password.",
                send_verification_button: "Send Verification Code", back_to_login: "Want to return to login?", login_here_3: "Login here",
                footer_title: "Mount Butak", footer_description: "Mount Butak climbing reservation platform via the Kucur route. Helping climbers plan and enjoy their natural adventure safely and pleasantly.",
                navigation_title: "Navigation", nav_home: "Home", nav_about_us: "About Us", nav_climbing_route: "Climbing Route",
                nav_gallery: "Gallery", nav_contact: "Contact", climbing_info_title: "Climbing Route", info_route: "Main Route: Kucur",
                info_difficulty: "Difficulty Level: Difficult", info_duration: "Duration: 3-4 Days", info_height: "Height: 2,868 masl",
                info_temperature: "Temperature: 15-22°C", contact_us_title: "Contact Us", contact_address: "Kucur Basecamp, Mount Butak, East Java",
                contact_chat: "Live Chat 24/7", copyright: "&copy; 2025 Mount Butak Climbing Reservation. All rights reserved.",
            }
        };

        let currentLang = 'id'; // Default language

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
        const langToggle = document.getElementById('lang-toggle');

        function translateElement(element) {
            const key = element.getAttribute('data-translate-key');
            if (key && translations[currentLang][key]) {
                const translation = translations[currentLang][key];

                // Cek apakah elemen input/placeholder
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.placeholder) {
                         element.placeholder = translation;
                    } else if (element.value) {
                         element.value = translation;
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

            // Handle placeholders manual (Dibutuhkan untuk elemen tanpa data-translate-key)
            // (Placeholder di bawah sudah ditangani dengan baik oleh logic utama)

            // Karena elemen input tidak memiliki data-translate-key untuk placeholder, kita perlu melakukan pembaruan langsung di sini.
            // Namun, dalam HTML, kita telah menambahkan data-translate-key pada placeholder. Mari kita pastikan semua placeholder ter-update.
            document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
                const key = el.getAttribute('data-translate-key');
                if (key && translations[lang][key]) {
                    el.placeholder = translations[lang][key];
                }
            });

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

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.feature-card');

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const card = entry.target;
                        const index = Array.from(cards).indexOf(card);

                        // Add a sequential delay based on the card's position
                        setTimeout(() => {
                            card.classList.add('is-visible');
                            observer.unobserve(card); // Stop observing once it's visible
                        }, index * 200); // 200ms delay between each card
                    }
                });
            }, {
                threshold: 0.1 // Triggers when 10% of the card is visible
            });

            cards.forEach(card => {
                observer.observe(card);
            });
        });

        // --- INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', function() {
            loadTheme();
            loadLanguage(); // Muat bahasa setelah tema

            // Existing JS logic...

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Enhanced scroll to top functionality
            const scrollToTopBtn = document.createElement('div');
            scrollToTopBtn.className = 'scroll-to-top visible';
            scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            document.body.appendChild(scrollToTopBtn);

            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Add animation on scroll for elements
            function animateOnScroll() {
                const elements = document.querySelectorAll('.card-hover, .bg-white, .testimonial-card, .feature-badge');
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;

                    if (elementPosition < screenPosition) {
                        if (element.style.opacity !== '1') {
                            element.style.opacity = '1';
                            element.style.transform = 'translateY(0)';
                        }
                    }
                });
            }

            window.addEventListener('scroll', animateOnScroll);

            // Initialize elements with opacity 0 for animation
            const elements = document.querySelectorAll('.card-hover, .bg-white, .testimonial-card, .feature-badge');
            elements.forEach(element => {
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
            });

            // Trigger initial animation check
            setTimeout(animateOnScroll, 100);

            // Add floating effect to hero section (The new CSS defines the .floating animation)
            const heroElements = document.querySelectorAll('.hero-background h1, .hero-background h2, .hero-background p, .hero-background a, .hero-background .counter, .hero-content');
            heroElements.forEach(el => {
                 if (el.classList.contains('hero-content')) {
                    el.classList.add('floating');
                }
            });

            // Enhanced counter animation for stats
            function animateCounter(element, target, duration) {
                let start = 0;
                const increment = target / (duration / 16);
                const timer = setInterval(() => {
                    start += increment;
                    element.textContent = Math.floor(start);

                    if (start >= target) {
                        element.textContent = target.toLocaleString('id-ID'); // Format numbers
                        clearInterval(timer);
                    }
                }, 16);
            }

            // Activate counters when they come into view
            const observerOptions = {
                root: null, rootMargin: '0px', threshold: 0.5
            };

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const numberElements = entry.target.querySelectorAll('.gradient-text, [id$="-rating"], [id$="-komentar"]');
                        numberElements.forEach(counter => {
                            const text = counter.textContent.replace(/[^0-9.]/g, '');
                            let target = parseFloat(text);
                            if (counter.textContent.endsWith('+')) {
                                target = target * 1000;
                            }
                            if (!isNaN(target)) {
                                counter.textContent = '0';
                                animateCounter(counter, target, 2000);
                                counterObserver.unobserve(entry.target);
                            }
                        });
                    }
                });
            }, observerOptions);

            // Observe counter elements
            document.querySelectorAll('.stat-card, .bg-gradient-to-r, .bg-gradient-primary-accent').forEach(card => {
                counterObserver.observe(card);
            });

            // Enhanced parallax effect to hero background
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelector('.hero-background');
                if (parallax) {
                    const speed = scrolled * 0.3;
                    parallax.style.backgroundPosition = `center calc(50% + ${speed}px)`;
                }
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.pulse-button, .glow-button');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (window.getComputedStyle(this).position === 'static') {
                        this.style.position = 'relative';
                    }
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');

                    const existingRipple = this.querySelector('.ripple');
                    if (existingRipple) { existingRipple.remove(); }
                    this.appendChild(ripple);

                    setTimeout(() => { ripple.remove(); }, 600);
                });
            });

            // Add typing animation to hero text
            function animateHeroText() {
                const heroContents = document.querySelectorAll('.hero-content > *');
                heroContents.forEach((el, index) => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 1s ease';
                    setTimeout(() => { el.style.opacity = '1'; }, 500 + (index * 200));
                });
            }
            setTimeout(animateHeroText, 500);

            // Add particle effect to hero section
            function createParticles() {
                const hero = document.querySelector('.hero-background');
                if (!hero) return;
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'absolute w-1 h-1 bg-white rounded-full opacity-30';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animation = `float ${Math.random() * 10 + 5}s infinite ease-in-out`;
                    hero.appendChild(particle);
                }
            }
            window.addEventListener('load', createParticles);

            // Add scroll progress indicator
            function addScrollProgress() {
                const progressContainer = document.createElement('div');
                progressContainer.id = 'scroll-progress';
                progressContainer.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 0%; height: 3px;
                    background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
                    z-index: 9999; transition: width 0.1s ease;
                `;
                document.body.appendChild(progressContainer);
                window.addEventListener('scroll', () => {
                    const scrollTop = window.pageYOffset;
                    const windowHeight = document.documentElement.scrollHeight - window.innerHeight;
                    const scrollPercent = (scrollTop / windowHeight) * 100;
                    progressContainer.style.width = scrollPercent + '%';
                });
            }
            addScrollProgress();

            // Add enhanced navigation highlighting
            function highlightNavOnScroll() {
                const sections = document.querySelectorAll('section[id]');
                const navLinks = document.querySelectorAll('.nav-link');
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (pageYOffset >= (sectionTop - 200)) {
                        current = section.getAttribute('id');
                    }
                });
                navLinks.forEach(link => {
                    link.classList.remove('text-accent');
                    if (link.getAttribute('href') === `#${current}`) {
                        link.classList.add('text-accent');
                    }
                });
            }
            window.addEventListener('scroll', highlightNavOnScroll);

            // Tab switching logic for Auth Section (with null checks)
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const forgotTab = document.getElementById('forgot-tab');
            const loginContent = document.getElementById('login-content');
            const registerContent = document.getElementById('register-content');
            const forgotContent = document.getElementById('forgot-content');

            function switchAuthTab(tabName) {
                const tabs = [loginTab, registerTab, forgotTab].filter(Boolean);
                const contents = [loginContent, registerContent, forgotContent].filter(Boolean);

                tabs.forEach(tab => {
                    if (tab) {
                        tab.classList.remove('text-blue-600', 'border-blue-600');
                        tab.classList.add('text-gray-500', 'border-transparent');
                    }
                });

                contents.forEach(content => {
                    if (content) {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                    }
                });

                if (tabName === 'login' && loginTab && loginContent) {
                    loginTab.classList.add('text-blue-600', 'border-blue-600');
                    loginTab.classList.remove('text-gray-500', 'border-transparent');
                    loginContent.classList.add('block');
                    loginContent.classList.remove('hidden');
                } else if (tabName === 'register' && registerTab && registerContent) {
                    registerTab.classList.add('text-blue-600', 'border-blue-600');
                    registerTab.classList.remove('text-gray-500', 'border-transparent');
                    registerContent.classList.add('block');
                    registerContent.classList.remove('hidden');
                } else if (tabName === 'forgot' && forgotTab && forgotContent) {
                    forgotTab.classList.add('text-blue-600', 'border-blue-600');
                    forgotTab.classList.remove('text-gray-500', 'border-transparent');
                    forgotContent.classList.add('block');
                    forgotContent.classList.remove('hidden');
                }
            }

            if (loginTab) loginTab.addEventListener('click', () => switchAuthTab('login'));
            if (registerTab) registerTab.addEventListener('click', () => switchAuthTab('register'));
            if (forgotTab) forgotTab.addEventListener('click', () => switchAuthTab('forgot'));

            const showRegister = document.getElementById('show-register');
            if (showRegister) showRegister.addEventListener('click', () => switchAuthTab('register'));

            const showLogin = document.getElementById('show-login');
            if (showLogin) showLogin.addEventListener('click', () => switchAuthTab('login'));

            const forgotPasswordLink = document.getElementById('forgot-password-link');
            if (forgotPasswordLink) forgotPasswordLink.addEventListener('click', () => switchAuthTab('forgot'));

            const showLoginFromForgot = document.getElementById('show-login-from-forgot');
            if (showLoginFromForgot) showLoginFromForgot.addEventListener('click', () => switchAuthTab('login'));

            // Rating interaction logic
            const ratingInputs = document.querySelectorAll('.rating input[type="radio"]');
            const ratingValueSpan = document.getElementById('rating-value');
            if (ratingInputs && ratingValueSpan) {
                ratingInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        ratingValueSpan.textContent = `${input.value} dari 5`;
                    });
                });
            }

            // Initialize the authentication UI based on PHP session status
            async function initializeAuthUI() {
                const authContainer = document.getElementById('auth-container');
                const userGreeting = document.getElementById('user-greeting');
                const userFullname = document.getElementById('user-fullname');
                const greetingText = document.getElementById('greeting-text');

                // First, check if Supabase auth session exists
                let isLoggedIn = false;
                let userName = null;

                try {
                    // Make sure supabase is initialized
                    if (window.supabase) {
                        const { data: { session }, error } = await supabase.auth.getSession();
                        if (session) {
                            isLoggedIn = true;

                            // Try to get user name from Supabase profile
                            const { data: profileData, error: profileError } = await supabase
                                .from('profiles')
                                .select('nama_lengkap')
                                .eq('id', session.user.id)
                                .single();

                            if (profileData) {
                                userName = profileData.nama_lengkap;
                            }
                        }
                    }
                } catch (err) {
                    console.error('Error checking Supabase session:', err);
                }

                // Fallback to PHP session data if Supabase doesn't have a valid session
                if (!isLoggedIn) {
                    const phpIsLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
                    const phpUserName = <?php echo json_encode($user_name); ?>;

                    if (phpIsLoggedIn && phpUserName) {
                        isLoggedIn = true;
                        userName = phpUserName;
                    }
                }

                if (isLoggedIn && userName) {
                    // User is logged in - show user greeting and hide login button
                    if (authContainer) {
                        authContainer.classList.add('hidden');
                    }
                    if (userGreeting) {
                        userGreeting.classList.remove('hidden');
                    }
                    if (userFullname) {
                        userFullname.textContent = userName;
                    }
                    if (greetingText) {
                        greetingText.textContent = 'Selamat datang,';
                    }
                } else {
                    // User is not logged in - show login button and hide user greeting
                    if (authContainer) {
                        authContainer.classList.remove('hidden');
                    }
                    if (userGreeting) {
                        userGreeting.classList.add('hidden');
                    }
                }
            }

            // Initialize the auth UI when the DOM is loaded
            document.addEventListener('DOMContentLoaded', async function() {
                await initializeAuthUI();

                // Set up auth state change listener to keep UI updated
                if (typeof supabase !== 'undefined') {
                    supabase.auth.onAuthStateChange((event, session) => {
                        console.log('Auth state changed:', event);
                        initializeAuthUI(); // Reinitialize UI when auth state changes
                    });
                }
            });

            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    // Call the handleRegister function from main.js
                    if (typeof handleRegister === 'function') {
                        handleRegister(e);
                    }
                });
            }

            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    // Call the handleLogin function from main.js
                    if (typeof handleLogin === 'function') {
                        handleLogin(e);
                    }
                });
            }

            // Function to update user ID field when user is logged in
            async function updateUserIdField() {
                const userIdField = document.getElementById('user-id');
                if (!userIdField) return;

                try {
                    // Check if Supabase client is available
                    let supabaseClient = null;
                    if (window.supabase && window.supabase.auth) {
                        supabaseClient = window.supabase;
                    } else if (window.supabaseClient && window.supabaseClient.auth) {
                        supabaseClient = window.supabaseClient;
                    }

                    if (!supabaseClient) {
                        console.error('Supabase client not initialized');
                        userIdField.value = 'anon_user_placeholder';
                        return;
                    }

                    // Try to get user from Supabase - this is what we need for database operations
                    try {
                        const { data: { user }, error } = await supabaseClient.auth.getUser();
                        if (error) {
                            console.warn('Could not get user from Supabase:', error.message);
                            // User is not logged in via Supabase, which is required for comments
                            userIdField.value = 'anon_user_placeholder';
                            return;
                        }

                        if (user) {
                            userIdField.value = user.id;
                        } else {
                            userIdField.value = 'anon_user_placeholder';
                        }
                    } catch (error) {
                        console.error('Error getting user from Supabase:', error);
                        userIdField.value = 'anon_user_placeholder';
                    }
                } catch (error) {
                    console.error('Error updating user ID field:', error);
                    userIdField.value = 'anon_user_placeholder';
                }
            }

            // Only add the inline handler if komentar.js is not loaded or not working
            const komentarForm = document.getElementById('komentarForm');
            if (komentarForm && typeof handleFormSubmit !== 'function') {
                komentarForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const komentar = document.getElementById('isi-komentar').value;
                    const rating = document.querySelector('input[name="rating"]:checked');
                    if (!komentar || !rating) {
                        document.getElementById('komentar-error-message').textContent = 'Harap isi komentar dan berikan rating.';
                        document.getElementById('komentar-error-message').classList.remove('hidden');
                        document.getElementById('komentar-success-message').classList.add('hidden');
                        return;
                    }

                    try {
                        // Check if Supabase client is initialized
                        let supabaseClient = null;
                        if (window.supabase && window.supabase.auth) {
                            supabaseClient = window.supabase;
                        } else if (window.supabaseClient && window.supabaseClient.auth) {
                            supabaseClient = window.supabaseClient;
                        }

                        if (!supabaseClient) {
                            throw new Error('Koneksi ke database tidak tersedia.');
                        }

                        // Get user from Supabase - this is required for database operations
                        let user = null;
                        try {
                            const supabaseResult = await supabaseClient.auth.getUser();
                            if (supabaseResult.error) {
                                throw new Error('Anda harus login terlebih dahulu untuk mengirim komentar. Silakan login dan coba kembali.');
                            }
                            user = supabaseResult.data.user;
                        } catch (supabaseError) {
                            console.error('Error getting user from Supabase:', supabaseError);
                            throw new Error('Gagal mengakses informasi pengguna. Silakan login kembali.');
                        }

                        if (!user) {
                            throw new Error('Anda harus login untuk mengirim komentar.');
                        }

                        // Prepare the comment data
                        const commentData = {
                            id_pengguna: user.id,
                            komentar: komentar,
                            rating: parseInt(rating.value)
                        };

                        // Insert to database
                        const { error: insertError } = await supabaseClient
                            .from('komentar')
                            .insert([commentData]);

                        if (insertError) throw insertError;

                        // Show success message
                        document.getElementById('komentar-success-message').textContent = `Komentar Anda telah terkirim! Rating: ${rating.value}/5`;
                        document.getElementById('komentar-success-message').classList.remove('hidden');
                        document.getElementById('komentar-error-message').classList.add('hidden');

                        // Reset form
                        document.getElementById('isi-komentar').value = '';
                        rating.checked = false;
                        if(document.getElementById('rating-value')) document.getElementById('rating-value').textContent = 'Pilih rating';

                        // Reload testimonials to show new comment
                        setTimeout(() => {
                            if(typeof loadTestimonials === 'function') {
                                loadTestimonials(true);
                            } else if(typeof window.loadTestimonials === 'function') {
                                window.loadTestimonials(true);
                            }
                        }, 1000);

                    } catch (error) {
                        console.error('Error submitting comment:', error);
                        document.getElementById('komentar-error-message').textContent = error.message || 'Gagal mengirim komentar. Silakan coba lagi.';
                        document.getElementById('komentar-error-message').classList.remove('hidden');
                        document.getElementById('komentar-success-message').classList.add('hidden');
                    }
                });
            }

            // Function to update comment form visibility based on login status
            async function updateCommentFormVisibility() {
                try {
                    // Check if Supabase client is available
                    let supabaseClient = null;
                    if (window.supabase && window.supabase.auth) {
                        supabaseClient = window.supabase;
                    } else if (window.supabaseClient && window.supabaseClient.auth) {
                        supabaseClient = window.supabaseClient;
                    }

                    const commentForm = document.getElementById('komentarForm');
                    const loginMessage = document.getElementById('login-to-comment-message');

                    if (commentForm && loginMessage) {
                        // For comment form to be visible, user must be logged in via Supabase
                        // since that's required for database operations
                        if (supabaseClient) {
                            try {
                                const { data: { user }, error } = await supabaseClient.auth.getUser();
                                if (!error && user) {
                                    // User is logged in via Supabase, show the form
                                    commentForm.classList.remove('hidden');
                                    loginMessage.classList.add('hidden');
                                } else {
                                    // User is not logged in via Supabase, show login message
                                    commentForm.classList.add('hidden');
                                    loginMessage.classList.remove('hidden');
                                }
                            } catch (error) {
                                console.error('Error checking Supabase auth status:', error);
                                // On error with Supabase auth, show login message
                                commentForm.classList.add('hidden');
                                loginMessage.classList.remove('hidden');
                            }
                        } else {
                            // If Supabase client isn't available, show login message
                            commentForm.classList.add('hidden');
                            loginMessage.classList.remove('hidden');
                        }
                    }
                } catch (error) {
                    console.error('Error updating comment form visibility:', error);
                    // On error, default to showing login message
                    const commentForm = document.getElementById('komentarForm');
                    const loginMessage = document.getElementById('login-to-comment-message');
                    if (commentForm && loginMessage) {
                        commentForm.classList.add('hidden');
                        loginMessage.classList.remove('hidden');
                    }
                }
            }

            // Update user ID field and form visibility when auth state changes and when DOM is loaded
            document.addEventListener('DOMContentLoaded', async () => {
                // Add a small delay to ensure Supabase is initialized
                await new Promise(resolve => setTimeout(resolve, 100));

                // Run the update functions after initialization
                updateUserIdField();
                updateCommentFormVisibility();

                // Set up auth state change listeners if Supabase is available
                if (window.supabase && window.supabase.auth) {
                    window.supabase.auth.onAuthStateChange((event, session) => {
                        updateUserIdField();
                        updateCommentFormVisibility();
                    });
                } else if (window.supabaseClient && window.supabaseClient.auth) {
                    window.supabaseClient.auth.onAuthStateChange((event, session) => {
                        updateUserIdField();
                        updateCommentFormVisibility();
                    });
                }
            });

            // Add click handlers for header logout buttons to use main.js handleLogout function
            const logoutLink = document.getElementById('logout-link');
            const logoutLinkHeader = document.getElementById('logout-link-header');

            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof handleLogout === 'function') {
                        handleLogout();
                    }
                });
            }

            if (logoutLinkHeader) {
                logoutLinkHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof handleLogout === 'function') {
                        handleLogout();
                    }
                });
            }
        });

    </script>

    <!-- Load Supabase config first to create the global client -->
    <script src="<?php echo $asset_url; ?>/js/config.js"></script>

    <!-- Load other scripts that depend on the global supabaseClient -->
    <script src="<?php echo $asset_url; ?>/js/main.js"></script>
    <script src="<?php echo $asset_url; ?>/js/index.js"></script>
    <script src="<?php echo $asset_url; ?>/js/sliding-komentar.js"></script>
    <script src="<?php echo $asset_url; ?>/js/weather-forecast.js"></script>
    <script src="<?php echo $asset_url; ?>/js/poster-slider.js"></script>
    <script src="<?php echo $asset_url; ?>/js/komentar.js"></script>

    <!-- Close the main content div -->
    </div>
</body>
</html>