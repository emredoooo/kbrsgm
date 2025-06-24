<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php'; // SERTAKAN INI

// Tangkap no kbgm dari URL
$no_kbgm_param = isset($_GET['no_kbgm']) ? $_GET['no_kbgm'] : '';
if (empty($no_kbgm_param)) {
    echo "<div class='alert alert-danger m-3'>No KBGM tidak ditemukan.</div>";
    exit;
}

$member = null; // Inisialisasi member
// Ambil data member berdasarkan no kbgm dari API
$member_response = callApi('members&id=' . urlencode($no_kbgm_param), 'GET');
if ($member_response && $member_response['status'] === 'success' && isset($member_response['data'])) {
    $member = $member_response['data'];
} else {
    echo "<div class='alert alert-danger m-3'>Data member tidak ditemukan atau API Error: " . ($member_response['message'] ?? 'Unknown error') . "</div>";
    exit;
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_kbgm = $_POST['no_kbgm']; // Ini dari hidden input
    $updateData = [
        'nik' => $_POST['nik'],
        'nama' => $_POST['nama'],
        'alamat' => $_POST['alamat'],
        'tempat_lahir' => $_POST['tempat_lahir'],
        'tanggal_lahir' => $_POST['tanggal_lahir'],
        'no_hp' => $_POST['no_hp'],
        'waktu_bergabung' => $_POST['waktu_bergabung'],
        'alamat_bergabung' => $_POST['alamat_bergabung'],
        'status' => isset($_POST['status']) ? 1 : 0
    ];

    // Panggil API untuk update member
    $update_response = callApi('members&id=' . urlencode($no_kbgm), 'PUT', $updateData);

    if ($update_response && $update_response['status'] === 'success') {
        echo "<script>alert('Data berhasil diperbarui'); window.location='list_member.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui data: " . ($update_response['message'] ?? 'Unknown error') . "</div>";
    }
}
?>

<div class="container mt-4">
    <h2>Edit Member</h2>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/style.css"> <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/script.js" defer></script> <form method="POST" class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <label>No KBGM</label>
                <input type="text" name="no_kbgm_display" class="form-control" value="<?= htmlspecialchars($member['no_kbgm']) ?>" disabled>
                <input type="hidden" name="no_kbgm" value="<?= htmlspecialchars($member['no_kbgm']) ?>">

                <label class="mt-3">NIK</label>
                <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($member['nik']) ?>" required>

                <label class="mt-3">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($member['nama']) ?>" required>

                <label class="mt-3">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($member['tempat_lahir']) ?>">
            </div>
            <div class="col-md-6">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control" value="<?= $member['tanggal_lahir'] ?>">

                <label class="mt-3">No HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($member['no_hp']) ?>">

                <label class="mt-3">Alamat</label>
                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($member['alamat']) ?>">

                <label class="mt-3">Alamat Bergabung</label>
                <input type="text" name="alamat_bergabung" class="form-control" value="<?= htmlspecialchars($member['alamat_bergabung']) ?>">

                <label class="mt-3">Waktu Bergabung</label>
                <input type="date" name="waktu_bergabung" class="form-control" value="<?= $member['waktu_bergabung'] ?>">

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="status" value="1" <?= $member['status'] == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label">Status Sudah (centang jika sudah)</label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-4">Simpan Perubahan</button>
        <a href="list_member.php" class="btn btn-secondary mt-4">Kembali</a>
    </form>
</div>
</body>
</html>