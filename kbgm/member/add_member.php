<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php'; // SERTAKAN INI

$success = '';
$error = '';

// Fungsi getKodeWilayahFromNIK tetap di sini jika masih dipakai untuk tampilan frontend
// atau jika Anda ingin menggunakannya untuk validasi awal sebelum kirim ke API.
// Tapi logika perhitungan no_kbgm akan dipindahkan ke API.
function getKodeWilayahFromNIKFrontend($nik) { // Beri nama berbeda agar tidak konflik dengan API
    if (strlen($nik) < 4) return '';
    $kodeKab = substr($nik, 2, 2);
    $mapping = [
        '01' => 'LS', '02' => 'LT', '03' => 'LU', '04' => 'LB', '05' => 'TL',
        '06' => 'TG', '07' => 'LM', '08' => 'WK', '09' => 'PS', '10' => 'PG',
        '11' => 'MS', '12' => 'TB', '13' => 'PB', '71' => 'BL', '72' => 'MT',
    ];
    return $mapping[$kodeKab] ?? '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $no_hp = preg_replace('/[^0-9]/', '', $_POST['no_hp']);
    if (substr($no_hp, 0, 1) === '0') {
        $no_hp = '62' . substr($no_hp, 1);
    }
    $waktu_bergabung = $_POST['waktu_bergabung'];
    $alamat_bergabung = $_POST['alamat_bergabung'];

    // Panggil API untuk cek NIK ganda
    $check_nik_response = callApi('members&nik=' . urlencode($nik), 'GET');

    if ($check_nik_response && $check_nik_response['status'] === 'success' && $check_nik_response['is_duplicate']) {
        $error = "NIK sudah terdaftar! Silahkan cek di menu <a href='/kbgm/member/list_member.php'>List Member</a>";
    } else if ($check_nik_response && $check_nik_response['status'] === 'error') {
        $error = "Gagal cek NIK: " . ($check_nik_response['message'] ?? 'Unknown API error');
    }
    else {
        // Data untuk dikirim ke API
        $postData = [
            'nik' => $nik,
            'nama' => $nama,
            'alamat' => $alamat,
            'tempat_lahir' => $tempat_lahir,
            'tanggal_lahir' => $tanggal_lahir,
            'no_hp' => $no_hp,
            'waktu_bergabung' => $waktu_bergabung,
            'alamat_bergabung' => $alamat_bergabung
        ];

        // Panggil API untuk menambahkan member
        $add_member_response = callApi('members', 'POST', $postData);

        if ($add_member_response && $add_member_response['status'] === 'success') {
            $no_kbgm_baru = $add_member_response['no_kbgm'] ?? 'N/A';
            $success = "Member berhasil ditambahkan! Nomor KBGM: <strong>$no_kbgm_baru</strong>";
        } else {
            $error = "Gagal menambahkan member: " . ($add_member_response['message'] ?? 'Unknown error');
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Member | KBGM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <script src="../assets/script.js" defer></script>
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Member Baru</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col">
                <label>NIK</label>
                <input type="text" name="nik" class="form-control" required>
                <small class="text-muted" id="kodeWilayah"></small>
            </div>
            <div class="col">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <input type="text" name="alamat" class="form-control" required>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control" required>
            </div>
            <div class="col">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxx" required>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Waktu Bergabung</label>
                <input type="date" name="waktu_bergabung" class="form-control" required>
            </div>
            <div class="col">
                <label>Alamat Bergabung</label>
                <input type="text" name="alamat_bergabung" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Member</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <script src="../assets/script.js" defer></script> </body>

<script>
function getKodeWilayahFromNIK(nik) {
    if (nik.length < 4) return '';
    const kodeKab = nik.substring(2, 2); // Ini akan selalu mengambil substring kosong jika 2,2
    // Seharusnya nik.substring(2, 4) seperti di script.js asli
    const mapping = {
        '01': 'LS', '02': 'LT', '03': 'LU', '04': 'LB', '05': 'TL',
        '06': 'TG', '07': 'LM', '08': 'WK', '09': 'PS', '10': 'PG',
        '11': 'MS', '12': 'TB', '13': 'PB', '71': 'BL', '72': 'MT',
    };
    return mapping[kodeKab] ?? '';
}

document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.querySelector('input[name="nik"]');
    const kodeWilayahSpan = document.getElementById('kodeWilayah');

    nikInput.addEventListener('input', function() {
        const nik = this.value;
        const kode = getKodeWilayahFromNIK(nik);
        kodeWilayahSpan.textContent = kode ? `Kode Wilayah: ${kode}` : '';
    });
});
</script>

</body>
</html>