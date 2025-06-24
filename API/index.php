<?php
// =========================================================
// KONFIGURASI API UTAMA
// =========================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: localhost/kbgm-v2/kbgm/'); // Ganti dengan domain frontend Anda!
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

// Tangani preflight OPTIONS request (penting untuk CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// =========================================================
// KONFIGURASI DATABASE
// =========================================================
// Database KBGM
define('DB_KBGM_HOST', 'aiti.biz.id');
define('DB_KBGM_PORT', '3306');
define('DB_KBGM_NAME', 'aitibizi_kbgm');
define('DB_KBGM_USER', 'aitibizi_kbgm');
define('DB_KBGM_PASS', '@aitirsgmdd2024');

// Database SIK
define('DB_SIK_HOST', 'aiti.biz.id');
define('DB_SIK_PORT', '3306');
define('DB_SIK_NAME', 'aitibizi_sik_kbgm');
define('DB_SIK_USER', 'aitibizi_sik_kbgm');
define('DB_SIK_PASS', '@aitirsgmdd2024');

// =========================================================
// KONFIGURASI KEAMANAN API
// =========================================================
define('API_KEY', 'aiti'); // Ganti!

// =========================================================
// FUNGSI UTILITY
// =========================================================

/**
 * Mendapatkan koneksi PDO ke database tertentu.
 * @param string $dbType 'kbgm' atau 'sik'
 * @return PDO Objek koneksi PDO.
 * @throws PDOException Jika koneksi gagal.
 */
function getDbConnection($dbType) {
    $host = '';
    $port = '';
    $dbname = '';
    $user = '';
    $pass = '';

    switch ($dbType) {
        case 'kbgm':
            $host = DB_KBGM_HOST;
            $port = DB_KBGM_PORT;
            $dbname = DB_KBGM_NAME;
            $user = DB_KBGM_USER;
            $pass = DB_KBGM_PASS;
            break;
        case 'sik':
            $host = DB_SIK_HOST;
            $port = DB_SIK_PORT;
            $dbname = DB_SIK_NAME;
            $user = DB_SIK_USER;
            $pass = DB_SIK_PASS;
            break;
        default:
            throw new Exception("Invalid database type specified.");
    }

    try {
        $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $dbname . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed for {$dbType}: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Internal Server Error: Database ({$dbType}) connection failed."]);
        exit();
    } catch (Exception $e) {
        error_log("Error in getDbConnection: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Internal Server Error: " . $e->getMessage()]);
        exit();
    }
}

/**
 * Memverifikasi API Key.
 */
function verifyApiKey() {
    $providedApiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (empty($providedApiKey) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $providedApiKey = $headers['X-Api-Key'] ?? '';
    }

    if (empty($providedApiKey) || $providedApiKey !== API_KEY) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid or missing API Key.']);
        exit();
    }
}

/**
 * Mengirim respons JSON.
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Mendapatkan kode wilayah dari NIK (Fungsi duplikat untuk konsistensi API).
 */
function getKodeWilayahFromNIKApi($nik) {
    $kode = substr($nik, 2, 2);
    $mapping = [
        '01' => 'LS', '02' => 'LT', '03' => 'LU', '04' => 'LB', '05' => 'TL',
        '06' => 'TG', '07' => 'LM', '08' => 'WK', '09' => 'PS', '10' => 'PG',
        '11' => 'MS', '12' => 'TB', '13' => 'PB', '71' => 'BL', '72' => 'MT',
    ];
    return $mapping[$kode] ?? 'XX';
}

// =========================================================
// ROUTING API
// =========================================================
// Verifikasi API Key untuk setiap request
verifyApiKey();

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null; // Digunakan untuk routing ke resource

switch ($resource) {
    case 'check_connection':
        handleCheckConnection($method);
        break;
    case 'auth':
        handleAuth($method); // Untuk login
        break;
    case 'members':
        handleMembers($method);
        break;
    case 'matching':
        handleMatching($method);
        break;
    case 'visits':
        handleVisits($method);
        break;
    case 'dashboard':
        handleDashboard($method);
        break;
    default:
        sendJsonResponse(['status' => 'error', 'message' => 'Resource not found.'], 404);
        break;
}

// =========================================================
// HANDLER FUNGSI UNTUK SETIAP RESOURCE
// =========================================================

function handleCheckConnection($method) {
    if ($method === 'GET') {
        try {
            // Coba koneksi ke kedua database
            getDbConnection('kbgm');
            getDbConnection('sik');
            sendJsonResponse(['status' => 'success', 'message' => 'API and Database connection successful.']);
        } catch (Exception $e) {
            // Error ditangani oleh getDbConnection, jadi hanya perlu menangkap jika ada error lain
            // atau jika ingin pesan error lebih spesifik di sini.
        }
    } else {
        sendJsonResponse(['status' => 'error', 'message' => 'Method Not Allowed for check_connection.'], 405);
    }
}

function handleAuth($method) {
    if ($method === 'POST') {
        $pdo_kbgm = getDbConnection('kbgm');
        $data = json_decode(file_get_contents('php://input'), true);

        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            sendJsonResponse(['status' => 'error', 'message' => 'Username and password are required.'], 400);
        }

        try {
            $stmt = $pdo_kbgm->prepare("SELECT id, username, password, role FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // --- LOGIKA VERIFIKASI PASSWORD ---
                $password_verified = false;

                // 1. Coba verifikasi dengan password_verify() (untuk hash bcrypt/argon2)
                if (password_verify($password, $user['password'])) {
                    $password_verified = true;

                    // Jika hash password_hash() sudah usang atau perlu di-rehash
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = $pdo_kbgm->prepare("UPDATE admin SET password = ? WHERE id = ?");
                        $update_stmt->execute([$new_hash, $user['id']]);
                        error_log("Password for user " . $user['username'] . " rehashed and updated.");
                    }
                } else {
                    // 2. Jika password_verify() gagal, coba verifikasi sebagai MD5
                    if (strlen($user['password']) === 32 && ctype_xdigit($user['password'])) { // Deteksi string MD5
                        if (md5($password) === $user['password']) {
                            $password_verified = true;

                            // --- MIGRASI PASSWORD KE HASH YANG LEBIH AMAN ---
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $update_stmt = $pdo_kbgm->prepare("UPDATE admin SET password = ? WHERE id = ?");
                            $update_stmt->execute([$new_hash, $user['id']]);
                            error_log("Password for user " . $user['username'] . " migrated from MD5 to bcrypt.");
                        }
                    }
                }

                if ($password_verified) {
                    // Jangan kembalikan password atau data sensitif lainnya
                    sendJsonResponse([
                        'status' => 'success',
                        'message' => 'Login successful.',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role']
                        ]
                    ]);
                } else {
                    sendJsonResponse(['status' => 'error', 'message' => 'Invalid username or password.'], 401);
                }
            } else {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid username or password.'], 401);
            }
        } catch (PDOException $e) {
            error_log("Auth error: " . $e->getMessage());
            sendJsonResponse(['status' => 'error', 'message' => 'Internal server error during authentication.'], 500);
        }
    } else {
        sendJsonResponse(['status' => 'error', 'message' => 'Method Not Allowed for auth.'], 405);
    }
}


function handleMembers($method) {
    $pdo_kbgm = getDbConnection('kbgm');
    $id = $_GET['id'] ?? null; // Untuk GET by ID, PUT, DELETE
    $nik = $_GET['nik'] ?? null; // Untuk cek NIK ganda

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single member by no_kbgm
                $stmt = $pdo_kbgm->prepare("SELECT * FROM member WHERE no_kbgm = ? LIMIT 1");
                $stmt->execute([$id]);
                $member = $stmt->fetch();
                if ($member) {
                    sendJsonResponse(['status' => 'success', 'data' => $member]);
                } else {
                    sendJsonResponse(['status' => 'error', 'message' => 'Member not found.'], 404);
                }
            } elseif ($nik) {
                // Check for duplicate NIK
                $stmt = $pdo_kbgm->prepare("SELECT id FROM member WHERE nik = ?");
                $stmt->execute([$nik]);
                if ($stmt->fetch()) {
                    sendJsonResponse(['status' => 'success', 'is_duplicate' => true, 'message' => 'NIK already registered.']);
                } else {
                    sendJsonResponse(['status' => 'success', 'is_duplicate' => false, 'message' => 'NIK not found.']);
                }
            } else {
                // Get all members
                $keyword = $_GET['keyword'] ?? '';
                $query = "SELECT * FROM member";
                $params = [];
                if (!empty($keyword)) {
                    $query .= " WHERE no_kbgm LIKE ? OR nama LIKE ?";
                    $params[] = "%{$keyword}%";
                    $params[] = "%{$keyword}%";
                }
                $query .= " ORDER BY waktu_bergabung DESC";

                try {
                    $stmt = $pdo_kbgm->prepare($query);
                    $stmt->execute($params);
                    $members = $stmt->fetchAll();
                    sendJsonResponse(['status' => 'success', 'data' => $members]);
                } catch (PDOException $e) {
                    error_log("Error fetching members: " . $e->getMessage());
                    sendJsonResponse(['status' => 'error', 'message' => 'Failed to retrieve members.'], 500);
                }
            }
            break;

        case 'POST':
            // Add new member
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input.'], 400);
            }

            $nik = trim($data['nik'] ?? '');
            $nama = trim($data['nama'] ?? '');
            $alamat = trim($data['alamat'] ?? '');
            $tempat_lahir = trim($data['tempat_lahir'] ?? '');
            $tanggal_lahir = trim($data['tanggal_lahir'] ?? '');
            $no_hp = preg_replace('/[^0-9]/', '', $data['no_hp'] ?? '');
            if (substr($no_hp, 0, 1) === '0') {
                $no_hp = '62' . substr($no_hp, 1);
            }
            $waktu_bergabung = trim($data['waktu_bergabung'] ?? '');
            $alamat_bergabung = trim($data['alamat_bergabung'] ?? '');

            if (empty($nik) || empty($nama) || empty($alamat) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($no_hp) || empty($waktu_bergabung) || empty($alamat_bergabung)) {
                sendJsonResponse(['status' => 'error', 'message' => 'All fields are required.'], 400);
            }

            // Check for duplicate NIK again (server-side validation)
            $stmt_check_nik = $pdo_kbgm->prepare("SELECT id FROM member WHERE nik = ?");
            $stmt_check_nik->execute([$nik]);
            if ($stmt_check_nik->fetch()) {
                sendJsonResponse(['status' => 'error', 'message' => 'NIK already registered.'], 409); // Conflict
            }

            // Generate no_kbgm
            $kode_wilayah = getKodeWilayahFromNIKApi($nik);
            $stmt_count = $pdo_kbgm->prepare("SELECT COUNT(*) as jumlah FROM member WHERE no_kbgm LIKE ?");
            $like_pattern = $kode_wilayah . "%";
            $stmt_count->execute([$like_pattern]);
            $row_count = $stmt_count->fetch();
            $nomor_urut = $row_count['jumlah'] + 1;
            $no_kbgm_generated = $kode_wilayah . str_pad($nomor_urut, 4, '0', STR_PAD_LEFT);

            try {
                $stmt = $pdo_kbgm->prepare("INSERT INTO member (no_kbgm, nik, nama, alamat, tempat_lahir, tanggal_lahir, no_hp, waktu_bergabung, alamat_bergabung) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$no_kbgm_generated, $nik, $nama, $alamat, $tempat_lahir, $tanggal_lahir, $no_hp, $waktu_bergabung, $alamat_bergabung]);
                sendJsonResponse(['status' => 'success', 'message' => 'Member added successfully!', 'no_kbgm' => $no_kbgm_generated], 201); // Created
            } catch (PDOException $e) {
                error_log("Error adding member: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to add member.'], 500);
            }
            break;

        case 'PUT':
            // Update member
            if (!$id) {
                sendJsonResponse(['status' => 'error', 'message' => 'No KBGM ID is required for updating member.'], 400);
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input.'], 400);
            }

            $nik = trim($data['nik'] ?? null);
            $nama = trim($data['nama'] ?? null);
            $alamat = trim($data['alamat'] ?? null);
            $tempat_lahir = trim($data['tempat_lahir'] ?? null);
            $tanggal_lahir = trim($data['tanggal_lahir'] ?? null);
            $no_hp = trim($data['no_hp'] ?? null);
            $waktu_bergabung = trim($data['waktu_bergabung'] ?? null);
            $alamat_bergabung = trim($data['alamat_bergabung'] ?? null);
            $status = isset($data['status']) ? (int)$data['status'] : null;

            $setParts = [];
            $params = [];

            if ($nik !== null) { $setParts[] = 'nik = ?'; $params[] = $nik; }
            if ($nama !== null) { $setParts[] = 'nama = ?'; $params[] = $nama; }
            if ($alamat !== null) { $setParts[] = 'alamat = ?'; $params[] = $alamat; }
            if ($tempat_lahir !== null) { $setParts[] = 'tempat_lahir = ?'; $params[] = $tempat_lahir; }
            if ($tanggal_lahir !== null) { $setParts[] = 'tanggal_lahir = ?'; $params[] = $tanggal_lahir; }
            if ($no_hp !== null) { $setParts[] = 'no_hp = ?'; $params[] = $no_hp; }
            if ($waktu_bergabung !== null) { $setParts[] = 'waktu_bergabung = ?'; $params[] = $waktu_bergabung; }
            if ($alamat_bergabung !== null) { $setParts[] = 'alamat_bergabung = ?'; $params[] = $alamat_bergabung; }
            if ($status !== null) { $setParts[] = 'status = ?'; $params[] = $status; }

            if (empty($setParts)) {
                sendJsonResponse(['status' => 'error', 'message' => 'No fields to update.'], 400);
            }

            $sql = "UPDATE member SET " . implode(', ', $setParts) . " WHERE no_kbgm = ?";
            $params[] = $id; // ID untuk WHERE clause

            try {
                $stmt = $pdo_kbgm->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(['status' => 'success', 'message' => 'Member updated successfully.']);
                } else {
                    sendJsonResponse(['status' => 'info', 'message' => 'No changes made or member not found.'], 200);
                }
            } catch (PDOException $e) {
                error_log("Error updating member: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to update member.'], 500);
            }
            break;

        case 'DELETE':
            // Delete member (Opsional, tidak ada di kode asli Anda tapi umum di API)
            if (!$id) {
                sendJsonResponse(['status' => 'error', 'message' => 'No KBGM ID is required for deleting member.'], 400);
            }
            try {
                $stmt = $pdo_kbgm->prepare("DELETE FROM member WHERE no_kbgm = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(['status' => 'success', 'message' => 'Member deleted successfully.']);
                } else {
                    sendJsonResponse(['status' => 'error', 'message' => 'Member not found.'], 404);
                }
            } catch (PDOException $e) {
                error_log("Error deleting member: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete member.'], 500);
            }
            break;

        default:
            sendJsonResponse(['status' => 'error', 'message' => 'Method not allowed for members.'], 405);
            break;
    }
}

function handleMatching($method) {
    $pdo_kbgm = getDbConnection('kbgm');
    $pdo_sik = getDbConnection('sik');
    $no_kbgm = $_GET['no_kbgm'] ?? null;
    $nik = $_GET['nik'] ?? null; // Digunakan jika Anda ingin mencari pasien SIK langsung dari NIK

    switch ($method) {
        case 'GET':
            if ($no_kbgm) {
                // Ambil data member KBGM
                $stmt_member = $pdo_kbgm->prepare("SELECT nik FROM member WHERE no_kbgm = ? LIMIT 1");
                $stmt_member->execute([$no_kbgm]);
                $member = $stmt_member->fetch();

                if (!$member) {
                    sendJsonResponse(['status' => 'error', 'message' => 'Member not found in KBGM database.'], 404);
                }

                $nik_member = $member['nik'];

                // Cari pasien di SIK berdasarkan NIK
                $stmt_pasien = $pdo_sik->prepare("SELECT no_rkm_medis, nm_pasien, no_ktp FROM pasien WHERE no_ktp = ? LIMIT 1");
                $stmt_pasien->execute([$nik_member]);
                $pasien = $stmt_pasien->fetch();

                // Cek apakah sudah dicocokkan sebelumnya
                $stmt_match = $pdo_kbgm->prepare("SELECT no_rm FROM matching WHERE no_kbgm = ? LIMIT 1");
                $stmt_match->execute([$no_kbgm]);
                $matched = $stmt_match->fetch();

                sendJsonResponse([
                    'status' => 'success',
                    'member_data' => $member,
                    'pasien_data_sik' => $pasien,
                    'is_matched' => (bool)$matched,
                    'matched_rm' => $matched ? $matched['no_rm'] : null
                ]);
            } else {
                sendJsonResponse(['status' => 'error', 'message' => 'No KBGM is required for matching GET.'], 400);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input.'], 400);
            }

            $no_kbgm_post = trim($data['no_kbgm'] ?? '');
            $no_rm_post = trim($data['no_rm'] ?? '');

            if (empty($no_kbgm_post) || empty($no_rm_post)) {
                sendJsonResponse(['status' => 'error', 'message' => 'no_kbgm and no_rm are required.'], 400);
            }

            try {
                // Cek apakah sudah dicocokkan sebelumnya
                $stmt_check = $pdo_kbgm->prepare("SELECT id FROM matching WHERE no_kbgm = ? LIMIT 1");
                $stmt_check->execute([$no_kbgm_post]);
                if ($stmt_check->fetch()) {
                    sendJsonResponse(['status' => 'error', 'message' => 'Member already matched.'], 409); // Conflict
                }

                // Simpan pencocokan
                $stmt_insert = $pdo_kbgm->prepare("INSERT INTO matching (no_kbgm, no_rm) VALUES (?, ?)");
                $stmt_insert->execute([$no_kbgm_post, $no_rm_post]);

                // Update status member
                $stmt_update_member = $pdo_kbgm->prepare("UPDATE member SET status = 1 WHERE no_kbgm = ?");
                $stmt_update_member->execute([$no_kbgm_post]);

                sendJsonResponse(['status' => 'success', 'message' => 'Matching saved successfully.']);
            } catch (PDOException $e) {
                error_log("Error saving matching: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to save matching.'], 500);
            }
            break;
        default:
            sendJsonResponse(['status' => 'error', 'message' => 'Method not allowed for matching.'], 405);
            break;
    }
}

function handleVisits($method) {
    $pdo_kbgm = getDbConnection('kbgm');
    $pdo_sik = getDbConnection('sik');
    $no_kbgm = $_GET['no_kbgm'] ?? null;

    if ($method === 'GET') {
        if (!$no_kbgm) {
            sendJsonResponse(['status' => 'error', 'message' => 'No KBGM is required for visits.'], 400);
        }

        try {
            // Ambil no RM dari tabel matching
            $stmt_match = $pdo_kbgm->prepare("SELECT no_rm FROM matching WHERE no_kbgm = ? LIMIT 1");
            $stmt_match->execute([$no_kbgm]);
            $match_data = $stmt_match->fetch();
            $no_rm = $match_data['no_rm'] ?? null;

            $visits_sik_data = [];
            if ($no_rm) {
                // Ambil data kunjungan dari database SIK
                $query_visits_sik = "
                    SELECT
                        rp.tgl_registrasi AS tanggal_kunjungan,
                        dk.nm_dokter AS nama_dokter,
                        res.diagnosa_utama AS diagnosa,
                        res.edukasi AS edukasi,
                        TIMEDIFF(ki.tgl_keluar, ki.tgl_masuk) AS lama_kunjungan,
                        rp.no_rawat AS id_kunjungan_sik
                    FROM
                        reg_periksa rp
                    INNER JOIN
                        pasien ps ON rp.no_rkm_medis = ps.no_rkm_medis
                    INNER JOIN
                        dokter dk ON rp.kd_dokter = dk.kd_dokter
                    LEFT JOIN
                        resume_pasien_ranap res ON rp.no_rawat = res.no_rawat
                    LEFT JOIN
                        kamar_inap ki ON rp.no_rawat = ki.no_rawat
                    WHERE
                        rp.no_rkm_medis = ?
                    ORDER BY
                        rp.tgl_registrasi DESC
                ";
                $stmt_visits_sik = $pdo_sik->prepare($query_visits_sik);
                $stmt_visits_sik->execute([$no_rm]);
                $visits_sik_data = $stmt_visits_sik->fetchAll();
            }
            sendJsonResponse(['status' => 'success', 'data' => $visits_sik_data]);
        } catch (PDOException $e) {
            error_log("Error fetching visits: " . $e->getMessage());
            sendJsonResponse(['status' => 'error', 'message' => 'Failed to retrieve visits.'], 500);
        }
    } else {
        sendJsonResponse(['status' => 'error', 'message' => 'Method not allowed for visits.'], 405);
    }
}

function handleDashboard($method) {
    $pdo_kbgm = getDbConnection('kbgm');
    $pdo_sik = getDbConnection('sik'); // Untuk kunjungan, jika mau mengambil dari SIK

    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? null;
            if ($type === 'summary') {
                // Ambil data ringkasan umum
                try {
                    $jumlah_member_total = $pdo_kbgm->query("SELECT COUNT(*) as total FROM member")->fetchColumn();
                    $jumlah_kunjungan = $pdo_kbgm->query("SELECT COUNT(*) as total FROM `matching`")->fetchColumn(); // Dari tabel `matching`

                    // Kunjungan terakhir (ambil dari KBGM atau SIK tergantung kebutuhan)
                    // Jika dari SIK, perlu join atau query terpisah
                    $kunjungan_terakhir_kbgm = $pdo_kbgm->query("SELECT * FROM visit_logs ORDER BY visit_date DESC LIMIT 1")->fetch(); // Jika visit_logs ada di KBGM
                    // Jika ingin dari SIK, Anda perlu menentukan logic lebih lanjut

                    sendJsonResponse([
                        'status' => 'success',
                        'data' => [
                            'jumlah_member_total' => (int)$jumlah_member_total,
                            'jumlah_kunjungan' => (int)$jumlah_kunjungan,
                            'kunjungan_terakhir' => $kunjungan_terakhir_kbgm
                        ]
                    ]);
                } catch (PDOException $e) {
                    error_log("Error fetching dashboard summary: " . $e->getMessage());
                    sendJsonResponse(['status' => 'error', 'message' => 'Failed to retrieve dashboard summary.'], 500);
                }
            } elseif ($type === 'daily_members') {
                // Data untuk Grafik Member Bergabung (30 Hari Terakhir)
                try {
                    $query_daily_counts = "
                        SELECT
                            DATE_FORMAT(waktu_bergabung, '%Y-%m-%d') as registration_date,
                            COUNT(*) as total
                        FROM
                            member
                        WHERE
                            waktu_bergabung >= CURDATE() - INTERVAL 29 DAY
                        GROUP BY
                            registration_date
                        ORDER BY
                            registration_date ASC;
                    ";
                    $stmt_daily_counts = $pdo_kbgm->query($query_daily_counts);
                    $fetched_counts = $stmt_daily_counts->fetchAll(PDO::FETCH_KEY_PAIR); // Fetch as date => count map

                    $daily_member_counts = [];
                    $chart_labels = [];
                    for ($i = 29; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        $chart_labels[] = date('d M', strtotime($date));
                        $daily_member_counts[] = $fetched_counts[$date] ?? 0;
                    }
                    sendJsonResponse([
                        'status' => 'success',
                        'data' => [
                            'labels' => $chart_labels,
                            'counts' => $daily_member_counts
                        ]
                    ]);
                } catch (PDOException $e) {
                    error_log("Error fetching daily member counts: " . $e->getMessage());
                    sendJsonResponse(['status' => 'error', 'message' => 'Failed to retrieve daily member counts.'], 500);
                }
            } else {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid dashboard type.'], 400);
            }
            break;
        default:
            sendJsonResponse(['status' => 'error', 'message' => 'Method not allowed for dashboard.'], 405);
            break;
    }
}

// =========================================================
// HANDLER BARU UNTUK KARTU KELUARGA
// =========================================================
function handleKartuKeluarga($method) {
    $pdo_kbgm = getDbConnection('kbgm'); // Menggunakan koneksi database KBGM
    $id = $_GET['id'] ?? null; // ID KK (dari tabel kartu_keluarga)
    $no_kk = $_GET['no_kk'] ?? null; // Nomor KK itu sendiri, untuk pencarian

    switch ($method) {
        case 'GET':
            if ($id) {
                // Mendapatkan satu KK berdasarkan ID
                $stmt = $pdo_kbgm->prepare("SELECT * FROM kartu_keluarga WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $kk_data = $stmt->fetch();
                if ($kk_data) {
                    // Juga ambil anggota keluarga jika ingin ditampilkan langsung
                    $stmt_members = $pdo_kbgm->prepare("SELECT no_kbgm, nik, nama, hubungan_kk FROM member WHERE kk_id = ?");
                    $stmt_members->execute([$id]);
                    $members_in_kk = $stmt_members->fetchAll();
                    $kk_data['anggota'] = $members_in_kk;
                    sendJsonResponse(['status' => 'success', 'data' => $kk_data]);
                } else {
                    sendJsonResponse(['status' => 'error', 'message' => 'Kartu Keluarga not found.'], 404);
                }
            } elseif ($no_kk) {
                // Mendapatkan satu KK berdasarkan nomor KK (no_kk)
                $stmt = $pdo_kbgm->prepare("SELECT * FROM kartu_keluarga WHERE no_kk = ? LIMIT 1");
                $stmt->execute([$no_kk]);
                $kk_data = $stmt->fetch();
                if ($kk_data) {
                    // Juga ambil anggota keluarga
                    $stmt_members = $pdo_kbgm->prepare("SELECT no_kbgm, nik, nama, hubungan_kk FROM member WHERE kk_id = ?");
                    $stmt_members->execute([$kk_data['id']]);
                    $members_in_kk = $stmt_members->fetchAll();
                    $kk_data['anggota'] = $members_in_kk;
                    sendJsonResponse(['status' => 'success', 'data' => $kk_data]);
                } else {
                    sendJsonResponse(['status' => 'error', 'message' => 'Kartu Keluarga with this number not found.'], 404);
                }
            } else {
                // Mendapatkan semua KK (dengan pagination/filter jika perlu di masa depan)
                $stmt = $pdo_kbgm->query("SELECT * FROM kartu_keluarga ORDER BY no_kk ASC");
                $all_kk = $stmt->fetchAll();
                sendJsonResponse(['status' => 'success', 'data' => $all_kk]);
            }
            break;

        case 'POST':
            // Menambah Kartu Keluarga baru
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input.'], 400);
            }

            $no_kk = trim($data['no_kk'] ?? '');
            $kepala_keluarga_no_kbgm = trim($data['kepala_keluarga_no_kbgm'] ?? null); // KBGM No Kepala Keluarga
            $alamat_kk = trim($data['alamat_kk'] ?? '');
            $tanggal_pembuatan_kk = trim($data['tanggal_pembuatan_kk'] ?? null); // Format YYYY-MM-DD

            if (empty($no_kk) || empty($alamat_kk)) {
                sendJsonResponse(['status' => 'error', 'message' => 'No. KK and Alamat KK are required.'], 400);
            }

            // Cek duplikasi no_kk
            $stmt_check_kk = $pdo_kbgm->prepare("SELECT id FROM kartu_keluarga WHERE no_kk = ?");
            $stmt_check_kk->execute([$no_kk]);
            if ($stmt_check_kk->fetch()) {
                sendJsonResponse(['status' => 'error', 'message' => 'Nomor Kartu Keluarga sudah terdaftar.'], 409); // Conflict
            }

            try {
                $stmt = $pdo_kbgm->prepare("INSERT INTO kartu_keluarga (no_kk, kepala_keluarga_no_kbgm, alamat_kk, tanggal_pembuatan_kk) VALUES (?, ?, ?, ?)");
                $stmt->execute([$no_kk, $kepala_keluarga_no_kbgm, $alamat_kk, $tanggal_pembuatan_kk]);
                sendJsonResponse(['status' => 'success', 'message' => 'Kartu Keluarga added successfully!', 'id' => $pdo_kbgm->lastInsertId()], 201);
            } catch (PDOException $e) {
                error_log("Error adding Kartu Keluarga: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to add Kartu Keluarga.'], 500);
            }
            break;

        case 'PUT':
            // Memperbarui Kartu Keluarga (berdasarkan ID KK)
            if (!$id) {
                sendJsonResponse(['status' => 'error', 'message' => 'ID Kartu Keluarga is required for update.'], 400);
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input.'], 400);
            }

            $no_kk = trim($data['no_kk'] ?? null);
            $kepala_keluarga_no_kbgm = trim($data['kepala_keluarga_no_kbgm'] ?? null);
            $alamat_kk = trim($data['alamat_kk'] ?? null);
            $tanggal_pembuatan_kk = trim($data['tanggal_pembuatan_kk'] ?? null);

            $setParts = [];
            $params = [];

            if ($no_kk !== null) { $setParts[] = 'no_kk = ?'; $params[] = $no_kk; }
            if ($kepala_keluarga_no_kbgm !== null) { $setParts[] = 'kepala_keluarga_no_kbgm = ?'; $params[] = $kepala_keluarga_no_kbgm; }
            if ($alamat_kk !== null) { $setParts[] = 'alamat_kk = ?'; $params[] = $alamat_kk; }
            if ($tanggal_pembuatan_kk !== null) { $setParts[] = 'tanggal_pembuatan_kk = ?'; $params[] = $tanggal_pembuatan_kk; }

            if (empty($setParts)) {
                sendJsonResponse(['status' => 'error', 'message' => 'No fields to update for Kartu Keluarga.'], 400);
            }

            $sql = "UPDATE kartu_keluarga SET " . implode(', ', $setParts) . " WHERE id = ?";
            $params[] = $id;

            try {
                $stmt = $pdo_kbgm->prepare($sql);
                $stmt->execute($params);
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(['status' => 'success', 'message' => 'Kartu Keluarga updated successfully.']);
                } else {
                    sendJsonResponse(['status' => 'info', 'message' => 'No changes made or Kartu Keluarga not found.'], 200);
                }
            } catch (PDOException $e) {
                error_log("Error updating Kartu Keluarga: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to update Kartu Keluarga.'], 500);
            }
            break;

        case 'DELETE':
            // Menghapus Kartu Keluarga
            if (!$id) {
                sendJsonResponse(['status' => 'error', 'message' => 'ID Kartu Keluarga is required for deletion.'], 400);
            }
            try {
                // Setel kk_id dan hubungan_kk di member menjadi NULL jika KK dihapus
                // Ini akan mencegah error foreign key jika KK memiliki member yang terhubung
                $pdo_kbgm->beginTransaction(); // Mulai transaksi
                $stmt_null_members = $pdo_kbgm->prepare("UPDATE member SET kk_id = NULL, hubungan_kk = NULL WHERE kk_id = ?");
                $stmt_null_members->execute([$id]);

                $stmt = $pdo_kbgm->prepare("DELETE FROM kartu_keluarga WHERE id = ?");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    $pdo_kbgm->commit(); // Selesaikan transaksi
                    sendJsonResponse(['status' => 'success', 'message' => 'Kartu Keluarga and linked members updated successfully.']);
                } else {
                    $pdo_kbgm->rollBack(); // Batalkan transaksi
                    sendJsonResponse(['status' => 'error', 'message' => 'Kartu Keluarga not found.'], 404);
                }
            } catch (PDOException $e) {
                $pdo_kbgm->rollBack(); // Batalkan transaksi jika ada error
                error_log("Error deleting Kartu Keluarga: " . $e->getMessage());
                sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete Kartu Keluarga.'], 500);
            }
            break;

        default:
            sendJsonResponse(['status' => 'error', 'message' => 'Method not allowed for kartu_keluarga.'], 405);
            break;
    }
}


// =========================================================
// HANDLER BARU UNTUK FAMILY MEMBERS (Tambahkan semua anggota keluarga)
// =========================================================
function handleFamilyMembers($method) {
    if ($method !== 'POST') {
        sendJsonResponse(['status' => 'error', 'message' => 'Method Not Allowed.'], 405);
    }

    $pdo_kbgm = getDbConnection('kbgm');
    $data_payload = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($data_payload['kepala_keluarga'])) {
        sendJsonResponse(['status' => 'error', 'message' => 'Invalid JSON input or missing kepala_keluarga data.'], 400);
    }

    $kepala_keluarga_data = $data_payload['kepala_keluarga'];
    $anggota_lain_data = $data_payload['anggota_lain'] ?? [];

    $pdo_kbgm->beginTransaction(); // Mulai transaksi untuk memastikan semua berhasil atau semua gagal

    try {
        // === 1. Proses Kartu Keluarga ===
        $no_kk_input = trim($kepala_keluarga_data['no_kk_input'] ?? '');
        $kk_id_hidden = filter_var($kepala_keluarga_data['kk_id_hidden'] ?? null, FILTER_VALIDATE_INT);
        $alamat_kk_from_member = trim($kepala_keluarga_data['alamat'] ?? '');
        $kepala_keluarga_no_kbgm_val = null; // Akan diisi setelah kepala keluarga terdaftar

        $kk_id = null;
        $no_kk_generated = null;

        if ($kk_id_hidden) {
            // KK sudah ada, gunakan ID yang dikirim dari frontend
            $kk_id = $kk_id_hidden;
            $stmt_get_kk = $pdo_kbgm->prepare("SELECT no_kk FROM kartu_keluarga WHERE id = ?");
            $stmt_get_kk->execute([$kk_id]);
            $kk_existing = $stmt_get_kk->fetch();
            if (!$kk_existing) {
                 throw new Exception("Referenced KK ID not found.");
            }
            $no_kk_generated = $kk_existing['no_kk'];
        } else {
            // KK baru atau No. KK baru, buat KK baru
            if (empty($no_kk_input)) {
                // Generate No. KK jika tidak disediakan
                $no_kk_generated = 'KK' . date('YmdHis') . rand(100, 999); // Contoh sederhana
            } else {
                $no_kk_generated = $no_kk_input;
            }

            // Cek duplikasi no_kk
            $stmt_check_kk = $pdo_kbgm->prepare("SELECT id FROM kartu_keluarga WHERE no_kk = ?");
            $stmt_check_kk->execute([$no_kk_generated]);
            if ($stmt_check_kk->fetch()) {
                throw new Exception("Nomor Kartu Keluarga sudah terdaftar.");
            }

            $stmt_insert_kk = $pdo_kbgm->prepare("INSERT INTO kartu_keluarga (no_kk, alamat_kk, tanggal_pembuatan_kk) VALUES (?, ?, ?)");
            $stmt_insert_kk->execute([
                $no_kk_generated,
                $alamat_kk_from_member,
                date('Y-m-d') // Tanggal pembuatan KK otomatis
            ]);
            $kk_id = $pdo_kbgm->lastInsertId();
        }

        // === 2. Tambah Member Kepala Keluarga ===
        $no_kbgm_kk = generateNoKbgm($kepala_keluarga_data['nik'], $pdo_kbgm); // Pastikan fungsi ini ada
        $kepala_keluarga_no_kbgm_val = $no_kbgm_kk; // Simpan untuk update KK
        
        $stmt_kk = $pdo_kbgm->prepare("INSERT INTO member
            (no_kbgm, nik, nama, alamat, tempat_lahir, tanggal_lahir, no_hp, waktu_bergabung, alamat_bergabung, kk_id, hubungan_kk)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_kk->execute([
            $no_kbgm_kk, $kepala_keluarga_data['nik'], $kepala_keluarga_data['nama'],
            $kepala_keluarga_data['alamat'], $kepala_keluarga_data['tempat_lahir'],
            $kepala_keluarga_data['tanggal_lahir'], $kepala_keluarga_data['no_hp'],
            $kepala_keluarga_data['waktu_bergabung'], $kepala_keluarga_data['alamat_bergabung'],
            $kk_id, 'Kepala Keluarga' // Hubungan otomatis untuk kepala keluarga
        ]);

        // Update KK dengan no_kbgm kepala keluarga
        $stmt_update_kk_head = $pdo_kbgm->prepare("UPDATE kartu_keluarga SET kepala_keluarga_no_kbgm = ? WHERE id = ?");
        $stmt_update_kk_head->execute([$kepala_keluarga_no_kbgm_val, $kk_id]);


        // === 3. Tambah Anggota Keluarga Lain ===
        foreach ($anggota_lain_data as $anggota) {
            $no_kbgm_anggota = generateNoKbgm($anggota['nik'], $pdo_kbgm);

            $stmt_anggota = $pdo_kbgm->prepare("INSERT INTO member
                (no_kbgm, nik, nama, alamat, tempat_lahir, tanggal_lahir, no_hp, waktu_bergabung, alamat_bergabung, kk_id, hubungan_kk)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_anggota->execute([
                $no_kbgm_anggota, $anggota['nik'], $anggota['nama'],
                $anggota['alamat'], $anggota['tempat_lahir'],
                $anggota['tanggal_lahir'], $anggota['no_hp'],
                $anggota['waktu_bergabung'], $anggota['alamat_bergabung'],
                $kk_id, $anggota['hubungan_kk'] // Hubungan dari form
            ]);
        }

        $pdo_kbgm->commit(); // Selesaikan transaksi jika semua berhasil
        sendJsonResponse(['status' => 'success', 'message' => 'Semua member dan Kartu Keluarga berhasil ditambahkan!', 'no_kk_generated' => $no_kk_generated, 'kk_id' => $kk_id], 201);

    } catch (Exception $e) {
        $pdo_kbgm->rollBack(); // Batalkan transaksi jika ada error
        error_log("Error adding family members: " . $e->getMessage());
        sendJsonResponse(['status' => 'error', 'message' => 'Gagal menambahkan member keluarga: ' . $e->getMessage()], 500);
    }
}
?>