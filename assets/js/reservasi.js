document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('reservasi-modal-overlay');
    const modalContainer = modalOverlay.querySelector('.modal-container');
    const modalCloseBtn = document.getElementById('reservasi-modal-close');
    const modalBody = document.getElementById('reservasi-modal-body');

    function showModal() {
        modalOverlay.style.display = 'flex';
        modalContainer.classList.remove('animate__fadeOutUp');
        modalContainer.classList.add('animate__fadeInDown');
    }

    function hideModal() {
        modalContainer.classList.remove('animate__fadeInDown');
        modalContainer.classList.add('animate__fadeOutUp');
        setTimeout(() => {
            modalOverlay.style.display = 'none';
            modalBody.innerHTML = '<p>Loading...</p>';
        }, 500);
    }

    document.querySelectorAll('.btn-validasi').forEach(button => {
        button.addEventListener('click', function() {
            const reservasiId = this.dataset.id;
            fetch(`actions/get_reservasi.php?id=${reservasiId}`)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    showModal();
                })
                .catch(error => {
                    modalBody.innerHTML = '<p>Gagal memuat data. Silakan coba lagi.</p>';
                    console.error('Error:', error);
                    showModal();
                });
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function() {
            const reservasiId = this.dataset.id;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data reservasi ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('actions/proses_reservasi.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ action: 'delete', id: reservasiId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Terhapus!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    });
                }
            });
        });
    });

    modalBody.addEventListener('click', function(event) {
        const target = event.target.closest('button');
        if (!target) return;

        const reservasiId = target.dataset.id;
        let newStatus = '';
        let confirmText = '';

        if (target.classList.contains('btn-setujui')) {
            newStatus = 'Disetujui';
            confirmText = 'menyetujui';
        } else if (target.classList.contains('btn-tolak')) {
            newStatus = 'Ditolak';
            confirmText = 'menolak';
        } else {
            return;
        }
        
        Swal.fire({
            title: `Anda yakin ingin ${confirmText} reservasi ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('actions/proses_reservasi.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'update_status', id: reservasiId, status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success')
                            .then(() => {
                                hideModal();
                                location.reload();
                            });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                });
            }
        });
    });

    // menutup modal
    modalCloseBtn.addEventListener('click', hideModal);
    modalOverlay.addEventListener('click', function(event) {
        if (event.target === modalOverlay) {
            hideModal();
        }
    });
});