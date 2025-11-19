// Tunggu hingga seluruh dokumen HTML selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    
    // --- LOGIKA FORM TAMBAH PENGELUARAN (AJAX) ---
    const tambahForm = document.getElementById('tambahPengeluaranForm');
    if (tambahForm) {
        tambahForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah reload halaman
            const formData = new FormData(this);
            const actionUrl = this.getAttribute('action');
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Tampilkan loading di tombol
            submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            submitButton.disabled = true;

            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected || response.type === 'opaqueredirect') {
                    window.location.reload(); 
                } else {
                    return response.text().then(text => {
                        throw new Error(text || 'Terjadi kesalahan tidak dikenal');
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan. Cek konsol (F12) untuk detail.'
                });
                console.error('Error:', error);
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }


    // ===========================================
    // --- LOGIKA MODAL EDIT PENGELUARAN ---
    // ===========================================
    
    const modalOverlay = document.getElementById('pembukuan-modal-overlay');
    const modalBody = document.getElementById('pembukuan-modal-body');
    const modalCloseBtn = document.getElementById('pembukuan-modal-close');
    const modalTitle = document.getElementById('pembukuan-modal-title');

    function openModal() {
        // PERBAIKAN: Menggunakan 'show' sesuai style.css Anda
        if (modalOverlay) modalOverlay.classList.add('show');
    }
    function closeModal() {
        // PERBAIKAN: Menggunakan 'show' sesuai style.css Anda
        if (modalOverlay) modalOverlay.classList.remove('show');
        if (modalBody) modalBody.innerHTML = ''; // Kosongkan isi modal
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeModal);
    }
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    const allEditButtons = document.querySelectorAll('.btn-edit');
    allEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            
            const id = this.getAttribute('data-id');
            if (!id) return;

            if (modalTitle) modalTitle.textContent = 'Edit Pengeluaran';

            // Tampilkan modal DENGAN spinner
            openModal();
            if(modalBody) {
                // Gunakan style inline untuk spinner jika belum ada di style.css
                modalBody.innerHTML = '<div class="loading-spinner" style="text-align: center; padding: 40px 0; font-size: 24px; color: #007bff;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
            }

            // Ambil HTML form dari get_pembukuan.php
            const fetchUrl = `pembukuan/get_pembukuan.php?id=${id}`;

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Gagal memuat data (HTTP ${response.status})`);
                    }
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                    setupModalFormSubmit();
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire('Error', 'Gagal memuat data untuk diedit.', 'error');
                    modalBody.innerHTML = `<p style='color: red;'>${error.message}</p>`;
                });
        });
    });

    // Fungsi ini mencari form di dalam modal edit dan memasang listener submit
    function setupModalFormSubmit() {
        const modalForm = document.getElementById('pengeluaranForm'); 
        if (modalForm) {
            modalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const actionUrl = this.getAttribute('action'); // -> "pembukuan/proses_pembukuan.php"
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;

                submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
                submitButton.disabled = true;

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.redirected || response.type === 'opaqueredirect') {
                        window.location.reload(); // Sukses, muat ulang halaman
                    } else {
                         return response.text().then(text => {
                            throw new Error(text || 'Terjadi kesalahan tidak dikenal');
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    console.error('Error:', error);
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
            });
        }
    }

    // =============================================
    // --- LOGIKA MODAL TAMBAH KATEGORI ---
    // =============================================
    const kategoriModalOverlay = document.getElementById('kategori-modal-overlay');
    const kategoriModalCloseBtn = document.getElementById('kategori-modal-close');
    const kategoriForm = document.getElementById('form-tambah-kategori');
    const bukaKategoriModalBtn = document.getElementById('buka-modal-kategori-btn');

    function openModalKategori() {
        // PERBAIKAN: Menggunakan 'show' sesuai style.css Anda
        if (kategoriModalOverlay) kategoriModalOverlay.classList.add('show');
    }
    function closeModalKategori() {
        // PERBAIKAN: Menggunakan 'show' sesuai style.css Anda
        if (kategoriModalOverlay) kategoriModalOverlay.classList.remove('show');
        if (kategoriForm) kategoriForm.reset(); // Reset form saat ditutup
    }

    if (bukaKategoriModalBtn) {
        bukaKategoriModalBtn.addEventListener('click', openModalKategori);
    }
    if (kategoriModalCloseBtn) {
        kategoriModalCloseBtn.addEventListener('click', closeModalKategori);
    }
    if (kategoriModalOverlay) {
        kategoriModalOverlay.addEventListener('click', (e) => {
            if (e.target === kategoriModalOverlay) {
                closeModalKategori();
            }
        });
    }

    if (kategoriForm) {
        kategoriForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const actionUrl = this.getAttribute('action'); // -> "pembukuan/proses_kategori.php"
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            submitButton.disabled = true;

            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // proses_kategori.php MENGEMBALIKAN JSON
            .then(data => {
                if (data.status === 'success' && data.new_kategori) {
                    closeModalKategori();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Kategori baru berhasil ditambahkan.'
                    });

                    // Buat opsi baru untuk dropdown
                    const newOption = new Option(data.new_kategori.nama_kategori, data.new_kategori.id_kategori);
                    
                    const selectTambah = document.getElementById('tambah_kategori');
                    if (selectTambah) {
                        selectTambah.appendChild(newOption);
                        selectTambah.value = data.new_kategori.id_kategori; // Langsung pilih
                    }

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Tidak bisa menyimpan kategori.'
                    });
                }
            })
            .catch(error => {
                Swal('Error', 'Terjadi kesalahan jaringan.', 'error');
                console.error('Error:', error);
            })
            .finally(() => {
                // Kembalikan tombol ke kondisi semula
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }
});