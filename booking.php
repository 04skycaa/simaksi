<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Pendakian - Gunung Butak</title>
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

        <!-- Booking Section -->
        <section class="py-20 bg-gradient-br-gray-white">
            <div class="container auto-margin px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-primary mb-4 animated-border auto-margin" data-translate-key="booking_title">Formulir Reservasi Pendakian</h2>
                    <p class="text-xl text-gray-600 max-w-3xl auto-margin leading-relaxed" data-translate-key="booking_subtitle">
                        Isi formulir berikut untuk melakukan reservasi pendakian Gunung Butak
                    </p>
                </div>

                <div class="max-w-4xl auto-margin">
                    <div class="feature-badge card-hover">
                        <div class="bg-gradient-primary-accent rounded-3xl p-1">
                            <div class="bg-white rounded-3xl shadow-2xl p-8">
                                <form id="bookingForm" class="space-y-6">
                                    <div class="grid-cols-1 md-grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="full_name_label">Nama Lengkap:</label>
                                            <input type="text" id="full-name" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" placeholder="Masukkan nama lengkap Anda" data-translate-key="placeholder_enter_full_name">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="email_label">Email:</label>
                                            <input type="email" id="email" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" placeholder="Masukkan email Anda" data-translate-key="placeholder_enter_email">
                                        </div>
                                    </div>

                                    <div class="grid-cols-1 md-grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="phone_number_label">Nomor Telepon:</label>
                                            <input type="tel" id="phone" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" placeholder="Masukkan nomor telepon Anda" data-translate-key="placeholder_enter_phone">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="booking_date_label">Tanggal Pendakian:</label>
                                            <input type="date" id="booking-date" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg">
                                        </div>
                                    </div>

                                    <div class="grid-cols-1 md-grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="person_count_label">Jumlah Pendaki:</label>
                                            <input type="number" id="person-count" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" min="1" value="1">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="payment_method_label">Metode Pembayaran:</label>
                                            <select id="payment-method" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg">
                                                <option value="" data-translate-key="select_payment">Pilih Metode Pembayaran</option>
                                                <option value="transfer" data-translate-key="payment_transfer">Transfer Bank</option>
                                                <option value="cash" data-translate-key="payment_cash">Cash di Tempat</option>
                                                <option value="card" data-translate-key="payment_card">Kartu Kredit/Debit</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-gray-800 text-lg font-bold mb-3" data-translate-key="additional_notes_label">Catatan Tambahan:</label>
                                        <textarea id="additional-notes" class="w-full px-4 py-4 rounded-2xl border border-gray-300 input-style transition-default text-lg" placeholder="Catatan tambahan atau permintaan khusus..." rows="4"></textarea>
                                    </div>

                                    <div class="flex-display items-center">
                                        <input type="checkbox" id="terms" class="mr-3 h-5 w-5 text-primary rounded">
                                        <label for="terms" class="text-gray-700" data-translate-key="accept_terms">
                                            Saya menyetujui syarat dan ketentuan serta kebijakan privasi
                                        </label>
                                    </div>

                                    <button type="submit" class="w-full bg-gradient-primary-accent hover:from-accent hover:to-primary text-white font-bold py-4 px-6 rounded-2xl transition-default hover-scale-105 shadow-2xl text-xl glow-button" data-translate-key="submit_booking">
                                        <i class="fas fa-paper-plane mr-3"></i>Konfirmasi Reservasi
                                    </button>
                                </form>
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
                            <li><a href="booking.php" class="nav-link text-gray-300 hover:text-accent transition-default block py-1 text-accent" data-translate-key="nav_booking">Reservasi</a></li>
                            <li><a href="index.php#testimoni" class="nav-link text-gray-300 hover:text-accent transition-default block py-1" data-translate-key="nav_contact">Testimoni</a></li>
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
                booking_title: "Formulir Reservasi Pendakian",
                booking_subtitle: "Isi formulir berikut untuk melakukan reservasi pendakian Gunung Butak",
                full_name_label: "Nama Lengkap:",
                placeholder_enter_full_name: "Masukkan nama lengkap Anda",
                email_label: "Email:",
                placeholder_enter_email: "Masukkan email Anda",
                phone_number_label: "Nomor Telepon:",
                placeholder_enter_phone: "Masukkan nomor telepon Anda",
                booking_date_label: "Tanggal Pendakian:",
                person_count_label: "Jumlah Pendaki:",
                payment_method_label: "Metode Pembayaran:",
                select_payment: "Pilih Metode Pembayaran",
                payment_transfer: "Transfer Bank",
                payment_cash: "Cash di Tempat",
                payment_card: "Kartu Kredit/Debit",
                additional_notes_label: "Catatan Tambahan:",
                accept_terms: "Saya menyetujui syarat dan ketentuan serta kebijakan privasi",
                submit_booking: "Konfirmasi Reservasi",
                navigation_title: "Navigasi",
                nav_home: "Beranda", 
                nav_about_us: "Tentang Kami",
                nav_climbing_route: "Jalur Pendakian",
                nav_booking: "Reservasi",
                nav_contact: "Testimoni",
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
                booking_title: "Climbing Reservation Form",
                booking_subtitle: "Fill out the following form to make a Mount Butak climbing reservation",
                full_name_label: "Full Name:",
                placeholder_enter_full_name: "Enter your full name",
                email_label: "Email:",
                placeholder_enter_email: "Enter your email",
                phone_number_label: "Phone Number:",
                placeholder_enter_phone: "Enter your phone number",
                booking_date_label: "Climbing Date:",
                person_count_label: "Number of Climbers:",
                payment_method_label: "Payment Method:",
                select_payment: "Select Payment Method",
                payment_transfer: "Bank Transfer",
                payment_cash: "Cash on Site",
                payment_card: "Credit/Debit Card",
                additional_notes_label: "Additional Notes:",
                accept_terms: "I agree to the terms and conditions and privacy policy",
                submit_booking: "Confirm Reservation",
                navigation_title: "Navigation",
                nav_home: "Home", 
                nav_about_us: "About Us",
                nav_climbing_route: "Climbing Route",
                nav_booking: "Reservation",
                nav_contact: "Testimonials",
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

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple form validation
            const fullName = document.getElementById('full-name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const bookingDate = document.getElementById('booking-date').value;
            const terms = document.getElementById('terms').checked;
            
            if (!fullName || !email || !phone || !bookingDate) {
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return;
            }
            
            if (!terms) {
                alert('Mohon setujui syarat dan ketentuan terlebih dahulu.');
                return;
            }
            
            // Form submission logic would go here
            alert('Reservasi berhasil dikonfirmasi! Kami akan menghubungi Anda segera.');
            this.reset();
        });

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