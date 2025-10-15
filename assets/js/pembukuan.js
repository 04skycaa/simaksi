document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ PEMBUKUAN.JS BERHASIL DIMUAT DAN DIJALANKAN');

    // PERUBAHAN: Mencari ID modal yang unik untuk halaman pembukuan
    const modalOverlay = document.getElementById('pembukuan-modal-overlay');
    const modalTitle = document.getElementById('pembukuan-modal-title');
    const modalBody = document.getElementById('pembukuan-modal-body');
    const closeModalBtn = document.getElementById('pembukuan-modal-close');
    //---------------------------------------------------------

    const tambahBtn = document.getElementById('tambahPengeluaran');
    const dataTableBody = document.querySelector('.data-table tbody');


    /**
     * Membuka modal dan memuat konten dari URL (file PHP form)
     * @param {string} title - Judul yang akan ditampilkan di modal
     * @param {string} url - URL ke file get_pembukuan.php
     */
    const openModal = async (title, url) => {
        modalTitle.textContent = title;
        modalBody.innerHTML = '<div class="loader">Memuat...</div>';
        modalOverlay.style.display = 'flex';

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Gagal mengambil data: ${response.statusText}`);
            }
            const html = await response.text();
            modalBody.innerHTML = html;
            handleFormSubmit(); // Pasang event listener ke form yang baru dimuat
        } catch (error) {
            modalBody.innerHTML = `<p style="color: red;">Gagal memuat konten. Cek path URL di console (F12).</p>`;
            console.error('Fetch Error:', error);
        }
    };

    /**
     * Menutup modal
     */
    const closeModal = () => {
        modalOverlay.style.display = 'none';
        modalBody.innerHTML = '';
    };
    
    /**
     * Menangani proses submit form di dalam modal
     */
    const handleFormSubmit = () => {
        const form = document.getElementById('pengeluaranForm');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        closeModal();
                        location.reload(); // Muat ulang halaman untuk melihat perubahan
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message || 'Terjadi kesalahan.',
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Tidak dapat terhubung ke server.',
                });
                console.error('Submit Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Simpan';
            }
        });
    };

    // --- EVENT LISTENERS ---

    // 1. Klik tombol "Tambah Pengeluaran"
    tambahBtn.addEventListener('click', () => {
        const url = '/simaksi/admin/pembukuan/get_pembukuan.php';
        openModal('Tambah Pengeluaran Baru', url);
    });

    // 2. Klik tombol "Edit" di dalam tabel (menggunakan event delegation)
    dataTableBody.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const url = `/simaksi/admin/pembukuan/get_pembukuan.php?id=${id}`;
            openModal('Edit Data Pengeluaran', url);
        }
    });

    // 3. Menutup modal
    closeModalBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
});