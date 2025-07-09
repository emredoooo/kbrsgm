<?php
include '../includes/auth.php';
include '../includes/navbar.php';
include '../includes/api_helper.php';

$kk_data = null;
$is_edit_mode = false;
$page_title = "Tambah Kartu Keluarga Baru";
$submit_button_text = "Simpan Kartu Keluarga";
$success_message = '';
$error_message = '';

// Ambil no_kk dari URL jika ada, untuk mode edit
$no_kk_param = isset($_GET['no_kk']) ? $_GET['no_kk'] : '';

// Jika ada no_kk_param, coba ambil data KK untuk mode edit
if (!empty($no_kk_param)) {
    $is_edit_mode = true;
    $page_title = "Edit Kartu Keluarga";
    $submit_button_text = "Perbarui Kartu Keluarga";

    $kk_response = callApi('kartu_keluarga&no_kk=' . urlencode($no_kk_param), 'GET');

    if ($kk_response && $kk_response['status'] === 'success' && $kk_response['data']) {
        $kk_data = $kk_response['data'];
    } else {
        $error_message = "Data Kartu Keluarga tidak ditemukan atau API Error: " . ($kk_response['message'] ?? 'Unknown error');
        $is_edit_mode = false; // Kembali ke mode tambah jika tidak ditemukan
    }
}

// Tangani form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $post_no_kk = trim($_POST['no_kk'] ?? '');
    $post_kepala_keluarga_no_kbgm = trim($_POST['kepala_keluarga_no_kbgm'] ?? '');
    $post_alamat_kk = trim($_POST['alamat_kk'] ?? '');
    $post_rt_kk = trim($_POST['rt_kk'] ?? '');
    $post_rw_kk = trim($_POST['rw_kk'] ?? '');
    $post_kelurahan_kk = trim($_POST['kelurahan_kk'] ?? '');
    $post_kecamatan_kk = trim($_POST['kecamatan_kk'] ?? '');
    $post_kota_kab_kk = trim($_POST['kota_kab_kk'] ?? '');
    $post_provinsi_kk = trim($_POST['provinsi_kk'] ?? '');
    $post_kode_pos_kk = trim($_POST['kode_pos_kk'] ?? '');
    $post_tanggal_pembuatan_kk = trim($_POST['tanggal_pembuatan_kk'] ?? '');

    // Data yang akan dikirim ke API
    $api_data = [
        'no_kk' => $post_no_kk,
        'kepala_keluarga_no_kbgm' => !empty($post_kepala_keluarga_no_kbgm) ? $post_kepala_keluarga_no_kbgm : null,
        'alamat_kk' => $post_alamat_kk,
        'rt_kk' => !empty($post_rt_kk) ? $post_rt_kk : null,
        'rw_kk' => !empty($post_rw_kk) ? $post_rw_kk : null,
        'kelurahan_kk' => !empty($post_kelurahan_kk) ? $post_kelurahan_kk : null,
        'kecamatan_kk' => !empty($post_kecamatan_kk) ? $post_kecamatan_kk : null,
        'kota_kab_kk' => !empty($post_kota_kab_kk) ? $post_kota_kab_kk : null,
        'provinsi_kk' => !empty($post_provinsi_kk) ? $post_provinsi_kk : null,
        'kode_pos_kk' => !empty($post_kode_pos_kk) ? $post_kode_pos_kk : null,
        'tanggal_pembuatan_kk' => !empty($post_tanggal_pembuatan_kk) ? $post_tanggal_pembuatan_kk : null,
    ];

    $api_method = 'POST';
    $api_url_params = 'kartu_keluarga';

    if ($is_edit_mode && !empty($no_kk_param)) {
        // Mode edit: gunakan PUT request
        $api_method = 'PUT';
        $api_url_params .= '&id=' . urlencode($kk_data['id']); // Gunakan ID dari data yang diambil
    }

    $response = callApi($api_url_params, $api_method, $api_data);

    if ($response && $response['status'] === 'success') {
        $success_message = "Kartu Keluarga berhasil " . ($is_edit_mode ? "diperbarui!" : "ditambahkan!");
        // Update kk_data untuk merefleksikan perubahan atau clear form untuk tambah baru
        if (!$is_edit_mode) {
            // Untuk mode tambah, kosongkan form atau arahkan ke list/edit yang baru
            // header('Location: manage_kk.php?no_kk=' . urlencode($post_no_kk)); // Arahkan ke halaman edit KK yang baru dibuat
            // exit();
            $kk_data = null; // Kosongkan form setelah tambah
        } else {
            // Refresh data setelah update
            $kk_response = callApi('kartu_keluarga&no_kk=' . urlencode($post_no_kk), 'GET');
            if ($kk_response && $kk_response['status'] === 'success' && $kk_response['data']) {
                $kk_data = $kk_response['data'];
            }
        }
    } else {
        $error_message = "Gagal " . ($is_edit_mode ? "memperbarui" : "menambahkan") . " Kartu Keluarga: " . ($response['message'] ?? 'Unknown error');
    }
}

// Fungsi helper untuk mendapatkan nilai default atau dari data KK
function get_form_value($key, $default = '', $kk_data = null) {
    if (isset($_POST[$key])) {
        return htmlspecialchars($_POST[$key]);
    }
    if ($kk_data && isset($kk_data[$key])) {
        return htmlspecialchars($kk_data[$key]);
    }
    return htmlspecialchars($default);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | KBGM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container mt-4">
        <h2><?= $page_title ?></h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <?php if ($is_edit_mode && $kk_data): ?>
                <input type="hidden" name="kk_id" value="<?= htmlspecialchars($kk_data['id']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="no_kk" class="form-label">No. Kartu Keluarga (No. KK)</label>
                <input type="text" class="form-control" id="no_kk" name="no_kk"
                    value="<?= get_form_value('no_kk', '', $kk_data) ?>"
                    <?= $is_edit_mode ? 'readonly' : 'required' ?>>
                <?php if ($is_edit_mode): ?>
                    <small class="form-text text-muted">Nomor KK tidak bisa diubah.</small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="kepala_keluarga_no_kbgm" class="form-label">No. KBGM Kepala Keluarga (Opsional)</label>
                <input type="text" class="form-control" id="kepala_keluarga_no_kbgm" name="kepala_keluarga_no_kbgm"
                       value="<?= get_form_value('kepala_keluarga_no_kbgm', '', $kk_data) ?>">
                <small class="form-text text-muted">Isi dengan No. KBGM member yang menjadi kepala keluarga.</small>
            </div>

            <div class="mb-3">
                <label for="alamat_kk" class="form-label">Alamat Kartu Keluarga</label>
                <input type="text" class="form-control" id="alamat_kk" name="alamat_kk"
                       value="<?= get_form_value('alamat_kk', '', $kk_data) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="rt_kk" class="form-label">RT</label>
                    <input type="text" class="form-control" id="rt_kk" name="rt_kk"
                           value="<?= get_form_value('rt_kk', '', $kk_data) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="rw_kk" class="form-label">RW</label>
                    <input type="text" class="form-control" id="rw_kk" name="rw_kk"
                           value="<?= get_form_value('rw_kk', '', $kk_data) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="kelurahan_kk" class="form-label">Kelurahan / Desa</label>
                <input type="text" class="form-control" id="kelurahan_kk" name="kelurahan_kk"
                       value="<?= get_form_value('kelurahan_kk', '', $kk_data) ?>">
            </div>

            <div class="mb-3">
                <label for="kecamatan_kk" class="form-label">Kecamatan</label>
                <input type="text" class="form-control" id="kecamatan_kk" name="kecamatan_kk"
                       value="<?= get_form_value('kecamatan_kk', '', $kk_data) ?>">
            </div>

            <div class="mb-3">
                <label for="kota_kab_kk" class="form-label">Kota / Kabupaten</label>
                <input type="text" class="form-control" id="kota_kab_kk" name="kota_kab_kk"
                       value="<?= get_form_value('kota_kab_kk', '', $kk_data) ?>">
            </div>

            <div class="mb-3">
                <label for="provinsi_kk" class="form-label">Provinsi</label>
                <input type="text" class="form-control" id="provinsi_kk" name="provinsi_kk"
                       value="<?= get_form_value('provinsi_kk', '', $kk_data) ?>">
            </div>

            <div class="mb-3">
                <label for="kode_pos_kk" class="form-label">Kode Pos</label>
                <input type="text" class="form-control" id="kode_pos_kk" name="kode_pos_kk"
                       value="<?= get_form_value('kode_pos_kk', '', $kk_data) ?>">
            </div>

            <div class="mb-3">
                <label for="tanggal_pembuatan_kk" class="form-label">Tanggal Pembuatan KK</label>
                <input type="date" class="form-control" id="tanggal_pembuatan_kk" name="tanggal_pembuatan_kk"
                       value="<?= get_form_value('tanggal_pembuatan_kk', date('Y-m-d'), $kk_data) ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?= $submit_button_text ?></button>
            <a href="list_kk.php" class="btn btn-secondary">Kembali ke Daftar KK</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>