<?php
include __DIR__ . '/../config/supabase.php';

date_default_timezone_set('Asia/Jakarta'); 

/**
 * Fungsi pembantu untuk mengambil data dari Supabase menggunakan fungsi di supabase.php
 * @param string $tableName Nama tabel Supabase.
 * @param string $filters Filter Supabase (misalnya 'tanggal_pendakian=eq.2025-11-05&select=id').
 * @param array $extraHeaders Header tambahan jika diperlukan (misalnya untuk count)
 * @return array Hasil data yang diambil.
 */

function fetchData($tableName, $filters = '', $extraHeaders = []) {
    $response = supabase_request('GET', $tableName . '?' . $filters, null, $extraHeaders);
    
    if (isset($response['error'])) {
        error_log("Gagal fetch data dari tabel {$tableName}: " . ($response['error']['message'] ?? 'Kesalahan tidak diketahui.'));
        return [];
    }
    return $response;
}

//untuk tanggal hari ini, awal bulan ini, dan 7 hari terakhir
$today_date = date('Y-m-d'); 
$first_day_of_month = date('Y-m-01'); 
$last_7_days = date('Y-m-d', strtotime('-7 days'));

// untuk menyimpan data metrik
$reservasi_hari_ini_raw = fetchData('reservasi', 'tanggal_pendakian=eq.' . $today_date . '&select=id_reservasi');
$total_reservasi_hari_ini = count($reservasi_hari_ini_raw);

// untuk menghitung total pendaki hari ini
$pendaki_hari_ini_raw = fetchData('pendaki_rombongan', 'tanggal_registrasi=eq.' . $today_date . '&select=id_pendaki');
$total_pendaki_hari_ini = count($pendaki_hari_ini_raw);

// untuk menghitung sisa kuota hari ini
$kuota_harian_data = fetchData('kuota_harian', 'tanggal_kuota=eq.' . $today_date . '&select=kuota_maksimal,kuota_terpakai');
$sisa_kuota = 0;
if (!empty($kuota_harian_data)) {
    $max = $kuota_harian_data[0]['kuota_maksimal'] ?? 0;
    $terpakai = $kuota_harian_data[0]['kuota_terpakai'] ?? 0;
    $sisa_kuota = $max - $terpakai;
}

// untuk menghitung total pemasukan hari ini
$pemasukan_hari_ini_raw = fetchData('pemasukan', "tanggal_pemasukan=eq." . $today_date . '&select=jumlah');
$total_pemasukan_hari_ini = array_sum(array_column($pemasukan_hari_ini_raw, 'jumlah'));


// untuk mengambil 5 aktivitas reservasi terbaru  
$aktivitas_terbaru_raw = fetchData('reservasi', 'order=created_at.desc&limit=5&select=created_at,id_reservasi,status_reservasi');
$aktivitas_terbaru = [];

foreach ($aktivitas_terbaru_raw as $res) {
    $status = $res['status_reservasi'] ?? 'menunggu_konfirmasi';
    $icon = '';
    $action_text = '';

    if ($status === 'terkonfirmasi') {
        $action_text = "Terkonfirmasi";
        $icon = 'line-md:person-add';
    } elseif ($status === 'menunggu_pembayaran') {
        $action_text = "Menunggu Pembayaran";
        $icon = 'line-md:alert';
    } elseif ($status === 'dibatalkan') {
        $action_text = "Dibatalkan";
        $icon = 'line-md:close-circle';
    } elseif ($status === 'selesai') {
        $action_text = "Selesai";
        $icon = 'line-md:clipboard-check';
    } else {
        $action_text = "Status: " . ucfirst($status);
        $icon = 'line-md:loading-loop';
    }

    $aktivitas_terbaru[] = [
        'waktu' => date('d M Y H:i', strtotime($res['created_at'])), 
        'aksi' => $action_text,
        'kode_booking' => $res['id_reservasi'] ?? 'N/A',
        'icon' => $icon,
        'status' => $status
    ];
}


// untuk menghitung status reservasi bulan ini
$reservations_this_month = fetchData('reservasi', "tanggal_pendakian=gte." . $first_day_of_month . '&select=status_reservasi');

$status_counts = [
    'terkonfirmasi' => 0,
    'menunggu_pembayaran' => 0,
    'dibatalkan' => 0,
    'selesai' => 0,
];

foreach ($reservations_this_month as $res) {
    $status = strtolower($res['status_reservasi'] ?? '');
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}


// untuk data pendapatan dan pengeluaran bulanan serta 7 hari terakhir
$date_range = [];
for ($i = 0; $i < 8; $i++) {
    $date_range[] = date('Y-m-d', strtotime("-$i days"));
}
$date_range = array_reverse($date_range); 

$pendapatan_harian_labels = []; 
$pemasukan_harian_data = array_fill_keys($date_range, 0);
$pengeluaran_harian_data = array_fill_keys($date_range, 0);

foreach ($date_range as $date) {
    $pendapatan_harian_labels[] = date('j M', strtotime($date)); 
}

// untuk proses pemasukan (bulanan dan 7 hari)
$pemasukan_bulanan_raw = fetchData('pemasukan', "tanggal_pemasukan=gte." . $first_day_of_month . '&select=tanggal_pemasukan,jumlah');
$total_pemasukan_bulanan = array_sum(array_column($pemasukan_bulanan_raw, 'jumlah'));

foreach ($pemasukan_bulanan_raw as $data) {
    $date_only = substr($data['tanggal_pemasukan'], 0, 10); 
    if (isset($pemasukan_harian_data[$date_only])) {
        $pemasukan_harian_data[$date_only] += $data['jumlah'];
    }
}

// untuk proses pengeluaran (bulanan dan 7 hari)
$pengeluaran_bulanan_raw = fetchData('pengeluaran', "tanggal_pengeluaran=gte." . $first_day_of_month . '&select=tanggal_pengeluaran,jumlah');
$total_pengeluaran_bulanan = array_sum(array_column($pengeluaran_bulanan_raw, 'jumlah'));

foreach ($pengeluaran_bulanan_raw as $data) {
    $date_only = substr($data['tanggal_pengeluaran'], 0, 10); 
    if (isset($pengeluaran_harian_data[$date_only])) {
        $pengeluaran_harian_data[$date_only] += $data['jumlah'];
    }
}

// untuk menyiapkan data JSON untuk JavaScript

$js_data = [
    'pemasukan' => $total_pemasukan_bulanan,
    'pengeluaran' => $total_pengeluaran_bulanan, 
    'aktivitas' => $aktivitas_terbaru,
    'reservasiStatus' => $status_counts,
    'pendapatanHarian' => [ 
        'labels' => $pendapatan_harian_labels,
        'pemasukan' => array_values($pemasukan_harian_data), 
        'pengeluaran' => array_values($pengeluaran_harian_data)
    ]
];

$json_data = json_encode($js_data);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrasi Pendakian</title>
    <script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --color-primary: #75B368; 
            --color-secondary: #35542E; 
            --color-background: #f3f4f6;
            --color-card-bg: #ffffff; 
            --color-text-dark: #1f2937; 
            --color-text-light: #6b7280; 
            --color-blue: #3b82f6; 
            --color-red: #ef4444; 
            --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
            --transition-duration: 0.3s;
        }

        /* untuk animasi masuk */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulseShadow {
            0% { box-shadow: var(--shadow-light); }
            50% { box-shadow: 0 6px 10px -1px rgba(0, 0, 0, 0.15), 0 3px 6px -2px rgba(0, 0, 0, 0.08); }
            100% { box-shadow: var(--shadow-light); }
        }

        /* untuk reset dasar */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-background);
            padding: 20px;
            color: var(--color-text-dark);
            min-height: 100vh;
        }

        .dashboard-container {
            padding: 20px;
            animation: fadeIn 0.8s ease-out both;
        }

        h1 {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--color-text-dark);
            margin-bottom: 2rem;
        }

        /* Kartu Dasar */
        .card {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-light);
            transition: transform var(--transition-duration), box-shadow var(--transition-duration);
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        /* untuk tampilan grid */
        .grid-top {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .grid-top {
                grid-template-columns: 3fr 2fr; 
            }
        }

        .grid-metrik-2x2 {
            display: grid;
            grid-template-columns: 1fr; 
            gap: 20px;
        }

        @media (min-width: 500px) {
            .grid-metrik-2x2 {
                grid-template-columns: repeat(2, 1fr); 
            }
        }

        .grid-bottom {
            display: grid;
            gap: 20px;
            grid-template-columns: 1fr; 
        }

        @media (min-width: 768px) {
            .grid-bottom {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* untuk tampilan kartu metrik */
        .metric-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            height: 120px; 
            padding: 15px;
        }

        .metric-card svg {
            color: var(--color-primary);
            width: 30px;
            height: 30px;
            margin-bottom: 8px;
            transition: transform var(--transition-duration);
        }

        .metric-card:hover svg {
            transform: scale(1.1) rotate(5deg);
        }

        .metric-card p {
            font-size: 0.875rem;
            color: var(--color-text-light);
            margin: 0;
            font-weight: 500;
            white-space: nowrap; 
        }

        .metric-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-top: 5px;
            animation: fadeIn 0.5s ease-out both; 
        }

        /* untuk diagram keuangan */
        .diagram-keuangan { 
            text-align: center;
            align-items: center;
            justify-content: flex-start;
        }

        .diagram-keuangan h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            color: var(--color-secondary); 
            padding-top: 0; 
            margin-top: 0;
            padding-left: 0; 
            margin-right: 0;
            box-sizing: border-box; 
        }

        /* untuk detail status reservasi */
        .color-dot { 
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: var(--color-text-dark);
        }

        .detail-text {
            font-weight: 500;
        }

        .detail-value {
            font-weight: 700;
            margin-left: 5px;
        }

        #keuanganDonutChart {
            max-height: 150px;
        }

        /* untuk aktivitas terbaru dan grafik pendapatan mingguan */
        .aktivitas-terakhir h3, .grafik-pendapatan-mingguan h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            color: var(--color-primary);
        }

        /* Aktivitas Terbaru */
        #daftar-aktivitas {
            list-style: none;
            padding: 0;
            overflow-y: auto; 
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px dashed #e5e7eb;
            transition: background-color 0.2s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background-color: #f9fafb;
        }
        
        .activity-item .iconify {
            font-size: 1.25rem !important;
            flex-shrink: 0;
            margin-right: 12px;
            margin-top: 2px;
        }
        
        .activity-item p {
            line-height: 1.4;
        }
        
        /* Grafik Pendapatan Mingguan */
        .chart-area {
            position: relative;
            height: 250px; 
            margin-bottom: 10px;
        }

        #pendapatanMingguanChart {
            height: 100% !important; 
        }

        .catatan-pendapatan {
            font-size: 0.875rem;
            color: var(--color-text-light);
            text-align: center;
            padding-top: 10px;
        }
        
        .catatan-pendapatan #minggu-tertinggi-pendapatan {
            font-weight: 600;
            color: var(--color-text-dark);
        }
        
        .catatan-pendapatan .iconify {
            display: inline-block;
            animation: pulseShadow 2s infinite ease-in-out; 
        }
        
    </style>
</head>
<body>
    
    <div id="dashboard-container" class="dashboard-container">
        <div class="grid-top">

            <div class="grid-metrik-2x2">
                
                <div class="metric-card card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M1 11.59A3.59 3.59 0 0 1 4.59 8h11.75a3.59 3.59 0 0 1 3.59 3.59v3.727c.543 1.424 1.743 2.317 2.939 2.87a9.3 9.3 0 0 0 2.097.675l.127.022l.03.005h.002l.025.003l.026.005a7.15 7.15 0 0 1 5.756 6.033h.028v.23a7 7 0 0 1 .04.782v.868q0 .248-.04.486v.141c.078.415.07.886-.122 1.375a3.05 3.05 0 0 1-2.579 2.162h-.002a3.03 3.03 0 0 1-2.716-1.149a3.03 3.03 0 0 1-4.779-.001a3.03 3.03 0 0 1-2.392 1.166a3.02 3.02 0 0 1-2.388-1.166a3.03 3.03 0 0 1-2.392 1.166a3.02 3.02 0 0 1-2.388-1.166a3.03 3.03 0 0 1-4.78 0A3.031 3.031 0 0 1 1 27.96zm27.905 13.34a5.15 5.15 0 0 0-4.08-4.064l-.046-.006l-.18-.032a11.3 11.3 0 0 1-2.57-.826c-1.309-.605-2.868-1.66-3.74-3.447l-2.062.035a1 1 0 0 1-.034-2l1.737-.029V13.34l-1.707.02a1 1 0 1 1-.025-1.999l1.712-.021A1.59 1.59 0 0 0 16.34 10h-4.98v1.91a2.43 2.43 0 0 1-2.43 2.43H3v10.59z"/></svg>
                    <p>Total Reservasi Hari Ini</p> 
                    <h2 id="total-reservasi-hari-ini"><?= number_format($total_reservasi_hari_ini, 0, ',', '.') ?></h2> 
                </div>
                
                <div class="metric-card card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m7 23l3.075-15.55q.15-.725.675-1.088T11.85 6t1.063.25t.787.75l1 1.6q.45.725 1.163 1.313t1.637.862V9H19v14h-1.5V12.85q-1.2-.275-2.225-.875T13.5 10.5l-.6 3l2.1 2V23h-2v-6l-2.1-2l-1.8 8zm.425-9.875l-2.125-.4q-.4-.075-.625-.413t-.15-.762l.75-3.925q.15-.8.85-1.263t1.5-.312l1.15.225zM13.5 5.5q-.825 0-1.412-.587T11.5 3.5t.588-1.412T13.5 1.5t1.413.588T15.5 3.5t-.587 1.413T13.5 5.5"/></svg>
                    <p>Total Pendaki Hari Ini</p>
                    <h2 id="total-pendaki-hari-ini"><?= number_format($total_pendaki_hari_ini, 0, ',', '.') ?></h2> 
                </div>

                <div class="metric-card card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M10.787 5.002C13.649 4.756 18.053 4.5 24 4.5c.647 0 1.644.003 2.537.009c.733.004 1.389.137 1.94.397c2.486 1.175 4.787 2.885 6.764 4.87c1.99 1.997 3.805 4.355 4.899 6.842q.308.705.322 1.686c.024 1.743.038 3.949.038 5.696c0 7.744-.273 12.997-.526 16.201c-.121 1.53-1.255 2.668-2.76 2.797c-2.863.246-7.267.502-13.214.502s-10.351-.256-13.213-.502c-1.506-.129-2.64-1.267-2.76-2.797C7.772 36.997 7.5 31.744 7.5 24s.273-12.997.526-16.201c.121-1.53 1.255-2.668 2.76-2.797M24 .5c-6.062 0-10.58.26-13.556.516c-3.469.298-6.131 3.002-6.405 6.468C3.777 10.804 3.5 16.164 3.5 24c0 7.837.277 13.196.539 16.516c.274 3.466 2.936 6.17 6.405 6.468c2.976.255 7.494.516 13.556.516s10.58-.26 13.556-.516c3.469-.299 6.131-3.002 6.405-6.468c.262-3.32.539-8.68.539-16.516c0-1.764-.014-3.988-.039-5.752c-.014-1.038-.185-2.16-.66-3.24c-1.355-3.081-3.518-5.839-5.726-8.056c-2.25-2.258-4.922-4.26-7.89-5.662c-1.183-.56-2.437-.774-3.622-.781A442 442 0 0 0 24 .5m3.58 12.037a29 29 0 0 1 6.557-.519a1.39 1.39 0 0 1 1.345 1.345a29 29 0 0 1-.52 6.557c-.232 1.176-1.652 1.518-2.455.715l-1.096-1.095c-1.28 1.151-3.084 2.894-4.501 4.726c-1.465 1.894-4.689 1.762-5.735-.705c-.555-1.309-1.313-3.036-2.018-4.451c-1.034.83-2.537 2.334-4.274 4.99a2.25 2.25 0 1 1-3.766-2.465c2.758-4.215 5.215-6.197 6.763-7.109c1.788-1.054 3.8-.214 4.646 1.316c.665 1.203 1.405 2.792 2.033 4.209a47 47 0 0 1 3.666-3.698l-1.36-1.36c-.804-.803-.46-2.223.715-2.456M13 28.75a2.25 2.25 0 0 0 0 4.5h22a2.25 2.25 0 1 0 0-4.5zm0 7a2.25 2.25 0 0 0 0 4.5h22a2.25 2.25 0 1 0 0-4.5z" clip-rule="evenodd"/></svg>
                    <p>Kuota Hari Ini (Sisa)</p>
                    <h2 id="sisa-kuota"><?= number_format($sisa_kuota, 0, ',', '.') ?></h2> 
                </div>

                <div class="metric-card card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M12 21a9 9 0 1 1 0-18a9 9 0 0 1 0 18m.5-8.5V9.5h-1v4l3.5 3.5l.7-.7l-3.2-3.3"/></svg>
                    <p>Total Pemasukan Hari Ini</p>
                    <h2 id="total-pemasukan-hari-ini">Rp <?= number_format($total_pemasukan_hari_ini, 0, ',', '.') ?></h2> 
                </div>
            </div>

            <div class="diagram-keuangan card">
            <h3 style="display: flex; align-items: center;">
                <span class="iconify iconify-md" data-icon="ant-design:pie-chart-filled" style="color: var(--color-secondary); margin-right: 8px;"></span>
                Diagram Status Reservasi Bulan Ini 
            </h3>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;">
                    <div style="width: 100%; max-width: 200px; height: auto;">
                           <canvas id="keuanganDonutChart"></canvas>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; padding-top: 16px;">
                        <div class="detail-row">
                            <span style="background-color: var(--color-primary);" class="color-dot"></span>
                            <p class="detail-text">Terkonfirmasi: <span id="data-terkonfirmasi" class="detail-value"><?= number_format($status_counts['terkonfirmasi'] ?? 0, 0, ',', '.') ?></span></p>
                        </div>
                        <div class="detail-row">
                            <span style="background-color: #f59e0b;" class="color-dot"></span>
                            <p class="detail-text">Menunggu Pembayaran: <span id="data-menunggu" class="detail-value"><?= number_format($status_counts['menunggu_pembayaran'] ?? 0, 0, ',', '.') ?></span></p>
                        </div>
                           <div class="detail-row">
                            <span style="background-color: #3b82f6;" class="color-dot"></span>
                            <p class="detail-text">Selesai: <span id="data-selesai" class="detail-value"><?= number_format($status_counts['selesai'] ?? 0, 0, ',', '.') ?></span></p>
                        </div>
                           <div class="detail-row">
                            <span style="background-color: var(--color-red);" class="color-dot"></span>
                            <p class="detail-text">Dibatalkan: <span id="data-dibatalkan" class="detail-value"><?= number_format($status_counts['dibatalkan'] ?? 0, 0, ',', '.') ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr style="border-top: 1px solid #e5e7eb; margin: 20px 0;">

        <div class="grid-bottom">

            <div class="aktivitas-terakhir card">
                <h3 style="display: flex; align-items: center;">
                    <span class="iconify iconify-md" data-icon="line-md:bell-loop" style="color: var(--color-primary); margin-right: 8px;"></span>
                    Aktivitas Terbaru (Reservasi)
                </h3>
                <ul id="daftar-aktivitas">
                    <?php if (!empty($aktivitas_terbaru)): ?>
                        <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                            <li class="activity-item">
                                <span class="iconify" data-icon="<?= $aktivitas['icon'] ?>" style="font-size: 1.125rem; color: var(--color-primary);"></span>
                                <div>
                                    <p style="font-size: 0.875rem; font-weight: 600;">
                                        **<?= $aktivitas['aksi'] ?>** (Kode Booking: <?= $aktivitas['kode_booking'] ?>)
                                    </p>
                                    <span style="font-size: 0.75rem; color: var(--color-text-light); display: block; margin-top: 2px;">
                                        Waktu Pengguna Aksi: <?= $aktivitas['waktu'] ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #9ca3af; padding: 12px;">Tidak ada aktivitas reservasi terbaru.</p>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="grafik-pendapatan-mingguan card">
                <h3 style="display: flex; align-items: center;">
                    <span class="iconify iconify-md" data-icon="mdi:chart-bar" style="color: var(--color-secondary); margin-right: 8px;"></span>
                    Grafik Pemasukan dan Pengeluaran 7 Hari Terakhir
                </h3>
                <div class="chart-area">
                    <canvas id="pendapatanMingguanChart"></canvas>
                </div>
                <p class="catatan-pendapatan">
                    Total Pemasukan Bulan Ini: <span style="font-weight: 600; color: var(--color-blue);">Rp <?= number_format($js_data['pemasukan'], 0, ',', '.') ?></span> 
                    | Total Pengeluaran: <span style="font-weight: 600; color: var(--color-red);">Rp <?= number_format($js_data['pengeluaran'], 0, ',', '.') ?></span>
                </p>
            </div>

        </div>

    </div>

    <script>
        // Data PHP disuntikkan ke variabel JavaScript
        const dataDashboard = <?= $json_data ?>;

        // Fungsi untuk me-render daftar aktivitas terakhir
        function renderAktivitas() {
            const ul = document.getElementById('daftar-aktivitas');
            
            if (ul.children.length > 0) {
                const items = ul.getElementsByClassName('activity-item');
                dataDashboard.aktivitas.forEach((item, index) => {
                    const li = items[index];
                    if (li) {
                        const iconSpan = li.querySelector('.iconify');
                        const status = item.status;
                        
                        let statusColor = '#6b7280'; 
                        if (status && status.toLowerCase().includes('terkonfirmasi')) {
                            statusColor = 'var(--color-primary)'; 
                        } else if (status && status.toLowerCase().includes('menunggu')) {
                            statusColor = '#f59e0b'; 
                        } else if (status && status.toLowerCase().includes('dibatalkan')) {
                            statusColor = 'var(--color-red)'; 
                        } else if (status && status.toLowerCase().includes('selesai')) {
                            statusColor = '#3b82f6';  
                        }

                        if (iconSpan) iconSpan.style.color = statusColor;
                        const pElement = li.querySelector('p');
                        const timeSpan = li.querySelector('span:last-child');
                        if (pElement) {
                            const actionTextHtml = item.aksi.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                            pElement.innerHTML = actionTextHtml + ` (Kode Booking: ${item.kode_booking})`;
                        }
                        if (timeSpan) {
                            timeSpan.innerHTML = `Waktu Pengguna Aksi: ${item.waktu}`;
                        }
                    }
                });
            }
        }

        // Fungsi untuk me-render grafik DONUT (Status Reservasi)
        function renderReservasiStatusDonutChart() {
            const ctx = document.getElementById('keuanganDonutChart').getContext('2d');
            const dataStatus = dataDashboard.reservasiStatus;
            
            const formatAngka = (value) => `${value.toLocaleString('id-ID', { maximumFractionDigits: 0 })}`;

            // Data untuk Chart
            const labels = ['Terkonfirmasi', 'Menunggu Pembayaran', 'Selesai', 'Dibatalkan'];
            const dataValues = [
                dataStatus.terkonfirmasi, 
                dataStatus.menunggu_pembayaran,
                dataStatus.selesai,
                dataStatus.dibatalkan
            ];
            
            // Warna disesuaikan dengan status
            const colors = [
                getComputedStyle(document.documentElement).getPropertyValue('--color-primary'), // Hijau (Terkonfirmasi)
                '#f59e0b', // Kuning (Menunggu)
                '#3b82f6', // Biru (Selesai)
                getComputedStyle(document.documentElement).getPropertyValue('--color-red') // Merah (Dibatalkan)
            ];

            new Chart(ctx, {
                type: 'doughnut', 
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: colors, 
                        hoverBackgroundColor: colors.map(c => c + 'AA'),
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%', 
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    return `${label}: ${formatAngka(value)} Reservasi`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Fungsi untuk me-render grafik pendapatan harian (Line Chart)
        function renderPendapatanChart() {
            const ctx = document.getElementById('pendapatanMingguanChart').getContext('2d');
            const data = dataDashboard.pendapatanHarian; 

            const formatRupiah = (value) => `Rp ${value.toLocaleString('id-ID', { maximumFractionDigits: 0 })}`;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Pemasukan (Rp)',
                        data: data.pemasukan,
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-blue'), // Biru untuk Pemasukan
                        backgroundColor: 'rgba(59, 130, 246, 0.2)', 
                        fill: true,
                        tension: 0.4, 
                        pointBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-blue'),
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-blue'),
                        pointHoverBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Pengeluaran (Rp)',
                        data: data.pengeluaran,
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-red'), // Merah untuk Pengeluaran
                        backgroundColor: 'rgba(239, 68, 68, 0.2)', 
                        fill: false, 
                        tension: 0.4, 
                        pointBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-red'),
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-red'),
                        pointHoverBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    return `${label}: ${formatRupiah(context.raw)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    if (value >= 1000000) return `Rp ${value/1000000} Juta`;
                                    return formatRupiah(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        window.onload = function() {
            renderAktivitas();
            renderReservasiStatusDonutChart(); 
            renderPendapatanChart();
        };
    </script>
</body>
</html>