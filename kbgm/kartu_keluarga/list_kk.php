<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$kk_data = [];

// Panggil API untuk mendapatkan daftar Kartu Keluarga
$api_endpoint = 'kartu_keluarga';
if (!empty($keyword)) {
    // API handleKartuKeluarga (GET) saat ini tidak memiliki keyword, perlu modifikasi API jika ingin pencarian
    // Untuk saat ini, kita akan melewati keyword ke API jika ada, meskipun API-nya belum mendukung.
    // Jika Anda ingin pencarian, API handler 'kartu_keluarga' di API/index.php perlu diupdate.
    $api_endpoint .= '&keyword=' . urlencode($keyword);
}
$kk_response = callApi($api_endpoint, 'GET');

if ($kk_response && $kk_response['status'] === 'success') {
    $kk_data = $kk_response['data'];
} else {
    error_log("Failed to fetch Kartu Keluarga from API: " . ($kk_response['message'] ?? 'Unknown error'));
    // Optionally, display an error message on the page
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kartu Keluarga | KBGM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <div class="container-fluid mt-4">
        <h2>Daftar Kartu Keluarga</h2>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <form action="/kbgm-v2/kbgm/kartu_keluarga/list_kk.php" method="GET" class="d-flex w-50 me-2">
                <input class="form-control me-2" type="search" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari No. KK / No. KBGM Kepala Keluarga" aria-label="Search">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
                <?php if (!empty($keyword)): ?>
                    <a href="/kbgm-v2/kbgm/kartu_keluarga/list_kk.php" class="btn btn-outline-secondary ms-2">Reset</a>
                <?php endif; ?>
            </form>
            <a href="manage_kk.php" class="btn btn-success">Tambah Kartu Keluarga Baru</a>
        </div>

        <div class="table-responsive w-100">
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>No. KK</th>
                        <th>Kepala Keluarga (No. KBGM)</th>
                        <th>Alamat KK</th>
                        <th>RT/RW</th>
                        <th>Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Kota/Kab</th>
                        <th>Provinsi</th>
                        <th>Kode Pos</th>
                        <th>Tgl. Pembuatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (!empty($kk_data)) {
                        foreach ($kk_data as $row) {
                            $no_kk = htmlspecialchars($row['no_kk']);
                            $kepala_keluarga_no_kbgm = htmlspecialchars($row['kepala_keluarga_no_kbgm'] ?? '-');
                            $alamat_kk = htmlspecialchars($row['alamat_kk'] ?? '-');
                            $rt_rw = htmlspecialchars(($row['rt_kk'] ?? '-') . '/' . ($row['rw_kk'] ?? '-'));
                            $kelurahan_kk = htmlspecialchars($row['kelurahan_kk'] ?? '-');
                            $kecamatan_kk = htmlspecialchars($row['kecamatan_kk'] ?? '-');
                            $kota_kab_kk = htmlspecialchars($row['kota_kab_kk'] ?? '-');
                            $provinsi_kk = htmlspecialchars($row['provinsi_kk'] ?? '-');
                            $kode_pos_kk = htmlspecialchars($row['kode_pos_kk'] ?? '-');
                            $tanggal_pembuatan_kk = htmlspecialchars($row['tanggal_pembuatan_kk'] ?? '-');

                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . $no_kk . "</td>";
                            echo "<td>" . $kepala_keluarga_no_kbgm . "</td>";
                            echo "<td>" . $alamat_kk . "</td>";
                            echo "<td>" . $rt_rw . "</td>";
                            echo "<td>" . $kelurahan_kk . "</td>";
                            echo "<td>" . $kecamatan_kk . "</td>";
                            echo "<td>" . $kota_kab_kk . "</td>";
                            echo "<td>" . $provinsi_kk . "</td>";
                            echo "<td>" . $kode_pos_kk . "</td>";
                            echo "<td>" . $tanggal_pembuatan_kk . "</td>";
                            echo "<td>
                                    <div class='d-flex flex-nowrap'>
                                        <a href='manage_kk.php?no_kk=" . urlencode($no_kk) . "' class='btn btn-sm btn-primary' title='Edit Kartu Keluarga'>
                                            <i class='bi bi-pencil-square'></i>
                                        </a>
                                        <a href='../member/list_member.php?keyword=" . urlencode($no_kk) . "' class='btn btn-sm btn-info ms-1' title='Lihat Anggota Keluarga'>
                                            <i class='bi bi-people-fill'></i>
                                        </a>
                                        </div>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center'>Data Kartu Keluarga tidak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </body>
</html>