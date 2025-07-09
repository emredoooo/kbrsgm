<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Saku Digital - Aplikasi KBGM</title>
    <style>
        /* CSS Sederhana untuk Konsep */
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f9; color: #333; line-height: 1.6; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .accordion-item { border-bottom: 1px solid #eee; }
        .accordion-header { padding: 15px; cursor: pointer; font-weight: bold; font-size: 1.2em; display: flex; justify-content: space-between; align-items: center; }
        .accordion-header:after { content: '+'; font-size: 1.5em; }
        .accordion-body { padding: 0 15px 15px; max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out; }
        
        /* SOLUSI: Menaikkan nilai max-height agar konten tidak terpotong */
        .accordion-item.active .accordion-body { max-height: 3000px; /* Nilai besar agar cukup untuk konten yang sangat panjang */ }
        .accordion-item.active .accordion-header:after { content: '−'; }

        /* Style untuk kotak peringatan penting */
        .kotak-penting {
            background-color: #fff3cd; /* Warna kuning hangat */
            border-left: 5px solid #ffc107; /* Aksen warna */
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .kotak-penting h4 { margin-top: 0; }
        img { max-width: 100%; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px; margin-bottom: 10px; }
        
        /* Style untuk list agar lebih rapi */
        .accordion-body ul { padding-left: 20px; margin-top: 5px; margin-bottom: 15px; }
        .accordion-body li { margin-bottom: 5px; }

    </style>
</head>
<body>
    
    <div class="container">
        <h1>Panduan Aplikasi KBGM</h1>

        <div class="accordion-item">
            <div class="accordion-header">Bab 1: Mendaftarkan Peserta Baru</div>
            <div class="accordion-body">
                <p><strong>Langkah 1.1: Buka menu tambah member</strong></p>
                <ul>
                    <li>Klik pada menu "Member" di navigasi bar.</li>
                    <li>Pilih opsi "Tambah Member" (kotak hijau pada gambar).</li>
                </ul>
                <img src="assets/gambar/navbar_member.jpg" alt="Screenshot navbar">
                
                <p><strong>Langkah 1.2: Lihat Halaman Tambah Member</strong></p>
                <img src="assets/gambar/tambah_member.jpg" alt="Screenshot formulir tambah member">

                <p><strong>Langkah 1.3: Isi formulir sesuai data peserta</strong></p>
                <p>Ketika mengisi data, pastikan:</p>
                <ul>
                    <li><strong>Poin 5 (Tanggal Lahir/kotak biru):</strong> Ketik manual (DD-MM-YYYY) atau klik ikon kalender.</li>
                    <li><strong>Poin 7 (Waktu Bergabung/kotak biru):</strong> Klik ikon kalender untuk memilih "today" atau sesuaikan tanggalnya.</li>
                    <li><strong>Poin 9 (Hubungan dalam Keluarga/kotak hijau):</strong> Pilih sesuai dengan hubungan pada Kartu Keluarga.</li> 
                </ul>
                
                <p><strong>Langkah 1.4: Simpan Data</strong></p>
                <p>Pastikan semua data sudah benar, lalu klik tombol "Simpan Member".</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">Bab 2: Halaman List Member KBGM Yang Terdaftar</div>
            <div class="accordion-body">
                <p><strong>Langkah 2.1: Buka daftar member</strong></p>
                <ul>
                    <li>Klik pada menu "Member" di navigasi bar.</li>
                    <li>Pilih opsi "Daftar Member" (kotak merah pada gambar).</li>
                </ul>
                <img src="assets/gambar/navbar_member.jpg" alt="Screenshot navbar">
                
                <p><strong>Langkah 2.2: Memahami Tampilan Daftar Member</strong></p>
                <img src="assets/gambar/daftar_member.jpg" alt="Screenshot daftar member">
                <p>Penjelasan:</p>
                <ul>
                    <li><strong>Kotak Biru:</strong> Kolom pencarian. Cari berdasarkan No. Peserta, Nama, atau No. KK. Klik tombol "Cari" setelah mengisi.</li>
                    <li><strong>Kotak Hijau:</strong> Status "Terverifikasi", menandakan member sudah dicocokkan.</li>
                    <li><strong>Kotak Oranye:</strong> Status "Belum Verifikasi", menandakan member perlu dicocokkan (lihat Bab 3).</li>
                    <li><strong>Panah Merah (1):</strong> Tombol untuk mengedit data member.</li>
                    <li><strong>Panah Merah (2):</strong> Tombol untuk melihat riwayat kunjungan member.</li>
                    <li><strong>Panah Merah (3):</strong> Tombol untuk mencetak kartu member.</li>
                </ul>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">Bab 3: Verifikasi & Matching Member KBGM</div>
            <div class="accordion-body">
                <div class="kotak-penting">
                    <h4>Mengapa Matching Data Itu WAJIB?</h4>
                    <p>Melewatkan langkah ini akan menyebabkan data riwayat medis pasien tidak muncul saat kunjungan. Selalu lakukan verifikasi!</p>
                </div>
                
                <p><strong>Langkah 3.1: Periksa Status Member</strong></p>
                <p>Setelah menyimpan peserta baru, periksa kolom status di halaman daftar member.</p>
                <ul>
                    <li><strong>Kotak Oranye ("Belum Verifikasi"):</strong> Data baru yang perlu dicocokkan. Klik tombol "Cocokkan".</li>
                    <li><strong>Kotak Hijau ("Terverifikasi"):</strong> Data yang sudah dicocokkan.</li>
                </ul>
                <img src="assets/gambar/daftar_member.jpg" alt="Screenshot tombol matching data">
                
                <p><strong>Langkah 3.2: Proses Pencocokan Data</strong></p>
                <p>Setelah mengklik "Cocokkan", sistem akan mencari data pasien di database rumah sakit.</p>
                
                <p><strong>- Tampilan ketika data pasien TIDAK ditemukan:</strong></p>
                <img src="assets/gambar/matching_x.jpg" alt="Screenshot proses matching data tidak ditemukan">
                
                <p><strong>- Tampilan ketika data pasien DITEMUKAN:</strong></p>
                <img src="assets/gambar/matching_y.jpg" alt="Screenshot proses matching data ditemukan">
                
                <p><strong>Langkah 3.3: Simpan Pencocokan</strong></p>
                <p>Jika data yang cocok ditemukan dan sudah benar, klik tombol "Simpan Pencocokan" untuk menyelesaikan proses verifikasi.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">Bab 4: Mencetak Kartu KBGM</div>
            <div class="accordion-body">

                <p><strong>Langkah 4.0: Buka Daftar Member</strong></p>
                <p>Perhatikan Gambar Berikut: </p>
                <img src="assets/gambar/download_option.jpg" alt="Screenshot opsi download kartu">

                <p><strong>Langkah 4.1: Cetak Member</strong></p>
                <p>Cari member yang ingin dicetak kartunya, lalu klik ikon cetak (kotak merah pada gambar di langkah 4.0).</p>
                
                <p><strong>Langkah 4.2: Pilih Format Kartu</strong></p>
                <p>Anda akan diberikan pilihan format untuk mengunduh kartu.</p>
                <ul>
                    <li><strong>Format JPEG:</strong> Kartu akan diunduh sebagai file gambar.</li>
                    <li><strong>Format PDF:</strong> Kartu akan diunduh sebagai file PDF.</li>
                </ul>
                
                <p><strong>Langkah 4.3: Unduh Kartu</strong></p>
                <p>Setelah memilih format, kartu akan otomatis terunduh ke perangkat Anda.</p>

                <p><strong>Langkah 4.4: Cetak Kartu</strong></p>
                <p>Buka Aplikasi yang digunakan untuk menghubungkan printer, lalu pilih file JPEG yang telah diunduh. Pastikan printer terhubung dan kertas label tersedia.</p>
                <div class="kotak-penting">
                <h4>Catatan Penting:</h4>
                <p>Pastikan ukuran kertas label yang digunakan sesuai dengan format yang dipilih untuk menghindari masalah saat mencetak. Ukuran yang digunakan: </p>
                <ul>
                    <li><strong>Format JPEG:</strong> lebar 500px dan tinggi 200px.</li>
                    <li><strong>Format PDF:</strong> lebar 50mm dan tinggi 20mm.</li>
                </ul>
                <p><em>(Jika mengalami kesulitan, silakan hubungi tim IT.)</em></p>
                </div>
            </div>
        </div>

    </div>

    <script>
        // JavaScript sederhana untuk fungsionalitas accordion
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                item.classList.toggle('active');
            });
        });
    </script>

</body>
</html>