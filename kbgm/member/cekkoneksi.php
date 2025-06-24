<?php
include '../includes/koneksi.php';

$response = array();

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        $response['status'] = 'error';
        $response['message'] = 'Koneksi gagal: ' . $conn->connect_error;
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Koneksi berhasil';
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
