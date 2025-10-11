document.addEventListener('DOMContentLoaded', function () {

    const modalOverlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const closeModalBtn = document.getElementById('closeModal');

    function showModal(title, content) {
        modalTitle.textContent = title;
        modalBody.innerHTML = content;
        modalOverlay.classList.add('show');
        const form = modalBody.querySelector('form');
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }
    }

    function hideModal() {
        modalOverlay.classList.remove('show');
    }

    //untuk tambah
    document.getElementById('tambahKuota').addEventListener('click', () => {
        const formContent = `
            <form id="formKuota" action="kuota_pendakian/proses_kuota.php" method="POST">
                <input type="hidden" name="action" value="tambah">
                <div class="form-group">
                    <label for="tanggal_kuota">Tanggal Kuota</label>
                    <input type="date" id="tanggal_kuota" name="tanggal_kuota" required>
                </div>
                <div class="form-group">
                    <label for="kuota_maksimal">Kuota Maksimal</label>
                    <input type="number" id="kuota_maksimal" name="kuota_maksimal" placeholder="Contoh: 50" required>
                </div>
                <button type="submit" class="btn green form-submit-btn">Simpan</button>
            </form>
        `;
        showModal('Tambah Kuota Pendakian', formContent);
    });

    //untuk edit
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', async function () {
            const id = this.getAttribute('data-id');

            try {
                const response = await fetch(`kuota_pendakian/get_kuota.php?id=${id}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const data = result.data;
                    const formContent = `
                        <form id="formKuota" action="../proses_kuota.php" method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id_kuota" value="${data.id_kuota}">
                            <div class="form-group">
                                <label for="tanggal_kuota">Tanggal Kuota</label>
                                <input type="date" id="tanggal_kuota" name="tanggal_kuota" value="${data.tanggal_kuota}" required>
                            </div>
                            <div class="form-group">
                                <label for="kuota_maksimal">Kuota Maksimal</label>
                                <input type="number" id="kuota_maksimal" name="kuota_maksimal" value="${data.kuota_maksimal}" required>
                            </div>
                            <button type="submit" class="btn green form-submit-btn">Update</button>
                        </form>
                    `;
                    showModal('Edit Kuota Pendakian', formContent);
                } else {
                    Swal.fire('Error', result.message || 'Gagal mengambil data kuota.', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
            }
        });
    });

    // untuk hapus
    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data kuota ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#35542E',
                cancelButtonColor: '#E74C3C',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                // Animasi untuk konfirmasi
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'hapus');
                    formData.append('id_kuota', id);

                    fetch('kuota_pendakian/proses_kuota.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Dihapus!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false,
                                // Animasi berhasil hapus
                                showClass: {
                                    popup: 'animate__animated animate__zoomIn'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut'
                                }
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                // Animasi gagal hapus
                                showClass: {
                                    popup: 'animate__animated animate__shakeX'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOutUp'
                                }
                            });
                        }
                    });
                }
            })
        });
    });


    // Fungsi untuk menangani submit form (Tambah & Edit)
    async function handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                hideModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: result.message,
                    showClass: {
                        popup: 'animate__animated animate__shakeX'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            }
        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan saat mengirim data.', 'error');
        }
    }

    // Menutup modal saat klik tombol close atau area overlay
    closeModalBtn.addEventListener('click', hideModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            hideModal();
        }
    });

});