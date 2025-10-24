// ===========================================
// KODE UNTUK SIDEBAR HOVER-TO-OPEN & FUNGSIONALITAS UMUM
// ===========================================

let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");
let toggle = document.querySelector(".toggle");
let hoverArea = navigation;

// Matikan fungsi toggle.onclick yang lama
if (toggle) {
  toggle.onclick = function (e) {
    e.preventDefault(); 
    // Manual override: Klik akan membalikkan status
    navigation.classList.toggle("active");
    main.classList.toggle("active");
  };
}

// LOGIKA HOVER-TO-OPEN SIDEBAR (Otomatis Buka/Tutup)
if (hoverArea) {
  let closeTimeout;
  const hoverDelay = 100; // Jeda 100ms untuk menutup

  hoverArea.addEventListener("mouseenter", function () {
    clearTimeout(closeTimeout); 
    // Buka sidebar hanya jika saat ini dalam mode tertutup (active)
    if (navigation.classList.contains("active")) {
        navigation.classList.remove("active");
        main.classList.remove("active");
    }
  });

  hoverArea.addEventListener("mouseleave", function () {
    // Tetapkan timeout untuk menutup setelah jeda singkat
    closeTimeout = setTimeout(() => {
        // Tutup sidebar
        navigation.classList.add("active");
        main.classList.add("active");
    }, hoverDelay); 
  });
}


// FUNGSI UNTUK MENAMPILKAN JAM DAN TANGGAL
// FUNGSI UNTUK MENAMPILKAN JAM, TANGGAL, DAN UCAPAN SELAMAT
function updateDateTime() {
    const now = new Date();
    const hour = now.getHours(); // Mengambil jam (0-23)

    let greeting = 'Selamat Malam'; // Default malam (21:00 - 04:59)

    if (hour >= 5 && hour < 11) {
        greeting = 'Selamat Pagi'; // Pagi (05:00 - 10:59)
    } else if (hour >= 11 && hour < 15) {
        greeting = 'Selamat Siang'; // Siang (11:00 - 14:59)
    } else if (hour >= 15 && hour < 18) {
        greeting = 'Selamat Sore'; // Sore (15:00 - 17:59)
    } else if (hour >= 18 && hour < 21) {
        greeting = 'Selamat Petang'; // Petang (18:00 - 20:59)
    }

    // Update elemen sapaan di HTML
    const greetingElement = document.getElementById('topbar-greeting');
    if (greetingElement) {
        greetingElement.textContent = greeting;
    }
    

    // --- Kode untuk Jam dan Tanggal (tetap sama) ---

    // Jam (HH:MM:SS)
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    // Mengganti titik dengan titik dua, sesuai format standar Indonesia (HH:MM:SS)
    const currentTime = now.toLocaleTimeString('id-ID', timeOptions); 
    document.getElementById('current-time').textContent = currentTime.replace(/\./g, ':');

    // Tanggal (Hari, dd Bulan yyyy)
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const currentDate = now.toLocaleDateString('id-ID', dateOptions);
    document.getElementById('current-date').textContent = currentDate;

    // Perbarui setiap detik
    setTimeout(updateDateTime, 1000);
}
function updateActiveMenuTitle() {
}

// Jalankan fungsi saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    updateDateTime();
    updateActiveMenuTitle(); // Dipanggil untuk inisialisasi
});