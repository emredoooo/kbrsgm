<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$members_data = [];

// Panggil API untuk mendapatkan daftar member
$api_endpoint = 'members';
if (!empty($keyword)) {
    $api_endpoint .= '&keyword=' . urlencode($keyword);
}
$members_response = callApi($api_endpoint, 'GET');

if ($members_response && $members_response['status'] === 'success') {
    $members_data = $members_response['data'];
} else {
    error_log("Failed to fetch members from API: " . ($members_response['message'] ?? 'Unknown error'));
    // Optionally, display an error message on the page
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Member KBGM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <div class="container-fluid mt-4">
        <h2>Daftar Member KBGM</h2>

        <div class="mb-3">
            <form action="/kbgm-v2/kbgm/member/list_member.php" method="GET" class="d-flex">
                <input class="form-control me-2" type="search" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari No. KBGM / Nama / No. KK" aria-label="Search">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
                <?php if (!empty($keyword)): ?>
                    <a href="/kbgm-v2/kbgm/member/list_member.php" class="btn btn-outline-secondary ms-2">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive w-100">
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>No. KBGM</th>
                        <th>No. RM</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>No. KK</th> <th> Sebagai</th> <th>No. HP</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (!empty($members_data)) {
                        foreach ($members_data as $row) {
                            $no_kbgm = htmlspecialchars($row['no_kbgm']);
                            $no_rm = 'Belum';

                            $no_kk_display = htmlspecialchars($row['no_kk_alias'] ?? '-');
                            $hubungan_kk_display = htmlspecialchars($row['hubungan_kk'] ?? '-');

                            $status_label = '';

                            if ($row['status'] == 1) {
                                $match_response = callApi('matching&no_kbgm=' . urlencode($no_kbgm), 'GET');
                                if ($match_response && $match_response['status'] === 'success' && $match_response['is_matched']) {
                                    $no_rm = htmlspecialchars($match_response['matched_rm']);
                                    $status_label = '<span class="badge bg-success">Sudah</span>';
                                } else {
                                    $status_label = '<span class="badge bg-warning text-dark">Tidak ditemukan</span>';
                                }
                            } else {
                                $status_label = '<a href="/kbgm-v2/kbgm/includes/matching.php?no_kbgm=' . $no_kbgm . '" class="btn btn-warning btn-sm">Cocokkan</a>';
                            }

                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($no_kbgm) . "</td>";
                            echo "<td>" . $no_rm . "</td>"; // Posisi No. RM tetap
                            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nik']) . "</td>";
                            echo "<td>" . $no_kk_display . "</td>"; // Posisi No. KK baru
                            echo "<td>" . $hubungan_kk_display . "</td>"; // Posisi Hubungan KK baru
                            echo "<td>" . htmlspecialchars($row['no_hp']) . "</td>";
                            echo "<td>" . $status_label . "</td>";
                            echo "<td>
                                    <div class='d-flex flex-nowrap'>
                                        <a href='edit_member.php?no_kbgm=" . htmlspecialchars($no_kbgm) . "' class='btn btn-sm btn-primary' title='Edit Member'>
                                            <i class='bi bi-pencil-square'></i>
                                        </a>
                                        <a href='../visits/list_visits.php?no_kbgm=" . htmlspecialchars($no_kbgm) . "' class='btn btn-sm btn-primary ms-1' title='Lihat Kunjungan'>
                                            <i class='bi bi-journal-medical'></i>
                                        </a>
                                        <button class='btn btn-sm btn-info ms-1' onclick='showDownloadOptions(this,
                                            \"" . htmlspecialchars($no_kbgm) . "\",
                                            \"" . htmlspecialchars($row['nama']) . "\",
                                            \"" . htmlspecialchars($row['no_hp']) . "\"
                                        )' title='Cetak / Download'>
                                            <i class='bi bi-download'></i>
                                        </button>
                                    </div>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>Data member tidak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/script.js" defer></script>
</body>
</html>