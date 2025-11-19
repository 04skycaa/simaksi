// Lokasi: /simaksi/assets/js/manage_pendakian.js

document.addEventListener('DOMContentLoaded', () => {
    // Ambil referensi ke elemen Modal
    const modalOverlay = document.getElementById('modalOverlay');
    const closeModalBtn = document.getElementById('closeModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const tableBody = document.querySelector('.data-table tbody');
    
    // --- Fungsi Modal ---
    const openModal = (title, contentHTML) => {
        modalTitle.textContent = title;
        modalBody.innerHTML = contentHTML;
        modalOverlay.classList.add('active');
    };

    const closeModal = () => {
        modalOverlay.classList.remove('active');
        // Memberi waktu transisi sebelum menghapus konten
        setTimeout(() => {
            modalBody.innerHTML = ''; 
        }, 300);
    };

    // Event Listener untuk menutup modal
    closeModalBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    // --- Fungsi Form Edit (Hanya Edit) ---
    const buildForm = (data = {}) => {
        // data.id_pendaki di-disable karena tidak boleh diubah saat edit
        let formHTML = `
            <form id="pendakianForm">
                <input type="hidden" name="id_reservasi" value="${data.id_reservasi ?? ''}">
                
                <label for="id_pendaki">ID Pendaki (Tidak dapat diubah):</label>
                <input type="number" id="id_pendaki" value="${data.id_pendaki ?? ''}" disabled>

                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="${data.nama_lengkap ?? ''}" required>

                <label for="nik">NIK:</label>
                <input type="text" id="nik" name="nik" value="${data.nik ?? ''}" required>

                <label for="alamat">Alamat:</label>
                <input type="text" id="alamat" name="alamat" value="${data.alamat ?? ''}" required>

                <label for="nomor_telepon">No. Telepon:</label>
                <input type="text" id="nomor_telepon" name="nomor_telepon" value="${data.nomor_telepon ?? ''}" required>

                <label for="kontak_darurat">Kontak Darurat:</label>
                <input type="text" id="kontak_darurat" name="kontak_darurat" value="${data.kontak_darurat ?? ''}" required>
                
                <p style="font-size: 0.8rem; color: #999;">Catatan: Pembaruan Surat Sehat harus dilakukan terpisah.</p>
                <div class="form-actions">
                    <button type="submit" class="btn blue">Simpan Perubahan</button>
                    <button type="button" class="btn red" id="cancelForm">Batal</button>
                </div>
            </form>
        `;
        return formHTML;
    };
    
    // --- Setup Submission Form (Hanya untuk Update) ---
    const setupFormSubmission = () => {
        const form = document.getElementById('pendakianForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // ID Pendaki dihapus agar tidak terkirim (karena disabled di form)
            delete data.id_pendaki; 

            const url = '/simaksi/api/update_pendakian.php'; 

            try {
                const response = await fetch(url, {
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && !result.error) {
                    Swal.fire('Berhasil!', `Data berhasil diubah.`, 'success').then(() => {
                        closeModal();
                        window.location.reload(); // Muat ulang tabel
                    });
                } else {
                    throw new Error(result.message || `Gagal mengubah data.`);
                }
            } catch (error) {
                Swal.fire('Gagal', error.message, 'error');
            }
        });
    };

    // --- Fungsi Menghapus Data ---
    const handleDelete = async (idReservasi) => {
        try {
            const response = await fetch('/simaksi/api/delete_pendakian.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_reservasi: idReservasi })
            });

            const result = await response.json();

            if (response.ok && !result.error) {
                Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success').then(() => {
                    window.location.reload(); 
                });
            } else {
                throw new Error(result.message || 'Gagal menghapus data.');
            }
        } catch (error) {
            Swal.fire('Gagal', error.message, 'error');
        }
    };


    // --- Event Listener Utama untuk Aksi Tabel (Edit/Hapus) ---
    tableBody.addEventListener('click', async (e) => {
        const target = e.target.closest('button');
        if (!target || !target.dataset.id) return;

        const idReservasi = target.dataset.id;
        const row = target.closest('tr');
        
        // 1. Tombol Edit (Tampilkan Modal)
        if (target.classList.contains('btn-edit')) {
            
            // Ambil data dari baris tabel
            const rowData = {
                id_reservasi: row.children[0].textContent,
                id_pendaki: row.children[1].textContent,
                nama_lengkap: row.children[2].textContent,
                nik: row.children[3].textContent,
                alamat: row.children[4].textContent,
                nomor_telepon: row.children[5].textContent,
                kontak_darurat: row.children[6].textContent,
            };

            openModal('Edit Data Anggota Rombongan', buildForm(rowData));

            // Setup event listener untuk tombol Batal
            document.getElementById('cancelForm').addEventListener('click', closeModal);
            
            // Setup submission form
            setupFormSubmission(); 
        } 
        
        // 2. Tombol Hapus (Tampilkan SweetAlert Konfirmasi)
        else if (target.classList.contains('btn-hapus')) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data dengan ID Reservasi " + idReservasi + "? Aksi ini permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleDelete(idReservasi);
                }
            });
        }
    });
});