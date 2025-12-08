// FILE: stats_loader.js

/**
 * Fungsi untuk mengambil data statistik (jumlah rombongan & rating) dari API PHP
 * dan menampilkannya di elemen HTML.
 */
async function loadStats() {
    // Jalur ke file PHP API yang mengambil data dari Supabase.
    // PASTIKAN jalur ini benar di server Anda.
    const apiUrl = 'admin/api/fetch_stats.php'; 
    
    // Default value jika terjadi error atau data belum dimuat
    const defaultText = 'N/A';
    
    // --- 1. Dapatkan elemen HTML ---
    const totalPendakiElement = document.getElementById('total-pendaki');
    const ratingElement = document.getElementById('rating-pengalaman');

    // Fungsi utilitas untuk menampilkan default text saat error
    const displayDefault = () => {
        if (totalPendakiElement) {
             totalPendakiElement.textContent = defaultText;
        }
        if (ratingElement) {
            ratingElement.textContent = defaultText;
        }
    };

    // Pastikan elemen ada di DOM
    if (!totalPendakiElement || !ratingElement) {
        console.warn("Peringatan: Salah satu elemen ID (total-pendaki atau rating-pengalaman) tidak ditemukan di HTML.");
        return; // Hentikan jika elemen tidak ada
    }

    try {
        const response = await fetch(apiUrl); 
        
        if (!response.ok) {
            // Jika response code bukan 200 (misal 404 Not Found atau 500 Internal Server Error)
            console.error(`Gagal mengambil data statistik. Status HTTP: ${response.status}`);
            displayDefault();
            return;
        }
        
        const stats = await response.json(); // Data JSON dari PHP
        
        // Cek jika PHP mengembalikan status error secara eksplisit (dari file fetch_stats.php)
        if (stats.status && stats.status.startsWith('error')) {
             console.error(`API Error: ${stats.message}`);
             displayDefault();
             return;
        }
        
        // --- 2. Isi Total Rombongan (Menggunakan kunci 'total_pendaki' dari PHP) ---
        let totalRombongan = stats.total_pendaki;
        let displayValue;
        
        if (totalRombongan !== undefined && totalRombongan >= 0) {
            // Formatting untuk menampilkan angka besar
            if (totalRombongan >= 1000) {
                displayValue = Math.floor(totalRombongan / 1000) + 'k+';
            } else {
                displayValue = totalRombongan.toString();
                // Tambahkan '+' hanya jika nilainya lebih dari 0 dan Anda ingin menekankan hitungan
                if (totalRombongan > 0) {
                    displayValue += '+';
                }
            }
        } else {
            displayValue = '0';
        }
        totalPendakiElement.textContent = displayValue;
        
        
        // --- 3. Isi Rata-rata Rating ---
        // Menggunakan nilai 'rating_display' (misalnya: "4.8/5" atau "Belum Ada Rating")
        ratingElement.textContent = stats.rating_display || defaultText;

    } catch (e) {
        console.error("Error loading stats (catch block):", e);
        // Tampilkan nilai default jika terjadi kesalahan jaringan atau parsing
        displayDefault();
    }
}

// Panggil fungsi loadStats() setelah seluruh konten HTML dimuat.
document.addEventListener('DOMContentLoaded', loadStats);