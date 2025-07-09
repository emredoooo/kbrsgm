<?php
// Tentukan judul halaman
$page_title = 'Dashboard';

// Panggil header. File ini SANGAT PENTING untuk memuat semua file CSS dan struktur utama.
// Pastikan file 'includes/header.php' dan 'includes/config.php' sudah benar.
require_once 'includes/header.php';

// --- BAGIAN PENGAMBILAN DATA API UNTUK SEMENTARA DIMATIKAN ---
// Kita akan fokus memperbaiki tampilan terlebih dahulu.
// Angka di bawah ini kita set ke 0 untuk sementara.
$total_kk = 0;
$total_members = 0;
$total_visits = 0;

?>

<!-- KONTEN UTAMA DASHBOARD -->
<div class="row">
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $total_kk; ?></h3>
                <p>Total Kartu Keluarga</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>kartu_keluarga/list_kk.php" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $total_members; ?></h3>
                <p>Total Anggota</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>member/list_member.php" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $total_visits; ?></h3>
                <p>Total Kunjungan</p>
            </div>
            <div class="icon">
                <i class="fas fa-briefcase-medical"></i>
            </div>
            <a href="#" class="small-box-footer">Info lebih lanjut <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

<?php
// Panggil footer. File ini PENTING untuk memuat file-file JavaScript.
require_once 'includes/footer.php';
?>
