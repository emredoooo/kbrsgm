<?php
// kbgm/cekkoneksi.php
include 'includes/api_helper.php'; // Sertakan API helper

$response = array();

// Panggil endpoint check_connection di API Anda
$apiResponse = callApi('check_connection', 'GET');

if ($apiResponse && $apiResponse['status'] === 'success') {
    $response['status'] = 'success';
    $response['message'] = 'Koneksi API berhasil';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Koneksi API gagal: ' . ($apiResponse['message'] ?? 'Unknown error');
}

header('Content-Type: application/json');
echo json_encode($response);
?>