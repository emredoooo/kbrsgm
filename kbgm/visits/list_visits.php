<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php'; // SERTAKAN INI

$no_kbgm = isset($_GET['no_kbgm']) ? $_GET['no_kbgm'] : '';
if (empty($no_kbgm)) {
    echo "<div class='alert alert-danger m-3'>No KBGM tidak ditemukan.</div>";
    exit;
}

$member = null;
$no_rm = null;
$visits_sik_data = [];

// Ambil data member dari API
$member_response = callApi('members&id=' . urlencode($no_kbgm), 'GET');
if ($member_response && $member_response['status'] === 'success' && isset($member_response['data'])) {
    $member = $member_response['data'];
} else {
    echo "<div class='alert alert-danger m-3'>Data member tidak ditemukan atau API Error: " . ($member_response['message'] ?? 'Unknown error') . "</div>";
    exit;
}

// Ambil data kunjungan dari API
$visits_response = callApi('visits&no_kbgm=' . urlencode($no_kbgm), 'GET');
if ($visits_response && $visits_response['status'] === 'success') {
    $visits_sik_data = $visits_response['data'];
} else {
    error_log("Failed to fetch visits from API: " . ($visits_response['message'] ?? 'Unknown error'));
    // Optionally, display an error message on the page
}

// Ambil no_rm untuk ditampilkan
$match_response = callApi('matching&no_kbgm=' . urlencode($no_kbgm), 'GET');
if ($match_response && $match_response['status'] === 'success' && $match_response['is_matched']) {
    $no_rm = $match_response['matched_rm'];
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="script.js" defer></script>

<div class="container mt-4">
<div class="d-flex align-items-center justify-content-start">
    <a href="../member/list_member.php" class="btn btn-primary">Kembali</a>
    <h3>|  Daftar Kunjungan <?= htmlspecialchars($member['nama']) ?> (<?= htmlspecialchars($member['no_kbgm']) ?>/<?= htmlspecialchars($no_rm ?? '-') ?>)</h3>
</div>

<table class="table table-striped mt-3">
    <thead>
        <tr>
            <th>No.</th>
            <th>Tanggal Kunjungan</th>
            <th>Dokter</th>
            <th>Diagnosa</th>
            <th>Edukasi</th>
            <th>Lama Kunjungan (jam)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($visits_sik_data)): ?>
            <?php $no = 1; ?>
            <?php foreach ($visits_sik_data as $visit_sik): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($visit_sik['tanggal_kunjungan']) ?></td>
                    <td><?= htmlspecialchars($visit_sik['nama_dokter']) ?></td>
                    <td><?= htmlspecialchars($visit_sik['diagnosa'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($visit_sik['edukasi'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($visit_sik['lama_kunjungan'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data kunjungan untuk member ini di RS Griya Medika DD.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php
// Tidak perlu lagi menutup koneksi mysqli karena sudah menggunakan PDO di API
?>