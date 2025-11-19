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
                    handleFormSubmit(form);
                }
            })
            .catch(error => {
                modalBody.innerHTML = "<p>Gagal memuat konten. Silakan coba lagi.</p>";
                console.error("Fetch Error:", error);
            });
    };

    // Fungsi untuk menangani submit form di dalam modal (AJAX)
    const handleFormSubmit = (form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(form);

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
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Jaringan',
                    text: 'Tidak bisa terhubung ke server.'
                });
                console.error("Submit Error:", error);
            });
        });
    };

    // Event listener untuk tombol "Tambah Pengguna"
    const tambahBtn = document.getElementById("tambahUser");
    if (tambahBtn) {
        tambahBtn.addEventListener("click", () => {
            openModal('/simaksi/admin/management_user/form_user.php', 'Tambah Pengguna Baru');
        });
    }

    // Event listener untuk semua tombol "Edit"
    document.querySelectorAll(".btn-edit").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-id");
            openModal(`/simaksi/admin/management_user/form_user.php?id=${userId}`, 'Edit Data Pengguna');
        });
    });

    // Event listener untuk semua tombol "Hapus"
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
                    formData.append('action', 'hapus');
                    formData.append('id', userId);

                    fetch('/simaksi/admin/management_user/proses_user.php', {
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