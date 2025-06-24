<?php
// kbgm/includes/api_helper.php
require_once __DIR__ . '/api_config.php'; // Pastikan path ini benar

/**
 * Fungsi generik untuk melakukan panggilan API dari PHP.
 * @param string $endpoint_path Path endpoint API relatif (misal: 'members', 'members&id=1')
 * @param string $method Metode HTTP (GET, POST, PUT, DELETE)
 * @param array $data Data yang akan dikirim (untuk POST/PUT), opsional
 * @return array|false Respons dari API dalam bentuk array, atau false jika gagal.
 */
function callApi($endpoint_path, $method = 'GET', $data = []) {
    $ch = curl_init();
    $url = API_BASE_URL . "?resource=" . $endpoint_path; // Tambahkan parameter resource

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Api-Key: ' . API_KEY_FRONTEND
    ]);

    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            // Tidak perlu pengaturan khusus untuk GET
            break;
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('cURL Error from frontend: ' . curl_error($ch));
        curl_close($ch);
        return ['status' => 'error', 'message' => 'Koneksi ke API gagal.'];
    }

    curl_close($ch);
    $decodedResponse = json_decode($response, true);

    // Cek status code HTTP dari API
    if ($httpCode >= 200 && $httpCode < 300) {
        return $decodedResponse;
    } else {
        error_log('API Frontend Error: HTTP ' . $httpCode . ' - ' . ($decodedResponse['message'] ?? 'Unknown API error from backend'));
        return $decodedResponse;
    }
}
?>