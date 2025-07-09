<?php
include 'includes/auth.php'; // Biarkan autentikasi lokal dulu
include 'includes/navbar.php';
include 'includes/api_helper.php'; // Sertakan API helper

// Ambil data ringkasan umum dari API
$summary_response = callApi('dashboard&type=summary', 'GET');
$jumlah_member_total = 0;
$jumlah_kunjungan = 0;
$kunjungan_terakhir = null;

if ($summary_response && $summary_response['status'] === 'success') {
    $jumlah_member_total = $summary_response['data']['jumlah_member_total'];
    $jumlah_kunjungan = $summary_response['data']['jumlah_kunjungan'];
    $kunjungan_terakhir = $summary_response['data']['kunjungan_terakhir'];
} else {
    error_log("Failed to fetch dashboard summary from API: " . ($summary_response['message'] ?? 'Unknown error'));
}

// --- Data untuk Grafik Member Bergabung (30 Hari Terakhir) ---
$daily_member_response = callApi('dashboard&type=daily_members', 'GET');
$json_chart_labels = '[]';
$json_daily_member_counts = '[]';

if ($daily_member_response && $daily_member_response['status'] === 'success') {
    $chart_labels = $daily_member_response['data']['labels'];
    $daily_member_counts = $daily_member_response['data']['counts'];
    $json_chart_labels = json_encode($chart_labels);
    $json_daily_member_counts = json_encode($daily_member_counts);
} else {
    error_log("Failed to fetch daily member counts from API: " . ($daily_member_response['message'] ?? 'Unknown error'));
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>KBGM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../kbgm-v2/kbgm/assets/style.css"> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../kbgm-v2/kbgm/assets/script.js" defer></script> </head>
<body class="bg-light">
    <?php // include navbar di sini jika belum ada ?>

    <div class="container py-5">
        <h2 class="mb-4">Dashboard KBGM</h2>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5>Total Member KBGM</h5>
                        <h3><?= $jumlah_member_total ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5>Member Pernah Dirawat</h5>
                        <h3><?= $jumlah_kunjungan ?></h3>
                    </div>
                </div>
            </div>

            </div>

        <div class="row g-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Pendaftaran Member Baru (30 Hari Terakhir)</h5>
                        <div class="chart-container">
                            <canvas id="memberChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const chartLabels = <?= $json_chart_labels ?>;
        const dailyMemberCounts = <?= $json_daily_member_counts ?>;

        const ctx = document.getElementById('memberChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah Member Baru',
                    data: dailyMemberCounts,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Member'
                        },
                        ticks: {
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });
    </script>
</body>
</html>