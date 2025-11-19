-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 13 Sep 2025 pada 14.08
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_simaksi`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CekKetersediaanKuota` (IN `p_tanggal_pendakian` DATE, IN `p_jumlah_diminta` INT, OUT `p_tersedia` BOOLEAN)   BEGIN
    DECLARE v_kuota_maksimal INT;
    DECLARE v_kuota_terpesan INT;

    SELECT kuota_maksimal, kuota_terpesan INTO v_kuota_maksimal, v_kuota_terpesan
    FROM kuota_harian
    WHERE tanggal_kuota = p_tanggal_pendakian;

    IF v_kuota_maksimal IS NOT NULL AND (v_kuota_maksimal - v_kuota_terpesan) >= p_jumlah_diminta THEN
        SET p_tersedia = TRUE;
    ELSE
        SET p_tersedia = FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `KonfirmasiPembayaranDanCatatPemasukan` (IN `input_id_reservasi` INT, IN `input_id_admin` INT)   BEGIN
    -- Deklarasi variabel untuk menampung data dari reservasi
    DECLARE v_total_harga INT;
    DECLARE v_status_sekarang ENUM('menunggu_pembayaran', 'terkonfirmasi', 'dibatalkan', 'selesai');
    DECLARE v_kode_reservasi VARCHAR(20);

    -- Mulai transaksi untuk memastikan semua query berhasil atau tidak sama sekali
    START TRANSACTION;

    -- Ambil data harga dan status dari reservasi yang akan dikonfirmasi
    SELECT total_harga, status, kode_reservasi INTO v_total_harga, v_status_sekarang, v_kode_reservasi
    FROM reservasi
    WHERE id_reservasi = input_id_reservasi;

    -- Cek apakah reservasi ada dan statusnya masih 'menunggu_pembayaran'
    IF v_total_harga IS NOT NULL AND v_status_sekarang = 'menunggu_pembayaran' THEN

        -- Langkah 1: Ubah status reservasi menjadi 'terkonfirmasi'
        UPDATE reservasi
        SET status = 'terkonfirmasi'
        WHERE id_reservasi = input_id_reservasi;

        -- Langkah 2: Tambahkan data baru ke tabel pemasukan
        INSERT INTO pemasukan (id_reservasi, id_admin, jumlah, keterangan, tanggal_pemasukan)
        VALUES (
            input_id_reservasi,
            input_id_admin,
            v_total_harga,
            CONCAT('Pemasukan dari tiket reservasi kode: ', v_kode_reservasi),
            CURDATE() -- Menggunakan tanggal hari ini saat konfirmasi
        );

        -- Jika semua berhasil, simpan perubahan
        COMMIT;

    ELSE
        -- Jika reservasi tidak ditemukan atau statusnya sudah bukan 'menunggu_pembayaran',
        -- batalkan semua operasi dalam transaksi ini.
        ROLLBACK;
        -- Anda bisa menambahkan sinyal error di sini untuk ditangkap oleh backend
        -- SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reservasi tidak dapat dikonfirmasi.';
    END IF;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `checklist_sampah_reservasi`
--

CREATE TABLE `checklist_sampah_reservasi` (
  `id_checklist` int UNSIGNED NOT NULL,
  `id_reservasi` int UNSIGNED NOT NULL,
  `id_item` int UNSIGNED NOT NULL,
  `jumlah_dibawa` smallint UNSIGNED NOT NULL COMMENT 'Jumlah item yang dibawa naik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Menyimpan detail checklist sampah untuk setiap reservasi';

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_sampah`
--

CREATE TABLE `item_sampah` (
  `id_item` int UNSIGNED NOT NULL,
  `nama_item` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Daftar master barang bawaan yang berpotensi jadi sampah';

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_pengeluaran`
--

CREATE TABLE `kategori_pengeluaran` (
  `id_kategori` int UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mengelompokkan jenis-jenis pengeluaran';

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuota_harian`
--

CREATE TABLE `kuota_harian` (
  `id_kuota` int UNSIGNED NOT NULL,
  `tanggal_kuota` date NOT NULL,
  `kuota_maksimal` int UNSIGNED NOT NULL COMMENT 'Kuota maksimal yang ditetapkan admin',
  `kuota_terpesan` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Kuota terisi, diupdate oleh trigger'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Menyimpan informasi kuota pendakian per hari';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemasukan`
--

CREATE TABLE `pemasukan` (
  `id_pemasukan` int UNSIGNED NOT NULL,
  `id_reservasi` int UNSIGNED DEFAULT NULL COMMENT 'Sumber pemasukan dari reservasi tiket',
  `id_admin` int UNSIGNED NOT NULL COMMENT 'Admin yang mencatat pemasukan',
  `jumlah` int UNSIGNED NOT NULL,
  `keterangan` text NOT NULL,
  `tanggal_pemasukan` date NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mencatat semua uang yang masuk';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaki_rombongan`
--

CREATE TABLE `pendaki_rombongan` (
  `id_pendaki` int UNSIGNED NOT NULL,
  `id_reservasi` int UNSIGNED NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `nomor_telepon` varchar(20) NOT NULL,
  `kontak_darurat` varchar(100) NOT NULL COMMENT 'Nama & No HP kontak darurat',
  `url_surat_sehat` varchar(255) DEFAULT NULL COMMENT 'Link/path ke file surat sehat yang di-upload'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Menyimpan data setiap individu dalam satu rombongan';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int UNSIGNED NOT NULL,
  `id_admin` int UNSIGNED NOT NULL COMMENT 'Admin yang mencatat pengeluaran',
  `id_kategori` int UNSIGNED DEFAULT NULL COMMENT 'Kategori pengeluaran',
  `jumlah` int UNSIGNED NOT NULL,
  `keterangan` text NOT NULL,
  `tanggal_pengeluaran` date NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mencatat semua biaya operasional';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int UNSIGNED NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kata_sandi_hash` varchar(255) NOT NULL COMMENT 'Simpan password yang sudah di-hash (e.g., bcrypt)',
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `peran` enum('pendaki','admin') NOT NULL DEFAULT 'pendaki',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Menyimpan data pengguna, baik pendaki maupun admin';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id_pengumuman` int UNSIGNED NOT NULL,
  `id_admin` int UNSIGNED NOT NULL COMMENT 'ID pengguna dengan peran admin yang membuat',
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `telah_terbit` tinyint(1) NOT NULL DEFAULT '0',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Pengumuman yang dibuat oleh admin';

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservasi`
--

CREATE TABLE `reservasi` (
  `id_reservasi` int UNSIGNED NOT NULL,
  `id_pengguna` int UNSIGNED NOT NULL COMMENT 'ID pendaki yang melakukan reservasi',
  `kode_reservasi` varchar(20) NOT NULL COMMENT 'Kode unik untuk referensi (e.g., GUNUNG-20250913-XYZ)',
  `tanggal_pendakian` date NOT NULL COMMENT 'Tanggal pendakian yang dipilih',
  `jumlah_pendaki` tinyint UNSIGNED NOT NULL COMMENT 'Jumlah total anggota dalam rombongan',
  `jumlah_tiket_parkir` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `total_harga` int UNSIGNED NOT NULL,
  `status` enum('menunggu_pembayaran','terkonfirmasi','dibatalkan','selesai') NOT NULL DEFAULT 'menunggu_pembayaran',
  `dipesan_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Menyimpan data transaksi reservasi utama';

--
-- Trigger `reservasi`
--
DELIMITER $$
CREATE TRIGGER `setelah_reservasi_dibuat` AFTER INSERT ON `reservasi` FOR EACH ROW BEGIN
    IF NEW.status != 'dibatalkan' THEN
        UPDATE kuota_harian
        SET kuota_terpesan = kuota_terpesan + NEW.jumlah_pendaki
        WHERE tanggal_kuota = NEW.tanggal_pendakian;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `setelah_reservasi_diperbarui` AFTER UPDATE ON `reservasi` FOR EACH ROW BEGIN
    -- Jika status berubah MENJADI 'dibatalkan'
    IF OLD.status != 'dibatalkan' AND NEW.status = 'dibatalkan' THEN
        UPDATE kuota_harian
        SET kuota_terpesan = kuota_terpesan - OLD.jumlah_pendaki
        WHERE tanggal_kuota = OLD.tanggal_pendakian;
    
    -- Jika status berubah DARI 'dibatalkan' menjadi status aktif
    ELSEIF OLD.status = 'dibatalkan' AND NEW.status != 'dibatalkan' THEN
        UPDATE kuota_harian
        SET kuota_terpesan = kuota_terpesan + NEW.jumlah_pendaki
        WHERE tanggal_kuota = NEW.tanggal_pendakian;
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `checklist_sampah_reservasi`
--
ALTER TABLE `checklist_sampah_reservasi`
  ADD PRIMARY KEY (`id_checklist`),
  ADD UNIQUE KEY `reservasi_item_unik` (`id_reservasi`,`id_item`),
  ADD KEY `id_item` (`id_item`);

--
-- Indeks untuk tabel `item_sampah`
--
ALTER TABLE `item_sampah`
  ADD PRIMARY KEY (`id_item`),
  ADD UNIQUE KEY `nama_item_unik` (`nama_item`);

--
-- Indeks untuk tabel `kategori_pengeluaran`
--
ALTER TABLE `kategori_pengeluaran`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori_unik` (`nama_kategori`);

--
-- Indeks untuk tabel `kuota_harian`
--
ALTER TABLE `kuota_harian`
  ADD PRIMARY KEY (`id_kuota`),
  ADD UNIQUE KEY `tanggal_unik` (`tanggal_kuota`);

--
-- Indeks untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD PRIMARY KEY (`id_pemasukan`),
  ADD KEY `id_reservasi` (`id_reservasi`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `pendaki_rombongan`
--
ALTER TABLE `pendaki_rombongan`
  ADD PRIMARY KEY (`id_pendaki`),
  ADD KEY `id_reservasi` (`id_reservasi`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `id_admin` (`id_admin`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email_unik` (`email`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id_pengumuman`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD UNIQUE KEY `kode_reservasi_unik` (`kode_reservasi`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `checklist_sampah_reservasi`
--
ALTER TABLE `checklist_sampah_reservasi`
  MODIFY `id_checklist` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `item_sampah`
--
ALTER TABLE `item_sampah`
  MODIFY `id_item` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_pengeluaran`
--
ALTER TABLE `kategori_pengeluaran`
  MODIFY `id_kategori` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kuota_harian`
--
ALTER TABLE `kuota_harian`
  MODIFY `id_kuota` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  MODIFY `id_pemasukan` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pendaki_rombongan`
--
ALTER TABLE `pendaki_rombongan`
  MODIFY `id_pendaki` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id_pengumuman` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `checklist_sampah_reservasi`
--
ALTER TABLE `checklist_sampah_reservasi`
  ADD CONSTRAINT `checklist_sampah_reservasi_ibfk_1` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi` (`id_reservasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `checklist_sampah_reservasi_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `item_sampah` (`id_item`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD CONSTRAINT `pemasukan_ibfk_1` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi` (`id_reservasi`) ON DELETE SET NULL,
  ADD CONSTRAINT `pemasukan_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `pendaki_rombongan`
--
ALTER TABLE `pendaki_rombongan`
  ADD CONSTRAINT `pendaki_rombongan_ibfk_1` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi` (`id_reservasi`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `pengeluaran_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_pengeluaran` (`id_kategori`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
e_simaksi`e-simaksi``e-simaksi``e-simaksi``e-simaksi``e-simaksi`e_simaksie_simaksi