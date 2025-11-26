<?php
$base_url = '/simaksi/admin'; 
?>
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<style>
    .form-container-reservasi {
        max-width: 1100px;
        margin-left: auto;
        margin-right: auto;
        padding: 1.5rem;
    }
    .form-container-reservasi .card-wrapper {
        background-color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); 
        border-radius: 1.5rem; 
        padding: 2.5rem;
    }
    .form-container-reservasi .title-section {
        font-size: 1.875rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        border-bottom: 2px solid #e5e7eb; 
        padding-bottom: 0.75rem;
    }
    .form-container-reservasi .title-section iconify-icon {
        color: #35542E;
        margin-right: 0.75rem;
        font-size: 2rem; 
    }
    .form-container-reservasi .group-card {
        border: 1px solid #d1d5db;
        padding: 1.5rem;
        border-radius: 0.75rem; 
        background-color: #fcfcfc; 
        transition: box-shadow 0.3s, transform 0.3s;
        margin-bottom: 2rem;
    }
    .form-container-reservasi .group-card:hover {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    .form-container-reservasi .group-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #35542E;
        margin-bottom: 1rem;
        border-bottom: 1px solid #d1d5db;
        padding-bottom: 0.5rem;
    }

    /* untuk form input */
    .form-container-reservasi .form-input-wrapper {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.5rem;
    }
    @media (min-width: 768px) {
        .form-container-reservasi .form-input-wrapper {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .form-container-reservasi .member-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .floating-label-group {
        position: relative;
        margin-top: 1.25rem; 
    }
    
    .form-container-reservasi .form-input {
        width: 100%;
        padding: 14px 10px 6px 10px; 
        border: 1px solid #75B368; 
        border-radius: 0.5rem; 
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06); 
        transition: all 0.2s;
        font-size: 0.875rem;
        box-sizing: border-box; 
        font-family: inherit;
    }

    .floating-label-group label {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        color: #6b7280; 
        font-size: 0.875rem;
        pointer-events: none;
        transition: all 0.2s ease-out;
        background: none;
        padding: 0 5px;
    }

    .form-container-reservasi .form-input:focus + label,
    .form-container-reservasi .form-input:not(:placeholder-shown) + label,
    .form-container-reservasi .form-input:valid + label { 
        top: 0;
        transform: translateY(-50%) scale(0.85);
        font-weight: 600;
        color: #35542E;
        background-color: #fcfcfc; 
    }
    
    .floating-label-group input[type="date"] + label {
        top: 0;
        transform: translateY(-50%) scale(0.85);
        font-weight: 600;
        color: #35542E;
        background-color: #fcfcfc;
    }

    .form-container-reservasi .form-input:focus {
        border-color: #35542E;
        outline: none;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3); 
    }

    /* untuk tampilan hitung harga */
    .form-container-reservasi .total-price-display {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background-color: #e6fffa; 
        border-left: 6px solid #35542E;
        border-radius: 0.75rem;
    }
    .form-container-reservasi .total-price-text {
        font-size: 1.125rem;
        font-weight: 700;
        color: #75B368;
    }
    .form-container-reservasi .total-price-amount {
        font-size: 1.8rem; 
        font-weight: 800;
        color: #75B368; 
        margin-top: 0.25rem;
    }

    /* animasi */
    .form-container-reservasi .animate-spin {
        animation: spin 1s linear infinite;
    }
    .form-container-reservasi .animate-spin-fast {
        animation: spin 0.5s linear infinite;
    }
    .form-container-reservasi iconify-icon {
        display: inline-block;
        vertical-align: middle;
    }

    /* animasi notifikasi (Pop-up) */
    .form-container-reservasi .notification-bar {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transform: translateX(120%);
        opacity: 0;
        transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.5s ease-out;
    }
    .form-container-reservasi .notification-show {
        transform: translateX(0);
        opacity: 1;
    }
@keyframes trash-shake {
    0%, 100% { transform: rotate(0deg); }
    15%, 45%, 75% { transform: rotate(-10deg) scale(1.1); }
    30%, 60%, 90% { transform: rotate(10deg) scale(1.1); }
}

.shimmer-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease-in-out; 
    z-index: 10; 
}

/* Pseudo-elemen untuk efek Shimmer (Cahaya Swipe) */
.shimmer-btn::before {
    content: '';
    position: absolute; 
    top: 0;
    left: -100%;
    width: 75%;
    height: 100%;
    background: linear-gradient(
        120deg,
        rgba(255, 255, 255, 0) 20%,
        rgba(255, 255, 255, 0.4) 50%,
        rgba(255, 255, 255, 0) 80%
    );
    transform: skewX(-25deg);
    transition: left 0.5s ease-in-out; 
}

/* Animasi Shimmer (Cahaya Swipe) saat hover */
.shimmer-btn:hover::before {
    left: 125%; 
}

.group-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .group-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }

        .form-input-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .floating-label-group {
            position: relative;
            margin-top: 10px;
            padding-top: 10px;
        }

        .form-input, .floating-label-group select {
            width: 100%;
            padding: 10px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus, .floating-label-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .floating-label-group select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 0.8em;
            padding-right: 30px; 
        }

        .floating-label-group label {
            position: absolute;
            top: 0;
            left: 10px;
            pointer-events: none;
            transition: 0.2s ease all;
            color: #999;
            background-color: #fff;
            padding: 0 5px;
            font-size: 0.85rem; 
        }
        .form-input:focus ~ label,
        .form-input:not(:placeholder-shown) ~ label {
            top: 0;
            font-size: 0.85rem;
            color: #007bff;
        }

        .floating-label-group select:focus ~ label,
        .floating-label-group select:valid ~ label:not(:empty) {
            top: 0;
            font-size: 0.85rem;
            color: #007bff;
        }

        .floating-label-group select:disabled ~ label {
            color: #555; 
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        
        .text-gray-500 {
            color: #6b7280; 
        }

        .mt-1 {
            margin-top: 0.25rem; 
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .mr-2 {
            margin-right: 0.5rem; 
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spinner {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        .text-base {
            font-size: 1rem;
        }

.shimmer-btn:hover {
    transform: translateY(-4px) scale(1.01); 
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); 
}

.shimmer-btn:active {
    transform: translateY(1px) scale(0.99);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

/* tampilan gaya untuk tombol submit*/

.form-container-reservasi .btn-submit-active {
    background-color: #35542E; 
    box-shadow: 0 4px 12px rgba(53, 84, 46, 0.4); 
}
.form-container-reservasi .btn-submit-disabled {
    background-color: #75B368; 
    cursor: not-allowed;
    box-shadow: none;
}

/* tampilan gaya untuk tombol tambah barang */
#addBarangBtn {
    background: linear-gradient(135deg, #75B368 0%, #35542E 100%) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(53, 84, 46, 0.3);
    border: none;
    font-weight: 600;
    width: auto; 
    padding: 10px 25px; 
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex; 
}

#addBarangBtn:hover {
    box-shadow: 0 6px 20px rgba(53, 84, 46, 0.5);
    transform: translateY(-2px) !important; 
}

/* gaya untuk tombol hapus barang */
.remove-barang-btn {
    background-color: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.remove-barang-btn:hover {
    background-color: #ef4444;
    color: white;
    transform: scale(1.1);
    animation: trash-shake 0.5s ease-in-out; 
}
.remove-barang-btn iconify-icon {
    color: inherit !important;
}

</style>
<div class="form-container-reservasi">
    <div class="card-wrapper">

        <h1 class="title-section">
            <iconify-icon icon="ph:calendar-plus-fill" class="mr-3"></iconify-icon> Form Tambah Reservasi Pendakian
        </h1>

        <div id="statusMessage" class="hidden" role="alert"></div>

        <form id="reservasiForm" class="space-y-8">
            
            <div class="group-card">
            <h3 class="group-title">Ketua Rombongan</h3>
        
        <div class="floating-label-group">
            <select id="id_pengguna" required class="form-input" disabled>
                <option value="" disabled selected></option> 
                <option value="1">Pengguna A</option> 
                <option value="2" selected>Pengguna B (Dipilih)</option> 
            </select>
            <label for="id_pengguna">Pilih Ketua Rombongan</label>
        </div>
        
        <p id="loadingProfiles" class="text-xs text-gray-500 mt-1 flex items-center">
             <iconify-icon icon="ph:spinner-gap-fill" class="spinner mr-2 text-base"></iconify-icon> Memuat daftar pengguna...
        </p>
    </div>

    <div class="group-card">
        <h3 class="group-title">Detail Pemesanan</h3>
        
        <div class="form-input-wrapper">
            
            <div class="floating-label-group">
                <input type="date" id="tanggal_pendakian" required class="form-input" placeholder=" ">
                <label for="tanggal_pendakian">Tanggal Pendakian</label>
                <p id="kuotaStatus" class="text-xs mt-1 text-gray-500">Kuota: Tersedia 100/150</p>
            </div>

            <div class="floating-label-group">
                <input type="number" id="jumlah_pendaki" required min="1" value="1" class="form-input" placeholder=" ">
                <label for="jumlah_pendaki">Jumlah Pendaki (Total)</label>
            </div>

            <div class="floating-label-group">
                <input type="number" id="jumlah_tiket_parkir" min="0" value="0" class="form-input" placeholder=" ">
                <label for="jumlah_tiket_parkir">Jumlah Tiket Parkir (Unit)</label>
            </div>
        </div>

                <div class="total-price-display">
                    <p class="total-price-text">Total Harga Estimasi:</p>
                    <p id="totalHargaDisplay" class="total-price-amount">Rp 0</p>
                    <input type="hidden" id="total_harga" value="0">
                    <input type="hidden" id="harga_tiket_per_orang">
                    <input type="hidden" id="harga_parkir_per_unit">
                </div>
            </div>

            <div class="group-card">
                <h3 class="group-title">Data Anggota Rombongan</h3>
                
                <div id="anggotaRombonganContainer" class="space-y-4">
                </div>
            </div>

            <div class="group-card">
                <h3 class="group-title">Barang Bawaan (Potensi Sampah)</h3>
                
                <div id="barangBawaanContainer" class="space-y-4">
                </div>
                
                <button type="button" id="addBarangBtn" 
                    class="mt-4 text-sm text-white py-2 px-4 rounded-md 
                           transition duration-150 transform hover:scale-[1.02] flex items-center shimmer-btn">
                    <iconify-icon icon="ph:plus-circle-fill" class="mr-1" style="font-size: 1rem;"></iconify-icon> Tambah Barang
                </button>

                <div class="mt-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-sm text-yellow-800 rounded-md">
                    Total Potensi Sampah: <span id="totalPotensiSampah" class="font-bold">0</span> Unit
                    <input type="hidden" id="jumlah_potensi_sampah" value="0">
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" id="submitBtn" disabled
                        class="w-full py-3 px-4 rounded-md shadow-lg text-lg font-medium text-white btn-submit-disabled shimmer-btn">
                    <iconify-icon icon="ph:floppy-disk-fill" class="mr-2" style="font-size: 1.25rem;"></iconify-icon> Buat Reservasi
                </button>
            </div>
        </form>

    </div>
</div>

<div id="notificationArea" class="form-container-reservasi"></div>


<script>
    const BASE_URL = '<?php echo $base_url; ?>'; 
    const RESERVATION_API_URL = `${BASE_URL}/api/bikin_reservasi.php`; 
    const QUOTA_API_URL = `${BASE_URL}/api/kuota.php`; 
    const PROFILES_API_URL = `${BASE_URL}/api/pengguna.php`;
    let hargaTiket = 0;
    let hargaParkir = 0;
    let isQuotaSufficient = false;

    // untuk elemen-elemen form
    const idPenggunaSelect = document.getElementById('id_pengguna');
    const tanggalInput = document.getElementById('tanggal_pendakian');
    const jumlahPendakiInput = document.getElementById('jumlah_pendaki');
    const jumlahParkirInput = document.getElementById('jumlah_tiket_parkir');
    const totalHargaDisplay = document.getElementById('totalHargaDisplay');
    const totalHargaInput = document.getElementById('total_harga');
    const kuotaStatus = document.getElementById('kuotaStatus');
    const anggotaContainer = document.getElementById('anggotaRombonganContainer');
    const barangContainer = document.getElementById('barangBawaanContainer');
    const submitBtn = document.getElementById('submitBtn');
    const addBarangBtn = document.getElementById('addBarangBtn');
    const totalPotensiSampahInput = document.getElementById('jumlah_potensi_sampah');
    const loadingProfiles = document.getElementById('loadingProfiles');
    const notificationArea = document.getElementById('notificationArea');

    // untuk inisialisasi format harga
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    }

    /** Menampilkan pesan status menggunakan notifikasi pop-up animasi */
    function displayMessage(type, title, text) {
        const iconMap = {
            success: 'ph:check-circle-fill',
            error: 'ph:x-circle-fill',
            warning: 'ph:warning-circle-fill'
        };
        const colorMap = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500'
        };

        // Buat elemen notifikasi baru
        const notification = document.createElement('div');
        notification.className = `notification-bar p-4 rounded-lg text-white ${colorMap[type]}`;
        notification.innerHTML = `
            <div class="flex items-start space-x-3">
                <iconify-icon icon="${iconMap[type]}" style="font-size: 1.5rem;"></iconify-icon>
                <div>
                    <p class="font-bold">${title}</p>
                    <p class="text-sm">${text}</p>
                </div>
            </div>
        `;
        notificationArea.appendChild(notification);
        setTimeout(() => notification.classList.add('notification-show'), 10);
        setTimeout(() => {
            notification.classList.remove('notification-show');
            setTimeout(() => notification.remove(), 500); 
        }, 5000); 
    }

    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            if (!file) {
                resolve(null);
                return;
            }
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

    // untuk bagian update reservasi
    function createMemberForm(index, isLeader = false) {
        const memberId = `member_${index}`;
        const isLeaderText = isLeader ? '<span class="text-xs text-red-500 font-semibold">(Ketua Rombongan / Diri Sendiri)</span>' : '';
        const html = `
            <div id="${memberId}" class="p-4 border border-gray-200 rounded-md bg-white shadow-sm transition duration-300 hover:shadow-md">
                <h4 class="font-bold text-gray-800 mb-3">Pendaki ${index + 1} ${isLeaderText}</h4>
                
                <div class="member-grid form-input-wrapper grid-cols-1 gap-4">
                    
                    <div class="floating-label-group col-span-1">
                        <input type="text" data-member-field="nama_lengkap" required 
                            class="form-input" placeholder=" " />
                        <label>Nama Lengkap</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <input type="text" data-member-field="nik" required 
                            class="form-input" placeholder=" " />
                        <label>NIK</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <input type="text" data-member-field="alamat" required 
                            class="form-input" placeholder=" " />
                        <label>Alamat</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <input type="tel" data-member-field="nomor_telepon" required 
                            class="form-input" placeholder=" " />
                        <label>Nomor Telepon</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <input type="tel" data-member-field="kontak_darurat" required 
                            class="form-input" placeholder=" " />
                        <label>Kontak Darurat</label>
                    </div>
                    
                    <div class="floating-label-group col-span-1">
                        <input type="file" data-member-field="surat_sehat_file" accept="image/*,application/pdf"
                            class="form-input file-input" placeholder=" " />
                        <label>Surat Sehat (File Gambar/PDF)</label>
                        <input type="hidden" data-member-field="url_surat_sehat" class="surat-sehat-base64" />
                        <p class="file-status text-xs mt-1 text-gray-500"></p>
                    </div>
                </div>
            </div>
        `;
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        return div.firstChild;
    }

    function updateMemberForms(count) {
        anggotaContainer.innerHTML = '';
        const actualCount = Math.max(1, count); 
        for (let i = 0; i < actualCount; i++) {
            anggotaContainer.appendChild(createMemberForm(i, i === 0)); 
        }
        checkFormValidity();
    }

    /* membuat formulir barang bawaan*/
    function createBarangForm(index) {
        const barangId = `barang_${index}`;
        const html = `
            <div id="${barangId}" class="p-3 border border-gray-200 rounded-md bg-white shadow-sm flex items-center space-x-3 transition duration-300 hover:bg-gray-100">
                <div class="form-input-wrapper" style="grid-template-columns: repeat(3, minmax(0, 1fr)); flex-grow: 1; gap: 1rem; margin-top: -0.5rem;">
                    
                    <div class="floating-label-group col-span-1">
                        <input type="text" data-barang-field="nama_barang" required 
                            class="form-input" placeholder=" " />
                        <label>Nama Barang</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <select data-barang-field="jenis_sampah" required class="form-input" placeholder=" ">
                            <option value="" disabled selected></option> <!-- Kosong untuk placeholder -->
                            <option value="organik">Organik</option>
                            <option value="anorganik">Anorganik</option>
                        </select>
                        <label>Jenis Sampah</label>
                    </div>

                    <div class="floating-label-group col-span-1">
                        <input type="number" data-barang-field="jumlah" required min="1" value="1"
                            class="form-input barang-jumlah" placeholder=" " />
                        <label>Jumlah Unit</label>
                    </div>
                </div>
                
                <!-- PERBAIKAN: Tombol Hapus dengan style baru -->
                <button type="button" data-remove-id="${barangId}" 
                    class="remove-barang-btn">
                    <iconify-icon icon="ph:trash-fill" style="font-size: 1rem;"></iconify-icon>
                </button>
            </div>
        `;
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        return div.firstChild;
    }

    function updatePotensiSampah() {
        if (!totalPotensiSampah || !totalPotensiSampahInput) return; 

        const total = Array.from(document.querySelectorAll('.barang-jumlah')).reduce((sum, input) => {
            return sum + (parseInt(input.value) || 0);
        }, 0);
        document.getElementById('totalPotensiSampah').textContent = total;
        totalPotensiSampahInput.value = total;
        checkFormValidity();
    }

    async function fetchProfiles() {
        loadingProfiles.classList.add('flex');
        idPenggunaSelect.disabled = true;

        try {
            const response = await fetch(PROFILES_API_URL); 
            if (!response.ok) {
                throw new Error(`Gagal mengambil data profiles. Status HTTP: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.status === 'success' && result.data && result.data.length > 0) {
                idPenggunaSelect.innerHTML = '<option value="" disabled selected></option>';
                result.data.forEach(profile => {
                    const option = document.createElement('option');
                    option.value = profile.id;
                    option.textContent = `${profile.nama_lengkap} (${profile.id.substring(0, 8)}...)`;
                    idPenggunaSelect.appendChild(option);
                });
                loadingProfiles.innerHTML = `<iconify-icon icon="ph:check-circle-fill" class="text-green-500 mr-2"></iconify-icon> ${result.data.length} pengguna dimuat.`;
                idPenggunaSelect.disabled = false;
            } else {
                loadingProfiles.innerHTML = '<iconify-icon icon="ph:warning-circle-fill" class="text-red-500 mr-2"></iconify-icon> Tidak ada data pengguna ditemukan! (Cek: api/pengguna.php)';
                displayMessage('error', 'Error Pengguna:', 'API Pengguna tidak mengembalikan data. Pastikan tabel Profiles terisi.');
            }
        } catch (error) {
            loadingProfiles.innerHTML = `<iconify-icon icon="ph:x-circle-fill" class="text-red-500 mr-2"></iconify-icon> Error: ${error.message}. Path: ${PROFILES_API_URL}`;
            displayMessage('error', 'Error Pengguna:', `Gagal memuat daftar Ketua Rombongan. Cek Konsol (F12) untuk detail URL yang salah.`);
        } finally {
            loadingProfiles.classList.remove('flex');
            checkFormValidity();
        }
    }
    
    async function fetchPricing() {
        try {
            const response = await fetch(RESERVATION_API_URL); 
            if (!response.ok) throw new Error('Gagal mengambil data harga.');
            const result = await response.json(); 
            
            if (result.status === 'success' && result.data) {
                const pricingMap = result.data;
                hargaTiket = pricingMap['tiket_masuk'] || 0; 
                hargaParkir = pricingMap['tiket_parkir'] || 0;
                
                document.getElementById('harga_tiket_per_orang').value = hargaTiket;
                document.getElementById('harga_parkir_per_unit').value = hargaParkir;
                calculateTotal(); 
            } else {
                displayMessage('warning', 'Konfigurasi Harga Belum Ada:', 'Menggunakan harga 0. Cek data di tabel pengaturan_biaya.');
            }
        } catch (error) {
            console.error('Error fetching pricing:', error);
            displayMessage('error', 'Error Harga:', 'Gagal mengambil data harga tiket. Cek API Bikin Reservasi GET.');
        }
    }

    async function checkQuota() {
        const tanggal = tanggalInput.value;
        const jumlahPendaki = parseInt(jumlahPendakiInput.value) || 0;

        if (!tanggal || jumlahPendaki === 0) {
            kuotaStatus.textContent = '';
            isQuotaSufficient = false;
            checkFormValidity();
            return;
        }

        kuotaStatus.innerHTML = '<iconify-icon icon="ph:spinner-gap-fill" class="animate-spin-fast mr-1"></iconify-icon> Memeriksa kuota...';
        kuotaStatus.classList.replace('text-red-600', 'text-gray-500');


        try {
            const response = await fetch(`${QUOTA_API_URL}?tanggal=${tanggal}`);
            const data = await response.json();

            if (data.status === 'error') throw new Error(data.message);
            
            const kuotaData = data.data; 
            let kuotaMaksimal = 50; 
            let kuotaTerpesan = 0;

            if (kuotaData && kuotaData.length > 0) {
                kuotaMaksimal = kuotaData[0].kuota_maksimal;
                kuotaTerpesan = kuotaData[0].kuota_terpesan;
            }

            const sisaKuota = kuotaMaksimal - kuotaTerpesan;
            
            if (jumlahPendaki > sisaKuota) {
                kuotaStatus.innerHTML = `<iconify-icon icon="ph:x-circle-fill" class="text-red-600 mr-1"></iconify-icon> Kuota tidak cukup. Sisa: ${sisaKuota} / ${kuotaMaksimal}`;
                kuotaStatus.classList.replace('text-gray-500', 'text-red-600');
                isQuotaSufficient = false;
            } else {
                kuotaStatus.innerHTML = `<iconify-icon icon="ph:check-circle-fill" class="text-green-600 mr-1"></iconify-icon> Kuota tersedia: ${sisaKuota} / ${kuotaMaksimal}`;
                kuotaStatus.classList.replace('text-gray-500', 'text-green-600');
                isQuotaSufficient = true;
            }

        } catch (error) {
            kuotaStatus.innerHTML = `<iconify-icon icon="ph:warning-circle-fill" class="text-red-600 mr-1"></iconify-icon> Gagal memeriksa kuota.`;
            kuotaStatus.classList.replace('text-gray-500', 'text-red-600');
            isQuotaSufficient = false;
        }
        checkFormValidity();
    }

    // untuk logika perhitungan harga 
    function calculateTotal() {
        const jumlahPendaki = parseInt(jumlahPendakiInput.value) || 0;
        const jumlahParkir = parseInt(jumlahParkirInput.value) || 0;

        const totalTiket = jumlahPendaki * hargaTiket;
        const totalParkir = jumlahParkir * hargaParkir;
        const grandTotal = totalTiket + totalParkir;

        totalHargaInput.value = grandTotal;
        totalHargaDisplay.textContent = formatRupiah(grandTotal);
    }

    function checkFormValidity() {
        const form = document.getElementById('reservasiForm');
        const isFormComplete = form.checkValidity();
        const isPendakiCountValid = parseInt(jumlahPendakiInput.value) > 0;
        const isTanggalSelected = tanggalInput.value.length > 0;
        const isKetuaSelected = idPenggunaSelect.value.length > 0;
        const allMembersComplete = Array.from(anggotaContainer.querySelectorAll('[required]')).every(input => input.value.trim().length > 0);
        const isUploading = Array.from(anggotaContainer.querySelectorAll('.file-status')).some(status => status.textContent.includes('Mengkonversi'));
        const allBarangComplete = Array.from(barangContainer.querySelectorAll('[required]')).every(input => input.value.trim().length > 0);

        const isDataValid = isFormComplete && isPendakiCountValid && isTanggalSelected && isKetuaSelected && isQuotaSufficient && allMembersComplete && allBarangComplete && !isUploading;

        submitBtn.disabled = !isDataValid;
        if (isDataValid) {
            submitBtn.classList.remove('btn-submit-disabled');
            submitBtn.classList.add('btn-submit-active');
        } else {
            submitBtn.classList.add('btn-submit-disabled');
            submitBtn.classList.remove('btn-submit-active');
        }
    }

    // untuk logika submit form
    async function handleFormSubmit(event) {
        event.preventDefault();
        
        if (submitBtn.disabled) {
            displayMessage('warning', 'Validasi Gagal:', 'Pastikan semua field terisi, Ketua Rombongan terpilih, dan kuota mencukupi, serta semua file sudah selesai diproses.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<iconify-icon icon="ph:spinner-gap-fill" class="animate-spin mr-2" style="font-size: 1.25rem;"></iconify-icon> Memproses...';
        
        const formData = {
            tanggal_pendakian: tanggalInput.value,
            jumlah_pendaki: parseInt(jumlahPendakiInput.value) || 0,
            jumlah_tiket_parkir: parseInt(jumlahParkirInput.value) || 0,
            total_harga: parseInt(totalHargaInput.value) || 0,
            jumlah_potensi_sampah: parseInt(totalPotensiSampahInput.value) || 0,
            id_pengguna: idPenggunaSelect.value, 
            anggota_rombongan: getAnggotaData(),
            barang_bawaan: getBarangBawaanData()
        };
        
        try {
            const response = await fetch(RESERVATION_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (response.ok) { 
                Swal.fire({
                    title: 'Berhasil!',
                    text: `Reservasi dengan kode ${result.kode_reservasi} berhasil dibuat.`,
                    icon: 'success',
                    timer: 2000, 
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?page=reservasi&tab=reservasi';
                });
                
            } else {
                throw new Error(result.error || 'Terjadi kesalahan saat membuat reservasi.');
            }
        } catch (error) {
            console.error('Submission Error:', error);
            displayMessage('error', 'Gagal:', error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="ph:floppy-disk-fill" class="mr-2" style="font-size: 1.25rem;"></iconify-icon> Buat Reservasi';
            checkFormValidity();
        }
    }

    // Fungsi untuk mengumpulkan data anggota
    function getAnggotaData() {
        const anggotaRombongan = [];
        Array.from(anggotaContainer.children).forEach(memberDiv => {
            const member = {};
            memberDiv.querySelectorAll('[data-member-field]').forEach(input => {
                if (input.type !== 'file') {
                    member[input.dataset.memberField] = input.value;
                }
            });
            anggotaRombongan.push(member);
        });
        return anggotaRombongan;
    }

    // Fungsi untuk mengumpulkan data barang bawaan
    function getBarangBawaanData() {
        const barangBawaan = [];
        Array.from(barangContainer.children).forEach(barangDiv => {
            const barang = {};
            barangDiv.querySelectorAll('[data-barang-field]').forEach(input => {
                barang[input.dataset.barangField] = input.value;
            });
            barangBawaan.push(barang);
        });
        return barangBawaan;
    }

// Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        fetchProfiles(); 
        fetchPricing();
        
        tanggalInput.min = new Date().toISOString().split('T')[0];

        idPenggunaSelect.addEventListener('change', checkFormValidity);
        tanggalInput.addEventListener('change', checkQuota);
        jumlahPendakiInput.addEventListener('input', () => {
            updateMemberForms(parseInt(jumlahPendakiInput.value) || 0);
            checkQuota();
            calculateTotal(); 
        });
        jumlahParkirInput.addEventListener('input', calculateTotal);
        
        addBarangBtn.addEventListener('click', () => {
            const count = barangContainer.children.length;
            barangContainer.appendChild(createBarangForm(count));
            updatePotensiSampah();
        });

        barangContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-barang-btn')) {
                const idToRemove = e.target.closest('button').dataset.removeId;
                document.getElementById(idToRemove).remove();
                updatePotensiSampah();
            }
        });
        barangContainer.addEventListener('input', (e) => {
            if (e.target.classList.contains('barang-jumlah') || e.target.closest('[data-barang-field]')) {
                updatePotensiSampah();
            }
        });

        // untuk menangani perubahan pada input file surat sehat
        document.getElementById('reservasiForm').addEventListener('change', async (e) => {
            if (e.target.type === 'file' && e.target.classList.contains('file-input')) {
                const fileInput = e.target;
                const file = fileInput.files[0];
                const container = fileInput.closest('.floating-label-group');
                const base64Input = container.querySelector('.surat-sehat-base64');
                const statusText = container.querySelector('.file-status');
                
                if (!file) {
                    base64Input.value = '';
                    statusText.textContent = '';
                    checkFormValidity();
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    statusText.textContent = '❌ File terlalu besar (> 2MB)';
                    statusText.classList.replace('text-gray-500', 'text-red-500');
                    base64Input.value = '';
                    checkFormValidity();
                    return;
                }

                statusText.textContent = `Mengkonversi ${file.name} ke Base64...`;
                statusText.classList.replace('text-red-500', 'text-gray-500');
                submitBtn.disabled = true; 

                try {
                    const base64Data = await fileToBase64(file);
                    base64Input.value = base64Data;
                    const mimeType = file.type;
                    const fileExtension = mimeType.split('/')[1] || 'dat';
                    base64Input.setAttribute('data-file-ext', fileExtension);
                    base64Input.setAttribute('data-file-name', file.name);

                    statusText.textContent = `✅ ${file.name} siap dikirim.`;
                    statusText.classList.replace('text-gray-500', 'text-green-500');

                } catch (error) {
                    statusText.textContent = '❌ Gagal membaca file.';
                    statusText.classList.replace('text-green-500', 'text-red-500');
                    base64Input.value = '';
                } finally {
                    checkFormValidity();
                }
            }
        });

        document.getElementById('reservasiForm').addEventListener('input', checkFormValidity);
        document.getElementById('reservasiForm').addEventListener('submit', handleFormSubmit);
        updateMemberForms(1); 
    });
</script>