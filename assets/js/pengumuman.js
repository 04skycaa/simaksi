document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('pengumuman-modal-overlay');
    const modalContainer = modalOverlay.querySelector('.modal-container');
    const modalTitle = document.getElementById('pengumuman-modal-title');
    const modalCloseBtn = document.getElementById('pengumuman-modal-close');
    const form = document.getElementById('pengumuman-form');
    
    const idInput = document.getElementById('id_pengumuman');
    const judulInput = document.getElementById('judul');
    const kontenInput = document.getElementById('konten');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const terbitCheckbox = document.getElementById('telah_terbit');

    // fungsi untuk menampilkan dan menyembunyikan modal
    function showModal() {
        modalOverlay.style.display = 'flex';
        modalContainer.classList.add('animate__fadeInDown');
        modalContainer.classList.remove('animate__fadeOutUp');
    }
    function hideModal() {
        modalContainer.classList.remove('animate__fadeInDown');
        modalContainer.classList.add('animate__fadeOutUp');
        setTimeout(() => { modalOverlay.style.display = 'none'; }, 500);
    }
    
    // tombol "Buat Pengumuman Baru"
    document.getElementById('btn-tambah-pengumuman').addEventListener('click', () => {
        form.reset();
        idInput.value = '';
        modalTitle.textContent = 'Buat Pengumuman Baru';
        showModal();
    });

    // tombol "Edit" di setiap baris
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`pengumuman/actions/get_pengumuman.php?id=${id}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;
                        idInput.value = data.id_pengumuman;
                        judulInput.value = data.judul;
                        kontenInput.value = data.konten;
                        startDateInput.value = data.start_date.slice(0, 16);
                        endDateInput.value = data.end_date.slice(0, 16);
                        terbitCheckbox.checked = data.telah_terbit;
                        modalTitle.textContent = 'Edit Pengumuman';
                        showModal();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                });
        });
    });

    // tombol "Hapus"
    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Pengumuman ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('pengumuman/actions/proses_pengumuman.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ action: 'delete', id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    });
                }
            });
        });
    });
    
    // untuk Tambah dan Edit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = idInput.value;
        const action = id ? 'update' : 'create';
        
        const formData = {
            action: action,
            id_pengumuman: id,
            judul: judulInput.value,
            konten: kontenInput.value,
            start_date: startDateInput.value,
            end_date: endDateInput.value,
            telah_terbit: terbitCheckbox.checked
        };
        
        fetch('pengumuman/actions/proses_pengumuman.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideModal();
                Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        });
    });

    // untuk menutup modal
    modalCloseBtn.addEventListener('click', hideModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) hideModal();
    });
});