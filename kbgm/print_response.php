<?php
// kbgm/print_response.php
header('Content-Type: application/json'); // Penting! Beri tahu aplikasi bahwa ini adalah JSON
// header('Access-Control-Allow-Origin: *'); // Tambahkan jika perlu untuk CORS, tapi biasanya tidak untuk aplikasi pihak ketiga

include '../includes/api_helper.php'; // Untuk memanggil API

// Ambil no_kbgm dari parameter URL
$no_kbgm = $_GET['no_kbgm'] ?? null;

$print_data = array();

if ($no_kbgm) {
    // 1. Panggil API untuk mendapatkan data member
    $member_response = callApi('members&id=' . urlencode($no_kbgm), 'GET');

    if ($member_response && $member_response['status'] === 'success' && isset($member_response['data'])) {
        $member = $member_response['data'];

        // 2. Format data sesuai spesifikasi JSON aplikasi "Bluetooth Print"

        // Judul (opsional)
        $obj_title = new stdClass();
        $obj_title->type = 0; // Teks 
        $obj_title->content = '=== KARTU MEMBER KBGM ==='; // Judul Anda
        $obj_title->bold = 1; // Bold 
        $obj_title->align = 1; // Tengah 
        $obj_title->format = 2; // Double Height + Width (besar) 
        array_push($print_data, $obj_title);

        // Baris kosong
        $obj_empty = new stdClass();
        $obj_empty->type = 0; // Teks 
        $obj_empty->content = ' '; // Baris kosong 
        array_push($print_data, $obj_empty);

        // Nama Member
        $obj_nama = new stdClass();
        $obj_nama->type = 0; // Teks 
        $obj_nama->content = 'Nama: ' . $member['nama'];
        $obj_nama->bold = 0; // Tidak bold
        $obj_nama->align = 0; // Kiri 
        $obj_nama->format = 0; // Normal 
        array_push($print_data, $obj_nama);

        // No. KBGM
        $obj_kbgm = new stdClass();
        $obj_kbgm->type = 0; // Teks 
        $obj_kbgm->content = 'No. KBGM: ' . $member['no_kbgm'];
        $obj_kbgm->bold = 0;
        $obj_kbgm->align = 0;
        $obj_kbgm->format = 0;
        array_push($print_data, $obj_kbgm);

        // No. HP
        $obj_hp = new stdClass();
        $obj_hp->type = 0; // Teks 
        $obj_hp->content = 'No. HP: ' . $member['no_hp'];
        $obj_hp->bold = 0;
        $obj_hp->align = 0;
        $obj_hp->format = 0;
        array_push($print_data, $obj_hp);

        // Baris kosong
        $obj_empty2 = new stdClass();
        $obj_empty2->type = 0; // Teks
        $obj_empty2->content = ' '; // Baris kosong
        array_push($print_data, $obj_empty2);

        // QR Code (opsional, jika Anda ingin mencetak QR dari no_kbgm) 
        // Pastikan 'value' valid untuk QR code.
        $obj_qr = new stdClass();
        $obj_qr->type = 3; // QR Code 
        $obj_qr->value = $member['no_kbgm']; // Atau URL profil member, dll. 
        $obj_qr->size = 40; // Ukuran QR code dalam mm (sesuaikan) 
        $obj_qr->align = 1; // Tengah 
        array_push($print_data, $obj_qr);

        // Baris kosong
        $obj_empty3 = new stdClass();
        $obj_empty3->type = 0; // Teks
        $obj_empty3->content = ' '; // Baris kosong
        array_push($print_data, $obj_empty3);

        // Catatan kaki (opsional)
        $obj_footer = new stdClass();
        $obj_footer->type = 0; // Teks
        $obj_footer->content = 'Terima kasih telah menjadi member KBGM!';
        $obj_footer->bold = 0;
        $obj_footer->align = 1; // Tengah
        $obj_footer->format = 4; // Kecil 
        array_push($print_data, $obj_footer);

    } else {
        // Jika data member tidak ditemukan atau ada error API
        $obj_error = new stdClass();
        $obj_error->type = 0; // Teks
        $obj_error->content = 'Error: Data member tidak ditemukan atau API bermasalah.';
        $obj_error->bold = 1;
        $obj_error->align = 1;
        array_push($print_data, $obj_error);
    }
} else {
    // Jika no_kbgm tidak diberikan di URL
    $obj_no_id = new stdClass();
    $obj_no_id->type = 0; // Teks
    $obj_no_id->content = 'Error: Parameter no_kbgm tidak ditemukan.';
    $obj_no_id->bold = 1;
    $obj_no_id->align = 1;
    array_push($print_data, $obj_no_id);
}

// Mengembalikan array sebagai JSON 
echo json_encode($print_data, JSON_UNESCAPED_UNICODE); // JSON_UNESCAPED_UNICODE untuk karakter non-ASCII jika ada
?>