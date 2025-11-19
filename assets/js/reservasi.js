let rombonganCounter = 0; 
let barangCounter = 0; 

// untuk menghapus overlay Swal2 yang mungkin tersisa
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
        button.addEventListener('click', function() {
            const reservasiId = this.getAttribute('data-id');
            if (reservasiId) {
                fetchDetailReservasi(reservasiId);
            }
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


    function showValidationPopup(detail) {
        
        Swal.close(true); 
        removeStuckOverlay(); 

        const r = detail.reservasi;
        const p = detail.profiles;
        const namaKetua = p && p.nama_lengkap ? p.nama_lengkap : 'N/A';
        const idReservasi = r.id_reservasi; 

        // untuk form utama reservasi
        const formReservasiHtml = `
            <form id="form-reservasi-validasi">
                <div class="swal2-detail-section">
                    <h4><i class="fa-solid fa-file-invoice"></i> Detail Pemesanan</h4>
                    
                    <label>Kode Booking</label>
                    <input type="text" name="kode_reservasi" value="${r.kode_reservasi || 'N/A'}" readonly>

                    <label>Ketua Rombongan</label>
                    <input type="text" name="nama_ketua" value="${namaKetua}" readonly>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tgl. Pendakian</label>
                            <input type="date" name="tanggal_pendakian" value="${r.tanggal_pendakian || ''}">
                        </div>
                        <div class="form-group">
                            <label>Jumlah Pendaki</label>
                            <input type="number" name="jumlah_pendaki" value="${r.jumlah_pendaki || 0}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tiket Parkir</label>
                            <input type="number" name="jumlah_tiket_parkir" value="${r.jumlah_tiket_parkir || 0}">
                        </div>
                        <div class="form-group">
                            <label>Total Harga (Rp)</label>
                            <input type="text" name="total_harga" value="${r.total_harga || 0}" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Status Pembayaran</label>
                            <select name="status">
                                <option value="menunggu_pembayaran" ${r.status === 'menunggu_pembayaran' ? 'selected' : ''}>Menunggu Pembayaran</option>
                                <option value="sudah_bayar" ${r.status === 'sudah_bayar' ? 'selected' : ''}>Sudah Bayar</option>
                                <option value="terkonfirmasi" ${r.status === 'terkonfirmasi' ? 'selected' : ''}>Terkonfirmasi</option>
                                <option value="dibatalkan" ${r.status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status Sampah</label>
                            <select name="status_sampah">
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

        // untuk data rombongan
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

        rombonganHtml += `<button type="button" class="btn green" id="btn-tambah-rombongan" style="margin-top: 10px;">
            <i class="fa-solid fa-plus"></i> Tambah Pendaki
        </button>`;

        // untuk data barang bawaan
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

        barangHtml += `<button type="button" class="btn green" id="btn-tambah-barang" style="margin-top: 10px;">
            <i class="fa-solid fa-plus"></i> Tambah Barang
        </button>`;


        const content = formReservasiHtml + rombonganHtml + barangHtml;

        Swal.fire({
            title: `Validasi Reservasi (ID: ${idReservasi})`,
            icon: 'info',
            html: content,
            width: '85%', 
            showCloseButton: false, 
            allowOutsideClick: false, 
            showCancelButton: true,
            confirmButtonText: 'Simpan & Validasi',
            cancelButtonText: 'Batal',
            customClass: { popup: 'swal2-detail-popup swal2-validation-form' },
            didOpen: () => {
                 document.getElementById('btn-tambah-rombongan').addEventListener('click', tambahPendaki);
                 document.getElementById('btn-tambah-barang').addEventListener('click', tambahBarang);
            },
            preConfirm: () => {
                return handleValidationUpdate(idReservasi);
            },
            willClose: () => {
                removeStuckOverlay(); 
            }
        });
    }

    // untuk membuat set input field rombongan (4 KOLOM)
    window.createRombonganFields = function(data = {}, index) {
        if (!data || data.id === null) { data = {}; index = rombonganCounter; }

        const id = data.id_pendaki ? data.id_pendaki : 'new_' + rombonganCounter++;
        const isNew = id.toString().startsWith('new');
        
        return `
            <div class="rombongan-item" data-id="${id}">
                <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Pendaki #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
                <input type="hidden" name="rombongan[${id}][id]" value="${id}">
                
                <label>Nama Lengkap</label>
                <input type="text" name="rombongan[${id}][nama_lengkap]" value="${data.nama_lengkap || ''}" required>

                <label>NIK</label>
                <input type="text" name="rombongan[${id}][nik]" value="${data.nik || ''}" required>

                <div class="form-row">
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="rombongan[${id}][alamat]" value="${data.alamat || ''}">
                    </div>
                    <div class="form-group">
                        <label>Kontak Darurat</label>
                        <input type="text" name="rombongan[${id}][kontak_darurat]" value="${data.kontak_darurat || ''}">
                    </div>
                </div>
                
                <button type="button" class="btn red btn-remove-rombongan" onclick="removeRombonganItem(this)" style="display: block; width: 100%; margin-top: 10px;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Pendaki
                </button>
            </div>
        `;
    }
    
    // untuk membuat set input field barang bawaan (3 KOLOM)
    window.createBarangFields = function(data = {}, index) {
        if (!data || data.id === null) { data = {}; index = barangCounter; }

        const id = data.id ? data.id : 'new_barang_' + barangCounter++;
        const isNew = id.toString().startsWith('new_barang');
        
        return `
            <div class="barang-item" data-id="${id}">
                <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Barang #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
                <input type="hidden" name="barang[${id}][id]" value="${id}">
                
                <label>Nama Barang</label>
                <input type="text" name="barang[${id}][nama_barang]" value="${data.nama_barang || ''}" required>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Sampah</label>
                        <select name="barang[${id}][jenis_sampah]">
                            <option value="organik" ${data.jenis_sampah === 'organik' ? 'selected' : ''}>Organik</option>
                            <option value="anorganik" ${data.jenis_sampah === 'anorganik' ? 'selected' : ''}>Anorganik</option>
                            <option value="bahaya" ${data.jenis_sampah === 'bahaya' ? 'selected' : ''}>Bahan Berbahaya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Unit)</label>
                        <input type="number" name="barang[${id}][jumlah]" value="${data.jumlah || 0}" required>
                    </div>
                </div>
                
                <button type="button" class="btn red btn-remove-barang" onclick="removeBarangItem(this)" style="display: block; width: 100%; margin-top: 10px;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Barang
                </button>
            </div>
        `;
    }

    // Fungsi untuk menangani tombol Hapus
    window.removeRombonganItem = function(button) { button.closest('.rombongan-item').remove(); }
    window.removeBarangItem = function(button) { button.closest('.barang-item').remove(); }

    // Fungsi untuk menambahkan baris kosong
    window.tambahPendaki = function(event) {
        const container = document.getElementById('rombongan-container');
        if (container) { container.insertAdjacentHTML('beforeend', createRombonganFields({}, container.children.length)); }
    }
    window.tambahBarang = function(event) {
        const container = document.getElementById('barang-container');
        if (container) { container.insertAdjacentHTML('beforeend', createBarangFields({}, container.children.length)); }
    }


    // Fungsi untuk mengirim data update ke server
    function handleValidationUpdate(id) {
        const form = document.getElementById('form-reservasi-validasi');
        const formData = new FormData(form);
        
        const rombonganData = {};
        document.querySelectorAll('.rombongan-item').forEach(item => {
            const itemId = item.getAttribute('data-id');
            rombonganData[itemId] = {
                id: itemId,
                nama_lengkap: item.querySelector(`[name="rombongan[${itemId}][nama_lengkap]"]`).value,
                nik: item.querySelector(`[name="rombongan[${itemId}][nik]"]`).value,
                alamat: item.querySelector(`[name="rombongan[${itemId}][alamat]"]`).value,
                kontak_darurat: item.querySelector(`[name="rombongan[${itemId}][kontak_darurat]"]`).value,
            };
        });
        
        const barangData = {};
        document.querySelectorAll('.barang-item').forEach(item => {
            const itemId = item.getAttribute('data-id');
            barangData[itemId] = {
                id: itemId,
                nama_barang: item.querySelector(`[name="barang[${itemId}][nama_barang]"]`).value,
                jenis_sampah: item.querySelector(`[name="barang[${itemId}][jenis_sampah]"]`).value,
                jumlah: item.querySelector(`[name="barang[${itemId}][jumlah]"]`).value,
            };
        });

        const payload = Object.fromEntries(formData.entries());
        payload.id_reservasi = id;
        payload.rombongan = rombonganData;
        payload.barang = barangData;

        const updateUrl = 'api/update_reservasi_status.php'; 

        return fetch(updateUrl, {
            method: 'PATCH', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) { throw new Error(result.message || 'Gagal menyimpan perubahan.'); }
            location.reload(); 
            return true; 
        })
        .catch(error => {
            Swal.showValidationMessage(`Gagal: ${error.message}`);
            return false;
        });
    }
});