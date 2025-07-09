<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php';

$success = '';
$error = '';

// Fungsi getKodeWilayahFromNIK tetap di sini jika masih dipakai untuk tampilan frontend
function getKodeWilayahFromNIKFrontend($nik) {
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
    $nik = $_POST['nik'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $no_hp = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');
    if (substr($no_hp, 0, 1) === '0') {
        $no_hp = '62' . substr($no_hp, 1);
    }
    $waktu_bergabung = $_POST['waktu_bergabung'] ?? '';
    $alamat_bergabung = $_POST['alamat_bergabung'] ?? '';
    // NEW: Ambil data no_kk dan hubungan_kk
    $no_kk = $_POST['no_kk'] ?? '';
    $hubungan_kk = $_POST['hubungan_kk'] ?? '';

    // Validasi input dasar
    if (empty($nik) || empty($nama) || empty($alamat) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($no_hp) || empty($waktu_bergabung) || empty($alamat_bergabung) || empty($no_kk) || empty($hubungan_kk)) {
        $error = "Semua kolom (termasuk No. Kartu Keluarga dan Hubungan Keluarga) wajib diisi.";
    } else {
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
                'alamat_bergabung' => $alamat_bergabung,
                'no_kk' => $no_kk, // NEW
                'hubungan_kk' => $hubungan_kk // NEW
            ];

            // Panggil API untuk menambahkan member
            $add_member_response = callApi('members', 'POST', $postData);

            if ($add_member_response && $add_member_response['status'] === 'success') {
                $no_kbgm_baru = $add_member_response['no_kbgm'] ?? 'N/A';
                $success = "Member berhasil ditambahkan! Nomor KBGM: <strong>$no_kbgm_baru</strong>";
                // Kosongkan form setelah sukses
                $_POST = [];
            } else {
                $error = "Gagal menambahkan member: " . ($add_member_response['message'] ?? 'Unknown error');
            }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/script.js" defer></script>
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
                <input type="text" name="nik" class="form-control" required value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">
                <small class="text-muted" id="kodeWilayah"></small>
            </div>
            <div class="col">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <input type="text" name="alamat" class="form-control" required value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control" required value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? '') ?>">
            </div>
            <div class="col">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control" required value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxx" required value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Waktu Bergabung</label>
                <input type="date" name="waktu_bergabung" class="form-control" required value="<?= htmlspecialchars($_POST['waktu_bergabung'] ?? '') ?>">
            </div>
            <div class="col">
                <label>Alamat Bergabung</label>
                <input type="text" name="alamat_bergabung" class="form-control" required value="<?= htmlspecialchars($_POST['alamat_bergabung'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="no_kk">No. Kartu Keluarga (No. KK)</label>
            <input type="text" id="no_kk" name="no_kk" class="form-control" required value="<?= htmlspecialchars($_POST['no_kk'] ?? '') ?>">
            <small class="text-muted" id="kkStatus"></small>
        </div>

        <div class="mb-3">
            <label for="hubungan_kk">Hubungan dalam Keluarga</label>
            <select id="hubungan_kk" name="hubungan_kk" class="form-select" required>
                <option value="">Pilih Hubungan</option>
                <option value="Kepala Keluarga" <?= (($_POST['hubungan_kk'] ?? '') === 'Kepala Keluarga') ? 'selected' : '' ?>>Kepala Keluarga</option>
                <option value="Istri" <?= (($_POST['hubungan_kk'] ?? '') === 'Istri') ? 'selected' : '' ?>>Istri</option>
                <option value="Anak" <?= (($_POST['hubungan_kk'] ?? '') === 'Anak') ? 'selected' : '' ?>>Anak</option>
                <option value="Menantu" <?= (($_POST['hubungan_kk'] ?? '') === 'Menantu') ? 'selected' : '' ?>>Menantu</option>
                <option value="Cucu" <?= (($_POST['hubungan_kk'] ?? '') === 'Cucu') ? 'selected' : '' ?>>Cucu</option>
                <option value="Orang Tua" <?= (($_POST['hubungan_kk'] ?? '') === 'Orang Tua') ? 'selected' : '' ?>>Orang Tua</option>
                <option value="Mertua" <?= (($_POST['hubungan_kk'] ?? '') === 'Mertua') ? 'selected' : '' ?>>Mertua</option>
                <option value="Famili Lain" <?= (($_POST['hubungan_kk'] ?? '') === 'Famili Lain') ? 'selected' : '' ?>>Famili Lain</option>
                <option value="Lainnya" <?= (($_POST['hubungan_kk'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Member</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/script.js" defer></script>

<script>
// Fungsi getKodeWilayahFromNIK ini ada di sini dan di script.js, pastikan konsisten
function getKodeWilayahFromNIK(nik) {
    if (nik.length < 4) return '';
    // Perbaikan: nik.substring(2, 4) untuk mengambil 2 digit setelah 2 digit pertama
    const kodeKab = nik.substring(2, 4);
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
    const noKkInput = document.getElementById('no_kk');
    const kkStatusSpan = document.getElementById('kkStatus');

    // Event listener untuk NIK (sudah ada, hanya memastikan perbaikan substring)
    nikInput.addEventListener('input', function() {
        const nik = this.value;
        const kode = getKodeWilayahFromNIK(nik);
        kodeWilayahSpan.textContent = kode ? `Kode Wilayah: ${kode}` : '';
    });

    // NEW: Event listener untuk No. KK untuk memberikan feedback real-time
    noKkInput.addEventListener('input', debounce(function() {
        const noKk = this.value.trim();
        if (noKk.length === 16) { // Asumsi No. KK 16 digit
            checkNoKkStatus(noKk);
        } else {
            kkStatusSpan.textContent = 'Nomor KK harus 16 digit.';
            kkStatusSpan.style.color = 'orange';
        }
    }, 500)); // Debounce untuk menunda eksekusi agar tidak terlalu banyak request API

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    async function checkNoKkStatus(noKk) {
        kkStatusSpan.textContent = 'Memeriksa No. KK...';
        kkStatusSpan.style.color = 'gray';
        try {
            const apiKey = 'aiti'; // Pastikan ini sesuai dengan API_KEY di backend Anda
            const response = await fetch(`../../API/index.php?resource=kartu_keluarga&no_kk=${encodeURIComponent(noKk)}`, {
                method: 'GET',
                headers: {
                    'X-Api-Key': apiKey,
                    'Content-Type': 'application/json'
                }
            });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                kkStatusSpan.textContent = `No. KK ditemukan: ${result.data.no_kk} (Kepala Keluarga: ${result.data.kepala_keluarga_no_kbgm || 'Belum Ditentukan'})`;
                kkStatusSpan.style.color = 'green';
            } else if (response.status === 404) {
                kkStatusSpan.textContent = 'No. KK belum terdaftar. Akan dibuat baru.';
                kkStatusSpan.style.color = 'blue';
            } else {
                kkStatusSpan.textContent = `Gagal memeriksa No. KK: ${result.message || 'Unknown error'}`;
                kkStatusSpan.style.color = 'red';
            }
        } catch (error) {
            console.error('Error checking No. KK:', error);
            kkStatusSpan.textContent = 'Terjadi kesalahan saat memeriksa No. KK.';
            kkStatusSpan.style.color = 'red';
        }
    }
});
</script>

</body>
</html>