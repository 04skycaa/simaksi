document.addEventListener("DOMContentLoaded", function () {
    const modalOverlay = document.getElementById("modalOverlay");
    const modalTitle = document.getElementById("modalTitle");
    const modalBody = document.getElementById("modalBody");
    const closeModal = document.getElementById("closeModal");

    // Fungsi untuk membuka modal dan memuat konten form
    const openModal = (url, title) => {
        modalTitle.innerText = title;
        modalBody.innerHTML = '<div class="loader">Memuat...</div>'; // Tampilkan loader
        modalOverlay.classList.add("show");

        fetch(url)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                const form = modalBody.querySelector("#userForm");
                if (form) {
                    // Hanya jalankan handleFormSubmit jika form_action adalah 'tambah'
                    const formActionInput = form.querySelector('input[name="form_action"]');
                    if (formActionInput && formActionInput.value === 'tambah') {
                        handleFormSubmit(form);
                    }
                }

                // --- TAMBAHKAN LOGIKA IKON MATA DI SINI ---
                // Kode ini kita pindahkan dari form_user.php
                const toggle = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');

                if (toggle && passwordInput) {
                    toggle.addEventListener('click', function() {
                        // Toggle tipe input
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        
                        // Toggle ikon
                        this.classList.toggle('fa-eye');
                        this.classList.toggle('fa-eye-slash');
                    });
                }
                // --- SELESAI PENAMBAHAN ---

            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Jaringan',
                    text: 'Tidak bisa terhubung ke server.'
                });
                console.error("Submit Error:", error);
            });
        
        // HAPUS BARIS INI (BARIS 85 DI FILE ANDA)
        // }); 

    }; // Baris ini (sebelumnya 86) adalah penutup yang benar untuk 'openModal'

    // --- FUNGSI YANG HILANG, TAMBAHKAN MULAI DARI SINI ---

    // Fungsi untuk menangani submit form di dalam modal (AJAX)
    const handleFormSubmit = (form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(form);

            // Tambahkan loader di tombol simpan
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
            }

            fetch(form.action, {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload(); // Muat ulang halaman untuk melihat perubahan
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Terjadi kesalahan.'
                    });
                    // Kembalikan tombol jika error
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnHtml;
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Jaringan',
                    text: 'Tidak bisa terhubung ke server.'
                });
                console.error("Submit Error:", error);
                // Kembalikan tombol jika error
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnHtml;
                    submitBtn.disabled = false;
                }
            });
        });
    };

    // --- SELESAI PENAMBAHAN FUNGSI ---


    // Event listener untuk tombol "Tambah Pengguna"
    const tambahBtn = document.getElementById("tambahUser");
    if (tambahBtn) {
        tambahBtn.addEventListener("click", () => {
            // Arahkan ke halaman register_user melalui sistem routing admin
            window.location.href = 'index.php?page=register_user';
        });
    }

    // Event listener untuk semua tombol "Edit" (sekarang "Lihat Detail")
    document.querySelectorAll(".btn-edit").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-id");
            // Judul modal diubah menjadi "Lihat Detail Pengguna"
            openModal(`management_user/form_user.php?id=${userId}`, 'Lihat Detail Pengguna');
        });
    });

    // Event listener untuk semua tombol "Hapus" (jika Anda menambahkannya nanti)
    document.querySelectorAll(".btn-hapus").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-id");
            
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data pengguna ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'hapus'); // Anda perlu proses_user.php untuk menangani 'action=hapus'
                    formData.append('id', userId);

                    fetch('management_user/proses_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Dihapus!', data.message, 'success')
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message || 'Gagal menghapus data.', 'error');
                        }
                    })
                    .catch(error => console.error("Delete Error:", error));
                }
            });
        });
    });

    // Event listener untuk menutup modal
    if (closeModal) closeModal.addEventListener("click", () => modalOverlay.classList.remove("show"));
    modalOverlay.addEventListener("click", e => {
        if (e.target === modalOverlay) modalOverlay.classList.remove("show");
    });
});