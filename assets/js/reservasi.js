let rombonganCounter = 0; 
let barangCounter = 0; 
let rombonganToDelete = [];
let barangToDelete = []; 

const HARGA_TIKET_MASUK = 20000;
const HARGA_TIKET_PARKIR = 5000;

function removeStuckOverlay() {
    const containers = document.querySelectorAll('.swal2-container');
    containers.forEach(container => {
        if (container.parentElement) {
            container.parentElement.removeChild(container);
        }
    });
    
    document.body.classList.remove('swal2-shown', 'swal2-height-auto', 'swal2-no-backdrop', 'swal2-toast-shown'); 
    document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto'); 
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

document.addEventListener('DOMContentLoaded', function() {
    
    document.querySelectorAll('.btn-validasi').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            const reservasiId = this.getAttribute('data-id');
            if (reservasiId) {
                fetchDetailReservasi(reservasiId);
            }
        });
    });

    document.querySelectorAll('.btn-aksi-status').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const reservasiId = this.getAttribute('data-id');
            const newStatus = this.getAttribute('data-new-status');
            const actionText = (newStatus === 'terkonfirmasi') ? 'mengonfirmasi' : 'menyelesaikan';
            const kodeReservasi = this.getAttribute('data-kode');
            const namaKetua = this.getAttribute('data-ketua');

            Swal.fire({
                title: `Konfirmasi Aksi`,
                html: `Apakah Anda yakin ingin <strong>${actionText}</strong> reservasi:
                       <br><br>
                       Kode: <strong>${kodeReservasi}</strong>
                       <br>
                       Ketua: <strong>${namaKetua}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Ya, ${actionText}!`,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleStatusUpdate(reservasiId, newStatus);
                }
            });
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const id = this.dataset.id;
            const tanggal = this.dataset.tanggal;
            const jumlah = this.dataset.jumlah;

            Swal.fire({
                title: 'Anda Yakin?',
                html: `Anda akan menghapus reservasi untuk tanggal <strong>${tanggal}</strong> (${jumlah} pendaki).<br>Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_reservasi', id);

                    fetch('index.php?page=reservasi&action=hapus_reservasi', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(_ => { // Variabel error tidak dipakai
                        Swal.fire('Error!', 'Terjadi kesalahan saat menghubungi server.', 'error');
                    });
                }
            });
        });
    });

}); 

function formatRupiah(number) {
    if (isNaN(number) || number === null) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
}

function fetchDetailReservasi(id) {
    removeStuckOverlay(); 

    Swal.fire({ title: 'Memuat Data...', text: 'Sedang mengambil detail reservasi.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
    fetch(`api/get_reservasi_detail.php?id_reservasi=${id}`) 
        .then(response => {
            if (!response.ok) { throw new Error('Gagal menghubungi server. Status: ' + response.status); }
            return response.json();
        })
        .then(data => {
            Swal.close(true);
            if (data.success && data.detail) {
                rombonganCounter = 0; 
                barangCounter = 0;
                showValidationPopup(data.detail);
            } else {
                const message = data.message || 'Detail reservasi tidak ditemukan.';
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Detail', text: message });
            }
        })
        .catch(error => {
            Swal.close(true); 
            removeStuckOverlay(); 
            Swal.fire({ icon: 'error', title: 'Kesalahan Server', text: 'Terjadi kesalahan saat mengambil data: ' + error.message });
        });
}


window.createRombonganFields = function(data = {}, index) {
    if (!data || data.id_pendaki === null || data.id_pendaki === undefined) { 
        data = {}; 
    }

    const id = data.id_pendaki ? data.id_pendaki : 'new_' + rombonganCounter++;
    const isNew = id.toString().startsWith('new');
    
    return `
        <div class="rombongan-item" data-id="${id}">
            <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Pendaki #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
            <input type="hidden" name="rombongan[${id}][id]" value="${id}">
            
            <label>Nama Lengkap</label>
            <input type="text" name="rombongan[${id}][nama_lengkap]" value="${data.nama_lengkap || ''}" readonly class="search-input">

            <label>NIK</label>
            <input type="text" name="rombongan[${id}][nik]" value="${data.nik || ''}" readonly class="search-input">

            <label>Nomor Telepon</label>
            <input type="tel" name="rombongan[${id}][nomor_telepon]" value="${data.nomor_telepon || ''}" readonly class="search-input">

            <div class="form-row">
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="rombongan[${id}][alamat]" value="${data.alamat || ''}" readonly class="search-input">
                </div>
                <div class="form-group">
                    <label>Kontak Darurat</label>
                    <input type="text" name="rombongan[${id}][kontak_darurat]" value="${data.kontak_darurat || ''}" readonly class="search-input">
                </div>
            </div>
            
            <!-- PERBAIKAN: Tombol Hapus Pendaki Dihilangkan -->
        </div>
    `;
}

window.createBarangFields = function(data = {}, index) {
    if (!data || data.id_barang === null || data.id_barang === undefined) { 
        data = {}; 
    }

    const id = data.id_barang ? data.id_barang : 'new_barang_' + barangCounter++;
    const isNew = id.toString().startsWith('new_barang');
    
    let jenisSampah = data.jenis_sampah || 'organik';
    if (jenisSampah === 'anorganik') {
        jenisSampah = 'non-organik';
    }

    return `
        <div class="barang-item" data-id="${id}">
            <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Barang #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
            <input type="hidden" name="barang[${id}][id]" value="${id}">
            
            <label>Nama Barang</label>
            <input type="text" name="barang[${id}][nama_barang]" value="${data.nama_barang || ''}" readonly class="search-input">

            <div class="form-row">
                <div class="form-group">
                    <label>Jenis Sampah</label>
                    <select name="barang[${id}][jenis_sampah]" disabled class="search-input">
                        <option value="organik" ${jenisSampah === 'organik' ? 'selected' : ''}>Organik</option>
                        <option value="non-organik" ${jenisSampah === 'non-organik' ? 'selected' : ''}>Anorganik</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah (Unit)</label>
                    <input type="number" name="barang[${id}][jumlah]" value="${data.jumlah || 0}" readonly class="search-input">
                </div>
            </div>
            
            <!-- PERBAIKAN: Tombol Hapus Barang Dihilangkan -->
        </div>
    `;
}


function showValidationPopup(detail) {
    Swal.close(true); 
    removeStuckOverlay(); 

    const r = detail.reservasi;
    const p = detail.profiles;
    const namaKetua = p && p.nama_lengkap ? p.nama_lengkap : 'N/A';
    const idReservasi = r.id_reservasi; 
    const jumlahPendakiAwal = detail.pendaki_rombongan ? detail.pendaki_rombongan.length : 0;
    const jumlahPendakiValid = (jumlahPendakiAwal === 0 && r.jumlah_pendaki > 0) ? r.jumlah_pendaki : jumlahPendakiAwal;

    const formReservasiHtml = `
        <form id="form-reservasi-validasi">
            <div class="swal2-detail-section">
                <h4><i class="fa-solid fa-file-invoice"></i> Detail Pemesanan</h4>
                
                <label>Kode Booking</label>
                <input type="text" name="kode_reservasi" value="${r.kode_reservasi || 'N/A'}" readonly class="search-input">

                <label>Ketua Rombongan</label>
                <input type="text" name="nama_ketua" value="${namaKetua}" readonly class="search-input">

                <div class="form-row">
                    <div class="form-group">
                        <label>Tgl. Pendakian</label>
                        <!-- PERBAIKAN: Input TANGGAL dibuat readonly -->
                        <input type="date" name="tanggal_pendakian" value="${r.tanggal_pendakian || ''}" readonly class="search-input">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Pendaki</label>
                        <!-- PERBAIKAN: Input diubah menjadi teks (read-only) dan diisi otomatis -->
                        <input type="number" id="jumlah_pendaki_modal" name="jumlah_pendaki" value="${jumlahPendakiValid}" readonly class="search-input" style="background-color: #f3f4f6; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tiket Parkir</label>
                        <!-- PERBAIKAN: Input TIKET PARKIR dibuat readonly -->
                        <input type="number" name="jumlah_tiket_parkir" value="${r.jumlah_tiket_parkir || 0}" readonly class="search-input">
                    </div>
                    <div class="form-group">
                        <label>Total Harga (Rp)</label>
                        <input type="text" name="total_harga" value="${r.total_harga || 0}" readonly class="search-input">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select name="status" class="search-input">
                            <option value="menunggu_pembayaran" ${r.status === 'menunggu_pembayaran' ? 'selected' : ''}>Menunggu Pembayaran</option>
                            <!-- PERBAIKAN: Opsi 'sudah_bayar' dihapus karena tidak ada di ENUM database -->
                            <!-- <option value="sudah_bayar" ${r.status === 'sudah_bayar' ? 'selected' : ''}>Sudah Bayar</option> -->
                            <option value="terkonfirmasi" ${r.status === 'terkonfirmasi' ? 'selected' : ''}>Terkonfirmasi</option>
                            <option value="selesai" ${r.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                            <option value="dibatalkan" ${r.status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status Sampah</label>
                        <select name="status_sampah" class="search-input">
                            <option value="belum_dicek" ${r.status_sampah === 'belum_dicek' ? 'selected' : ''}>Belum Dicek</option>
                            <option value="sesuai" ${r.status_sampah === 'sesuai' ? 'selected' : ''}>Sesuai</option>
                            <option value="tidak_sesuai" ${r.status_sampah === 'tidak_sesuai' ? 'selected' : ''}>Tidak Sesuai</option>
                        </select>
                    </div>
                </div>
                <hr>
            </div>
        </form>
    `;

    let rombonganHtml = '<h4><i class="fa-solid fa-users"></i> Data Rombongan</h4>';
    rombonganHtml += '<div id="rombongan-container" class="swal2-form-array-container">';

    if (detail.pendaki_rombongan && detail.pendaki_rombongan.length > 0) {
        detail.pendaki_rombongan.forEach((item, index) => {
            rombonganHtml += createRombonganFields(item, index);
        });
    } else {
        rombonganHtml += createRombonganFields(null, 0);
    }

    rombonganHtml += '</div>';
    let barangHtml = '<h4><i class="fa-solid fa-box-open"></i> Barang & Sampah Bawaan</h4>';
    barangHtml += '<div id="barang-container" class="swal2-form-array-container">';

    if (detail.barang_sampah_bawaan && detail.barang_sampah_bawaan.length > 0) {
        detail.barang_sampah_bawaan.forEach((item, index) => {
            barangHtml += createBarangFields(item, index);
        });
    } else {
        barangHtml += createBarangFields(null, 0);
    }

    barangHtml += '</div>';
    const content = formReservasiHtml + rombonganHtml + barangHtml;

    Swal.fire({
        title: `Detail Reservasi (ID: ${idReservasi})`,
        icon: 'info',
        html: content,
        width: '85%', 
        showCloseButton: false, 
        allowOutsideClick: false, 
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        cancelButtonText: 'Tutup',
        customClass: { popup: 'swal2-detail-popup swal2-validation-form' },
        didOpen: () => {
             rombonganToDelete = [];
             barangToDelete = [];
             const jumlahPendakiInput = document.getElementById('jumlah_pendaki_modal');
             const tiketParkirInput = document.querySelector('input[name="jumlah_tiket_parkir"]');
             jumlahPendakiInput.addEventListener('change', updateModalTotalHarga); 
             tiketParkirInput.addEventListener('input', updateModalTotalHarga);
             updateModalTotalHarga();
        },
        preConfirm: () => {
             return handleValidationUpdate(idReservasi);
        },
        willClose: () => {
             removeStuckOverlay(); 
        }
    });
}


window.removeRombonganItem = function(button) { 
    const item = button.closest('.rombongan-item');
    const id = item.getAttribute('data-id');
    if (id && !id.startsWith('new_')) {
        rombonganToDelete.push(id);
    }
    
    item.remove(); 
    updateModalPendakiCount();
}
window.removeBarangItem = function(button) { 
    const item = button.closest('.barang-item');
    const id = item.getAttribute('data-id');
    if (id && !id.startsWith('new_barang_')) {
        barangToDelete.push(id);
    }

    item.remove(); 
}

window.tambahPendaki = function() {
    const container = document.getElementById('rombongan-container');
    if (container) { 
        container.insertAdjacentHTML('beforeend', createRombonganFields({}, container.children.length)); 
        updateModalPendakiCount();
    }
}
window.tambahBarang = function() {
    const container = document.getElementById('barang-container');
    if (container) { container.insertAdjacentHTML('beforeend', createBarangFields({}, container.children.length)); }
}

function updateModalPendakiCount() {
    const container = document.getElementById('rombongan-container');
    const inputJumlah = document.getElementById('jumlah_pendaki_modal');
    if (container && inputJumlah) {
        inputJumlah.value = container.children.length;
        inputJumlah.dispatchEvent(new Event('change'));
    }
}

function updateModalTotalHarga() {
    const jumlahPendakiInput = document.getElementById('jumlah_pendaki_modal');
    const tiketParkirInput = document.querySelector('input[name="jumlah_tiket_parkir"]');
    const totalHargaInput = document.querySelector('input[name="total_harga"]');

    if (!jumlahPendakiInput || !tiketParkirInput || !totalHargaInput) return;

    const jumlahPendaki = parseInt(jumlahPendakiInput.value) || 0;
    const jumlahParkir = parseInt(tiketParkirInput.value) || 0;

    const total = (jumlahPendaki * HARGA_TIKET_MASUK) + (jumlahParkir * HARGA_TIKET_PARKIR);

    totalHargaInput.value = total;
}


async function handleValidationUpdate(id) {
    const form = document.getElementById('form-reservasi-validasi');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    payload.id_reservasi = id;
    payload.jumlah_pendaki = document.getElementById('jumlah_pendaki_modal').value;
    const updateUrl = 'api/update_reservasi_status.php'; 

    try {
        const response = await fetch(updateUrl, {
            method: 'PATCH', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Gagal menyimpan perubahan.');
        }

        Swal.fire({
            title: 'Berhasil!',
            text: result.message || 'Data reservasi berhasil diperbarui.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            location.reload(); 
        });

    } catch (error) {
        Swal.showValidationMessage(`Gagal: ${error.message}`);
    }
}

function handleStatusUpdate(id, newStatus) {
    Swal.fire({
        title: 'Memperbarui Status...',
        text: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('id_reservasi', id);
    formData.append('new_status', newStatus);

    fetch('index.php?page=reservasi&action=update_status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success')
            .then(() => location.reload());
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(_ => {
        Swal.fire('Error!', 'Terjadi kesalahan saat menghubungi server.', 'error');
    });
}