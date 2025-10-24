<?php
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'kuota_pendakian':
        $content = '../admin/kuota_pendakian/kuota_pendakian.php';
        break;
    case 'pembukuan':
        $content = '../admin/pembukuan/pembukuan.php';
        break;
    case 'user':
        $content = '../admin/management_user/management_user.php';
        break;
    case 'login':
        $content = '../auth/login.php';
        break;
    case 'reservasi':
        $content = '../admin/reservasi/reservasi.php';
        break;
    case 'pengumuman':
        $content = '../admin/pengumuman/pengumuman.php';
        break;
    case 'manage_pendakian':
        $content = '../admin/manage_pendakian/manage_pendakian.php';
        break;
    case 'dashboard':
    default:
        $content = 'dashboard.php'; 
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="/simaksi/assets/images/favicon.ico">
</head>

<body>
    <div class="container">
        <div class="navigation active">
            <ul>
                <li>
                    <a href="#">
                        <img src="../assets/images/logo2.png" class="logo-img">
                    </a>
                </li>

                <li data-menu-name="Dashboard" class="<?php echo $page == 'dashboard' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=dashboard">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M13 9V3h8v6zM3 13V3h8v10zm10 8V11h8v10zM3 21v-6h8v6z"/></svg>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li data-menu-name="Reservasi & Validasi" class="<?php echo $page == 'reservasi' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=reservasi">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M14.008 19.003L14.014 17a1.001 1.001 0 0 1 2.005 0v1.977c0 .481 0 .722.154.87c.155.147.39.137.863.117c1.863-.079 3.008-.33 3.814-1.136c.81-.806 1.061-1.951 1.14-3.817c.015-.37.023-.556-.046-.679c-.07-.123-.345-.277-.897-.586a1.999 1.999 0 0 1 0-3.492c.552-.308.828-.463.897-.586s.061-.308.045-.679c-.078-1.866-.33-3.01-1.139-3.817c-.877-.876-2.155-1.097-4.322-1.153a.497.497 0 0 0-.51.497V7a1.001 1.001 0 0 1-2.005 0l-.007-2.501a.5.5 0 0 0-.5-.499H9.994c-3.78 0-5.67 0-6.845 1.172c-.81.806-1.061 1.951-1.14 3.817c-.015.37-.023.556.046.679c.07.123.345.278.897.586a1.999 1.999 0 0 1 0 3.492c-.552.309-.828.463-.897.586s-.061.308-.045.678c.078 1.867.33 3.012 1.139 3.818C4.324 20 6.214 20 9.995 20h3.01c.472 0 .707 0 .854-.146s.148-.38.149-.851M16.018 13v-2a1.001 1.001 0 0 0-2.005 0v2a1.002 1.002 0 0 0 2.006 0" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="title">Reservasi & Validasi</span>
                    </a>
                </li>

                <li data-menu-name="Kuota Pendakian" class="<?php echo $page == 'kuota_pendakian' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=kuota_pendakian">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path fill="currentColor" d="M256.22 18.375c-132.32 0-239.783 107.43-239.783 239.75S123.9 497.905 256.22 497.905S496 390.446 496 258.126S388.54 18.375 256.22 18.375m0 17.875c102.773 0 189.092 69.664 214.374 164.406l-79.313-81.47l-6.967-7.155l-6.688 7.47l-77.22 86.438a1913 1913 0 0 0-34.467-30.063l-6.563-5.625l-6.125 6.156a3510 3510 0 0 0-55.438 57.094l-76.437-83.375l-6.875-7.5l-6.875 7.5l-71.188 77.313C51.364 119.34 143.983 36.25 256.22 36.25m102.25 147.28l-3.845 35.376l21.563-32l10.75 16.688l9.968-8.47l27.188 26.814L417 187.344l19.5 5.062l39.188 40.25l.843-.812a224 224 0 0 1 1.564 26.28c0 37.033-9.06 71.917-25.063 102.595c-46.25-53.48-92.512-100.116-138.75-142.283l11-12.312l33.19-22.594zm-220.16 22.75l26.438 18.782l20.22 22.032c-39.47 42.024-78.63 85.836-115.94 130.344c-21.98-34.443-34.718-75.38-34.718-119.313v-.78l16.25-17.658L87.81 219.5l-17.187 54.063l41.813-51.22l27.312 32.72l-1.438-48.782zm141.375 61.657l53.157 60.938l-7.688-54.563L386.312 315a1632 1632 0 0 1 56.75 62.78l.188-.186C403.853 439.216 334.868 480.03 256.22 480.03c-71.76 0-135.483-33.992-176.033-86.75c19.135-22.91 38.775-45.645 58.72-68.06l56.155-33.814l-29.312 76.75l61.53-73.375l6.25 32.19l19.532-36.783l47.844 69.5l-21.22-91.75z"/></svg>
                        </span>
                        <span class="title">Kuota Pendakian</span>
                    </a>
                </li>

                <li data-menu-name="Pengumuman" class="<?php echo $page == 'pengumuman' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=pengumuman">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M25.5.5a2 2 0 0 0-2 2v3a2 2 0 1 0 4 0v-3a2 2 0 0 0-2-2m-17 21a2 2 0 1 1 0-4h3a2 2 0 1 1 0 4zm34 0a2 2 0 1 0 0-4h-3a2 2 0 1 0 0 4zM12.064 8.895a2 2 0 0 1 2.829-2.828l2.121 2.12a2 2 0 1 1-2.828 2.83zm26.87-2.828a2 2 0 0 1 0 2.828l-2.122 2.121a2 2 0 1 1-2.828-2.828l2.121-2.121a2 2 0 0 1 2.829 0M25.049 10.04a1.5 1.5 0 0 1 2.119.095l.003.003l.003.004l.01.01l.03.034l.1.117q.13.15.368.447c.319.4.785 1.008 1.398 1.874c1.225 1.73 3.042 4.491 5.446 8.656c2.405 4.164 3.887 7.117 4.773 9.045a41 41 0 0 1 .924 2.147a18 18 0 0 1 .254.687l.014.043l.005.014l.002.007v.002a1.5 1.5 0 0 1-2.85.935c-1.245.418-2.51.83-3.782 1.233a72 72 0 0 0-1.257-2.881a98 98 0 0 0-4.047-7.789a98 98 0 0 0-4.72-7.399a72 72 0 0 0-1.865-2.526c.985-.9 1.974-1.79 2.959-2.659a1.5 1.5 0 0 1 .113-2.099m5.81 25.93q.068.159.133.318c-2.04.625-4.07 1.224-6.032 1.786l.277 1.032a6.59 6.59 0 1 1-12.733 3.412l-.266-.992c-.98.25-1.82.463-2.49.63c-1.649.412-3.445-.144-4.503-1.582c-.38-.518-.805-1.13-1.147-1.724a19 19 0 0 1-.92-1.855c-.716-1.635-.3-3.469.882-4.691c2.61-2.7 8.892-9.116 15.704-15.464l.207.27c.335.442.822 1.098 1.419 1.944a95 95 0 0 1 4.574 7.17a95 95 0 0 1 3.922 7.546c.435.94.76 1.69.974 2.2Zm-14.75 4.546l.259.966a2.59 2.59 0 1 0 5.005-1.34l-.263-.982c-1.787.495-3.472.95-5.001 1.356" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="title">Pengumuman</span>
                    </a>
                </li>

                <li data-menu-name="Keuangan" class="<?php echo $page == 'pembukuan' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=pembukuan">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 1536"><path fill="currentColor" d="M2048 1408v128H0V0h128v1408zM1920 160v435q0 21-19.5 29.5T1865 617l-121-121l-633 633q-10 10-23 10t-23-10L832 896l-416 416l-192-192l585-585q10-10 23-10t23 10l233 233l464-464l-121-121q-16-16-7.5-35.5T1453 128h435q14 0 23 9t9 23"/></svg>
                        </span>
                        <span class="title">Keuangan</span>
                    </a>
                </li>

                <li data-menu-name="Manage Pendakian" class="<?php echo $page == 'manage_pendakian' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=manage_pendakian">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M22 5v2h-3v3h-2V7h-3V5h3V2h2v3Zm-3 14H5V5h6V3H5a2.006 2.006 0 0 0-2 2v14a2.006 2.006 0 0 0 2 2h14a2.006 2.006 0 0 0 2-2v-6h-2Z"/></svg>
                        </span>
                        <span class="title">Manage Pendakian</span>
                    </a>
                </li>

                <li data-menu-name="Management User" class="<?php echo $page == 'user' ? 'hovered' : ''; ?>">
                    <a href="index.php?page=user">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 28 28"><path fill="currentColor" d="M9.5 13a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9m14-3.5a3.5 3.5 0 1 1-7 0a3.5 3.5 0 0 1 7 0M2 17.25A2.25 2.25 0 0 1 4.25 15h10.5q.298.001.573.074A7.48 7.48 0 0 0 13 20.5c0 .665.086 1.309.249 1.922c-.975.355-2.203.578-3.749.578C2 23 2 17.75 2 17.75zm14.796-.552a2 2 0 0 1-1.441 2.497l-1.024.253a6.8 6.8 0 0 0 .008 2.152l.976.235a2 2 0 0 1 1.45 2.51l-.324 1.098c.518.46 1.11.835 1.753 1.1l.843-.886a2 2 0 0 1 2.899 0l.85.895a6.2 6.2 0 0 0 1.751-1.09l-.335-1.16a2 2 0 0 1 1.441-2.495l1.026-.254a6.8 6.8 0 0 0-.008-2.152l-.977-.235a2 2 0 0 1-1.45-2.51l.324-1.1a6.2 6.2 0 0 0-1.753-1.1l-.843.888a2 2 0 0 1-2.9 0l-.849-.895a6.2 6.2 0 0 0-1.751 1.09zM20.5 22a1.5 1.5 0 1 1 0-3a1.5 1.5 0 0 1 0 3"/></svg>
                        </span>
                        <span class="title">Management User</span>
                    </a>
                </li>
                
         </ul>
        </div>
        
                <div class="main active">
             <div class="topbar">
                 <div class="topbar-left">
                 <span id="active-menu-title" class="topbar-menu-name">
                        <?php 
                            $menu_titles = [
                                'dashboard' => 'Dashboard',
                                'reservasi' => 'Reservasi & Validasi',
                                'kuota_pendakian' => 'Kuota Pendakian',
                                'pengumuman' => 'Pengumuman',
                                'pembukuan' => 'Keuangan',
                                'manage_pendakian' => 'Manage Pendakian',
                                'user' => 'Management User'
                            ];
                            $current_title = isset($menu_titles[$page]) ? $menu_titles[$page] : 'Dashboard';
                            echo $current_title;
                        ?>
                    </span>
            </div>
        <div class="topbar-right">
     <div class="topbar-info">
    <span id="topbar-greeting" style="font-size: 0.95em; font-weight: 600; color: #35542E;">Selamat Pagi</span>
    <span id="current-date" style="margin-top: 5px;">Jumat, 24 Oktober 2025</span>
    <span id="current-time">14:13:10</span>
    </div>
         <a href="index.php?page=user_profile" class="topbar-user">
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4"/></svg>
         <span>zawawi</span>
         </a> 

    <a href="../auth/login.php" class="btn-logout">
     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 14 14"><path fill="currentColor" fill-rule="evenodd" d="M2.5.351a40.5 40.5 0 0 1 5.74 0c1.136.081 2.072.874 2.264 1.932a2.25 2.25 0 0 0-2.108 2.28H4.754a2.25 2.25 0 0 0 0 4.5h3.642a2.25 2.25 0 0 0 2.145 2.281l-.004.085c-.06 1.2-1.06 2.132-2.296 2.22a40.5 40.5 0 0 1-5.742 0C1.263 13.561.263 12.63.203 11.43a91 91 0 0 1 0-8.859C.263 1.372 1.263.439 2.5.351m7.356 5.462L9.661 4.7a1 1 0 0 1 1.432-1.067c1.107.553 2.178 1.624 2.731 2.731a1 1 0 0 1 0 .895c-.553 1.107-1.624 2.178-2.731 2.731A1 1 0 0 1 9.66 8.924l.195-1.111H4.754a1 1 0 1 1 0-2z" clip-rule="evenodd"/></svg>
     <span>Logout</span>
     </a>
                </div>
             </div>
            <div class="content-area"> 
    <?php include $content; ?>
    </div>
     </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>