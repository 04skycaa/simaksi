(function() {

// Pastikan Anda telah mengimpor library Supabase di HTML Anda sebelum script ini
// <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

// --- 1. KONFIGURASI SUPABASE ---
// !!! PENTING: GANTI DENGAN KREDENSIAL SUPABASE ANDA YANG SEBENARNYA !!!
const SUPABASE_URL = 'https://kitxtcpfnccblznbagzx.supabase.co'; 
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk1ODIxMzEsImV4cCI6MjA3NTE1ODEzMX0.OySigpw4AWI3G7JW_8r8yXu7re0Mr9CYv8u3d9Fr548'; 
// !!! JANGAN LUPA DIGANTI !!!

// 'supabase' di sini sekarang bersifat lokal dalam fungsi (IIFE) ini.
// Perbaikan: Akses objek Supabase global yang diekspos oleh library (biasanya window.supabase).
// Kita asumsikan library Supabase dimuat dan menyediakan objek global 'supabase'.
const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// Nama tabel sesuai dengan struktur database Anda (komentar)
const TABLE_NAME = 'komentar'; 

// --- 2. FUNGSI UTAMA UNTUK MEMUAT TESTIMONIAL ---

async function loadTestimonials() {
    const container = document.getElementById('testimoni-container');
    const loadingMessage = document.getElementById('loading-message');
    container.innerHTML = ''; // Kosongkan kontainer
    
    // Tampilkan pesan loading
    container.appendChild(loadingMessage); 
    loadingMessage.classList.remove('hidden');

    // Ambil data dari tabel 'komentar', urutkan berdasarkan 'dibuat_pada' terbaru
    // PERUBAHAN KRUSIAL: Menggunakan relasi untuk mengambil data 'nama_lengkap' dari tabel 'profiles'.
    // Sintaks 'id_pengguna(nama_lengkap)' mengasumsikan kolom 'id_pengguna' di tabel 'komentar' 
    // adalah foreign key ke tabel 'profiles' (di Supabase, ini direpresentasikan sebagai nama relasi)
    const { data, error } = await supabase
        .from(TABLE_NAME)
        .select(`
            id_komentar, 
            komentar, 
            rating, 
            dibuat_pada, 
            profiles(nama_lengkap) 
        `)
        .order('dibuat_pada', { ascending: false })
        .limit(10); // Ambil 10 testimonial terbaru

    // Sembunyikan pesan loading
    loadingMessage.classList.add('hidden');
    container.removeChild(loadingMessage);
    
    if (error) {
        console.error('Error memuat data testimonial:', error);
        container.innerHTML = `<p class="text-red-500 text-center w-full">Gagal memuat testimonial: ${error.message}</p>`;
        return;
    }

    if (data.length === 0) {
        container.innerHTML = `<p class="text-gray-500 text-center w-full">Belum ada testimonial. Jadilah yang pertama!</p>`;
        return;
    }

    // Render setiap data ke dalam kartu testimonial
    data.forEach(item => {
        const testimonialCard = createTestimonialCard(item);
        container.appendChild(testimonialCard);
    });

    // Inisialisasi slider setelah data dimuat
    initSlider();
}

/**
 * Membuat elemen HTML untuk setiap kartu testimonial.
 * @param {object} data - Data komentar dari Supabase.
 * @returns {HTMLElement} Elemen div kartu testimonial.
 */
function createTestimonialCard(data) {
    const date = new Date(data.dibuat_pada).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Ambil nama pengguna. Jika relasi profiles ada, gunakan nama_lengkap.
    // Jika profiles null (misalnya, foreign key tidak valid atau data hilang), gunakan 'Anonim'.
    // Objek 'profiles' akan berisi data yang di-join: data.profiles = { nama_lengkap: '...' }
    const userName = data.profiles && data.profiles.nama_lengkap 
                     ? data.profiles.nama_lengkap 
                     : 'Anonim';
    
    // Fungsi untuk membuat bintang (diisi atau kosong)
    const renderStars = (rating) => {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            // Gunakan ikon Font Awesome (fas fa-star)
            stars += `<i class="fas fa-star ${i <= rating ? 'text-yellow-400' : 'text-gray-300'}"></i>`;
        }
        return stars;
    };
    
    const card = document.createElement('div');
    card.className = 'testimonial-card flex-shrink-0 px-4';
    card.innerHTML = `
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-2xl p-8 h-full">
            <div class="flex-display justify-between items-start mb-4">
                <div class="text-2xl">${renderStars(data.rating)}</div>
                <div class="text-sm text-gray-500">${date}</div>
            </div>
            <p class="text-gray-800 text-lg mb-6 leading-relaxed">"${data.komentar}"</p>
            <div class="border-t border-gray-200 pt-4">
                <p class="font-semibold text-primary">Pengguna: ${userName}</p> 
                <p class="text-sm text-gray-500">ID Komentar: ${data.id_komentar}</p>
            </div>
        </div>
    `;
    return card;
}


// --- 3. LOGIKA FORM SUBMISSION (KIRIM KOMENTAR) ---

document.getElementById('komentarForm').addEventListener('submit', handleFormSubmit);

async function handleFormSubmit(event) {
    event.preventDefault();

    const komentarInput = document.getElementById('isi-komentar');
    const ratingInput = document.querySelector('input[name="rating"]:checked');
    
    const komentar = komentarInput.value.trim();
    const rating = ratingInput ? parseInt(ratingInput.value) : null;

    const errorMsg = document.getElementById('komentar-error-message');
    const successMsg = document.getElementById('komentar-success-message');
    errorMsg.classList.add('hidden');
    successMsg.classList.add('hidden');
    
    // Mendapatkan ID Pengguna (PENTING: Ganti dengan logika autentikasi Supabase nyata)
    // Untuk tujuan contoh ini, kita asumsikan pengguna sudah terautentikasi atau anonim.
    let user_id = 'anon_user_placeholder'; // Nilai default
    
    // Cek apakah ada sesi Supabase yang aktif (lebih disarankan)
    const { data: { user } } = await supabase.auth.getUser();
    if (user) {
         user_id = user.id;
    } else {
        // Jika tidak ada user, mungkin Anda bisa meminta user untuk login
        errorMsg.textContent = 'Anda harus login untuk mengirim komentar.';
        errorMsg.classList.remove('hidden');
        return;
    }
    
    // Validasi input
    if (!komentar || !rating) {
        errorMsg.textContent = 'Komentar dan Rating wajib diisi.';
        errorMsg.classList.remove('hidden');
        return;
    }

    const newComment = {
        id_pengguna: user_id, 
        komentar: komentar,
        rating: rating,
    };

    // Kirim data ke Supabase
    const { data, error } = await supabase
        .from(TABLE_NAME)
        .insert([newComment]);

    if (error) {
        console.error('Error saat mengirim komentar:', error);
        errorMsg.textContent = `Gagal mengirim komentar: ${error.message}`;
        errorMsg.classList.remove('hidden');
    } else {
        successMsg.textContent = 'Terima kasih! Komentar Anda berhasil dikirim.';
        successMsg.classList.remove('hidden');
        // Reset form
        komentarInput.value = '';
        if (ratingInput) ratingInput.checked = false;
        document.getElementById('rating-value').textContent = 'Pilih rating';
        
        // Muat ulang testimonial untuk menampilkan yang baru
        loadTestimonials(); 
    }
}


// --- 4. LOGIKA SLIDER (NAVIGASI, DOTS, DAN AUTO-SLIDE) ---
let currentIndex = 0;
let autoSlideInterval; // Variabel untuk menyimpan interval auto-slide
const SLIDE_DURATION = 5000; // Geser setiap 5 detik

/**
 * Mendapatkan jumlah kartu yang terlihat berdasarkan lebar layar (responsif).
 * @returns {number} Jumlah kartu yang terlihat.
 */
function getVisibleCards() {
    if (window.innerWidth <= 640) return 1; // Mobile
    if (window.innerWidth <= 1024) return 2; // Tablet
    return 3; // Desktop
}

/**
 * Memulai logika auto-slide.
 */
function startAutoSlide() {
    // Pastikan interval sebelumnya dihapus agar tidak terjadi duplikasi
    clearInterval(autoSlideInterval);
    
    autoSlideInterval = setInterval(() => {
        const container = document.getElementById('testimoni-container');
        const cards = container.querySelectorAll('.testimonial-card');
        const visibleCards = getVisibleCards();
        
        if (cards.length > 0) {
            // Cek apakah sudah mencapai slide terakhir
            if (currentIndex >= cards.length - visibleCards) {
                // Kembali ke slide pertama
                currentIndex = 0;
            } else {
                // Geser ke kartu berikutnya
                currentIndex += 1;
            }
            updateSlider();
        }
    }, SLIDE_DURATION);
}

/**
 * Menghentikan auto-slide dan memulai ulang setelah jeda singkat (untuk interaksi pengguna).
 * @param {number} restartDelay - Jeda dalam milidetik sebelum memulai ulang.
 */
function pauseAndRestartAutoSlide(restartDelay = 6000) {
    clearInterval(autoSlideInterval);
    setTimeout(startAutoSlide, restartDelay);
}


function initSlider() {
    const container = document.getElementById('testimoni-container');
    const cards = container.querySelectorAll('.testimonial-card');
    const dotsContainer = document.getElementById('testimonial-dots');
    dotsContainer.innerHTML = ''; 

    if (cards.length === 0) {
        // Hentikan auto-slide jika tidak ada kartu
        clearInterval(autoSlideInterval);
        return;
    }

    const visibleCards = getVisibleCards();
    const totalPages = Math.ceil(cards.length / visibleCards);

    // Buat dots
    for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = `w-3 h-3 rounded-full cursor-pointer transition-colors ${i === 0 ? 'bg-primary' : 'bg-gray-300'}`;
        dot.dataset.page = i;
        dot.addEventListener('click', () => {
            currentIndex = i * visibleCards; // Pindah ke kartu pertama di halaman
            updateSlider();
            pauseAndRestartAutoSlide(); // Pause dan restart setelah interaksi
        });
        dotsContainer.appendChild(dot);
    }
    
    // Batasi index agar tidak melebihi jumlah kartu yang tersedia
    currentIndex = Math.min(currentIndex, cards.length - visibleCards);
    if (currentIndex < 0) currentIndex = 0;

    updateSlider();
    startAutoSlide(); // Mulai auto-slide setelah inisialisasi
}

function updateSlider() {
    const container = document.getElementById('testimoni-container');
    const cards = container.querySelectorAll('.testimonial-card');
    
    if (cards.length === 0) return;

    const visibleCards = getVisibleCards();
    const totalCards = cards.length;
    
    // Pastikan currentIndex tidak melebihi batas (untuk mencegah tampilan kosong)
    const maxIndex = Math.max(0, totalCards - visibleCards);
    currentIndex = Math.min(currentIndex, maxIndex);
    
    const totalPages = Math.ceil(totalCards / visibleCards);
    const currentPage = Math.floor(currentIndex / visibleCards);


    // Hitung lebar kartu tunggal dalam persentase tampilan container (100% / jumlah total kartu)
    // Kemudian kalikan dengan index saat ini.
    // Ini mengasumsikan lebar total kontainer slider adalah totalCards * lebar_kartu_asli.
    
    // Karena lebar setiap card adalah (100 / visibleCards) % dari kontainer #testimoni-container
    // dan kita ingin geser 1 kartu, kita geser sebesar (100 / totalCards) % * currentIndex
    
    // Atau cara yang lebih sederhana:
    // Persentase yang perlu digeser adalah (currentIndex * (lebar 1 kartu / lebar 100% container)) * 100
    // Lebar 1 kartu = 100 / totalCards (dalam persentase)
    let translateValue = (currentIndex * (100 / totalCards));
    
    // Karena kita menggunakan min-width: 33.3333% (atau 50% / 100%), 
    // kita perlu hitung berapa kali min-width itu digeser.
    
    // Contoh: 9 kartu, 3 terlihat (33.33% per kartu)
    // Index 0: 0%
    // Index 1: 33.3333% / 3 = 11.11% geser per kartu. Ini terlalu kecil.
    // Seharusnya: 1 kartu = 1 unit geser. Geser harus 100% / (Total Cards / Visible Cards)
    
    // Kita gunakan min-width 33.3333% (1/3) per kartu.
    const slideUnit = 100 / totalCards; // Geseran yang dibutuhkan untuk 1 kartu
    translateValue = currentIndex * slideUnit;

    container.style.transform = `translateX(-${translateValue}%)`;
    
    // Update dots (Pagination)
    const dots = document.querySelectorAll('#testimonial-dots div');
    // Cari dot yang aktif. Dot aktif adalah dot di halaman saat ini.
    dots.forEach((dot, index) => {
        // Halaman saat ini adalah ketika index dot dikalikan dengan visibleCards
        // masih kurang dari atau sama dengan currentIndex, dan index dot berikutnya
        // melebihi currentIndex
        const isCurrentPage = (index * visibleCards) <= currentIndex && ((index + 1) * visibleCards) > currentIndex;
        
        if (isCurrentPage) {
            // Kelas untuk dot aktif (ganti 'bg-primary' dengan warna aktif Anda)
            dot.className = 'w-3 h-3 rounded-full cursor-pointer transition-colors bg-primary'; 
        } else {
            // Kelas untuk dot tidak aktif (ganti 'bg-gray-300' dengan warna tidak aktif Anda)
            dot.className = 'w-3 h-3 rounded-full cursor-pointer transition-colors bg-gray-300';
        }
    });
}

document.getElementById('next-testimonial').addEventListener('click', () => {
    const container = document.getElementById('testimoni-container');
    const cards = container.querySelectorAll('.testimonial-card');
    const visibleCards = getVisibleCards();
    
    // Pindah ke kartu berikutnya, batasi hingga kartu terakhir yang terlihat
    if (currentIndex < cards.length - visibleCards) {
        currentIndex += 1;
        updateSlider();
        pauseAndRestartAutoSlide(); // Pause dan restart setelah interaksi
    } else if (currentIndex === cards.length - visibleCards && cards.length > visibleCards) {
        // Jika sudah di slide terakhir, kembali ke slide pertama
        currentIndex = 0;
        updateSlider();
        pauseAndRestartAutoSlide(); // Pause dan restart setelah interaksi
    }
});

document.getElementById('prev-testimonial').addEventListener('click', () => {
    const container = document.getElementById('testimoni-container');
    const cards = container.querySelectorAll('.testimonial-card');
    const visibleCards = getVisibleCards();

    // Pindah ke kartu sebelumnya, batasi hingga kartu pertama
    if (currentIndex > 0) {
        currentIndex -= 1;
        updateSlider();
        pauseAndRestartAutoSlide(); // Pause dan restart setelah interaksi
    } else if (currentIndex === 0 && cards.length > visibleCards) {
        // Jika sudah di slide pertama, pindah ke slide terakhir
        currentIndex = Math.max(0, cards.length - visibleCards);
        updateSlider();
        pauseAndRestartAutoSlide(); // Pause dan restart setelah interaksi
    }
});

// Update slider saat ukuran jendela berubah (responsif)
window.addEventListener('resize', () => {
    // Reset index saat resize untuk menghindari posisi yang aneh
    currentIndex = 0; 
    pauseAndRestartAutoSlide(500); // Pause dan restart setelah resize selesai
    initSlider(); // Panggil initSlider untuk membuat dots baru yang sesuai dengan breakpoint baru
});


// --- 5. LOGIKA RATING BINTANG ---

const ratingInputs = document.querySelectorAll('input[name="rating"]');
const ratingValueSpan = document.getElementById('rating-value');

ratingInputs.forEach(input => {
    input.addEventListener('change', () => {
        const selectedRating = input.value;
        ratingValueSpan.textContent = `Anda memberi ${selectedRating} bintang.`;
    });
});


// --- 6. INISIALISASI ---

document.addEventListener('DOMContentLoaded', () => {
    loadTestimonials(); // Mulai memuat data saat halaman dimuat
});

})();