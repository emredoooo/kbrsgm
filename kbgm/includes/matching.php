<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php'; // SERTAKAN INI

// Tangkap no kbgm dari URL
$no_kbgm = isset($_GET['no_kbgm']) ? $_GET['no_kbgm'] : '';
if (empty($no_kbgm)) {
    echo "<div class='alert alert-danger m-3'>No KBGM tidak ditemukan.</div>";
    exit;
}

$member = null;
$pasien = null;
$is_matched = false;

// Panggil API untuk mendapatkan data member dan pasien SIK
$matching_response = callApi('matching&no_kbgm=' . urlencode($no_kbgm), 'GET');

if ($matching_response && $matching_response['status'] === 'success') {
    $member = $matching_response['member_data'];
    $pasien = $matching_response['pasien_data_sik'];
    $is_matched = $matching_response['is_matched'];
} else {
    echo "<div class='alert alert-danger m-3'>Gagal memuat data pencocokan: " . ($matching_response['message'] ?? 'Unknown error') . "</div>";
    exit;
}

if (!$member) {
    echo "<div class='alert alert-danger m-3'>Data member tidak ditemukan.</div>";
    exit;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
  <h3>Pencocokan Data Member dengan Database SIK</h3>
  <div class="row mt-4">
    <div class="col-md-6">
      <h5>Data Member (KBGM)</h5>
      <ul class="list-group">
        <li class="list-group-item"><strong>Nama:</strong> <?= htmlspecialchars($member['nama']) ?></li>
        <li class="list-group-item"><strong>NIK:</strong> <?= htmlspecialchars($member['nik']) ?></li>
        <li class="list-group-item"><strong>No. KBGM:</strong> <?= htmlspecialchars($member['no_kbgm']) ?></li>
      </ul>
    </div>

    <div class="col-md-6">
      <h5>Data Pasien (SIK)</h5>
      <?php if ($pasien): ?>
        <ul class="list-group">
          <li class="list-group-item"><strong>Nama:</strong> <?= htmlspecialchars($pasien['nm_pasien']) ?></li>
          <li class="list-group-item"><strong>No. RM:</strong> <?= htmlspecialchars($pasien['no_rkm_medis']) ?></li>
          <li class="list-group-item"><strong>NIK:</strong> <?= htmlspecialchars($pasien['no_ktp']) ?></li>
        </ul>
        <form method="post" class="mt-3">
          <input type="hidden" name="no_rm" value="<?= htmlspecialchars($pasien['no_rkm_medis']) ?>">
          <button type="submit" name="simpan" class="btn btn-success" <?= $is_matched ? 'disabled' : '' ?>>
              <?= $is_matched ? 'Sudah Dicocokkan' : 'Simpan Pencocokan' ?>
          </button>
          <a href="/kbgm-v2/kbgm/member/list_member.php" class="btn btn-secondary">Kembali</a>
        </form>
      <?php else: ?>
        <div class="alert alert-warning">Pasien dengan NIK ini tidak ditemukan di database RS Griya Medika.</div>
        <a href="/kbgm-v2/kbgm/member/list_member.php" class="btn btn-secondary">Kembali</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
// Simpan hasil pencocokan
if (isset($_POST['simpan']) && $pasien) {
    $no_rm_to_save = $_POST['no_rm'];
    $no_kbgm_to_save = $member['no_kbgm'];

    // Panggil API untuk menyimpan pencocokan
    $save_match_response = callApi('matching', 'POST', [
        'no_kbgm' => $no_kbgm_to_save,
        'no_rm' => $no_rm_to_save
    ]);

    if ($save_match_response && $save_match_response['status'] === 'success') {
        echo "<script>alert('Pencocokan berhasil disimpan!'); window.location.href='/kbgm-v2/kbgm/member/list_member.php';</script>";
    } else if ($save_match_response && $save_match_response['status'] === 'error' && ($save_match_response['message'] ?? '') === 'Member already matched.') {
        echo "<script>alert('Data sudah dicocokkan sebelumnya!'); window.location.href='/kbgm-v2/kbgm/member/list_member.php';</script>";
    }
    else {
        echo "<script>alert('Gagal menyimpan pencocokan: " . ($save_match_response['message'] ?? 'Unknown error') . "');</script>";
    }
}
?>