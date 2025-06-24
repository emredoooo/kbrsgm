// cekkoneksi
function cekkoneksi() {
    Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
    }).fire({
        title: 'Mengecek koneksi...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../cekkoneksi.php') 
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        Swal.close(); 
        if (data.status === 'success') {
            Swal.mixin({
                toast: true,
                position: 'top',
                iconColor: 'green',
                customClass: { popup: 'colored-toast' },
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).fire({
                icon: 'success',
                title: 'Koneksi berhasil broo'
            });
        } else {
            Swal.mixin({
                toast: true,
                position: 'top',
                iconColor: 'red',
                customClass: { popup: 'colored-toast' },
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).fire({
                icon: 'error',
                title: 'Waduh koneksi gagal nih :('
            });
        }
    })
    .catch(error => {
        // Tutup loading SweetAlert jika terjadi error
        Swal.close(); 
        console.error("Error checking connection:", error);
        Swal.mixin({
            toast: true,
            position: 'top',
            iconColor: 'pink',
            customClass: { popup: 'colored-toast' },
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        }).fire({
            icon: 'error',
            title: 'Aku gagal ngecek koneksi nih',
            text: 'Coba bilang mamas IT suruh cek aku yaa :('
        });
    });
}

// Fungsi untuk mendapatkan kode wilayah dari NIK
function getKodeWilayahFromNIK(nik) {
    // Ambil 2 digit setelah kode provinsi (indeks 2 dan panjang 2)
    // Ingat, string indeks di JavaScript dimulai dari 0
    const kode = nik.substring(2, 4); 

    const mapping = {
        '01': 'Lampung Selatan (LS)',
        '02': 'Lampung Tengah (LT)',
        '03': 'Lampung Utara (LU)',
        '04': 'Lampung Barat (LB)',
        '05': 'Tulang Bawang (TL)',
        '06': 'Tanggamus (TG)',
        '07': 'Lampung Timur (LM)',
        '08': 'Way Kanan (WK)',
        '09': 'Pesawaran (PS)',
        '10': 'Pringsewu (PG)',
        '11': 'Mesuji (MS)',
        '12': 'Tulang Bawang Barat (TB)',
        '13': 'Pesisir Barat (PB)',
        '71': 'Kota Bandar Lampung (BL)',
        '72': 'Kota Metro (MT)',
    };

    // Menggunakan operator nullish coalescing (??) untuk fallback ke 'XX'
    // Jika mapping[kode] undefined, maka gunakan 'XX'
    return mapping[kode] ?? 'XX'; 
}

// Contoh penggunaan di JavaScript (misalnya saat input NIK berubah):
// const inputNIK = document.getElementById('nikInput');
// inputNIK.addEventListener('input', function() {
//     const nikValue = this.value;
//     const kodeWilayah = getKodeWilayahFromNIK(nikValue);
//     console.log("Kode Wilayah (JS):", kodeWilayah);
// });

//mencetak member
function cetakMember(buttonElement, no_kbgm, nama, no_hp) {
    Swal.fire({
        title: 'Pilih Opsi Cetak',
        html: `Anda ingin:`,
        icon: 'info',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Cetak Langsung', // Ganti teks
        denyButtonText: `Buka PDF`, // Jika ingin tetap ada opsi PDF biasa
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Pengguna memilih 'Cetak Langsung' via Bluetooth Print App

            // Buat URL halaman respons PHP Anda
            const responseUrl = `http://localhost/kbgm-v2/kbgm/print_response.php?no_kbgm=${encodeURIComponent(no_kbgm)}`;

            // Buat link skema khusus untuk meluncurkan aplikasi Bluetooth Print 
            const appSchemeUrl = `my.bluetoothprint.scheme://${encodeURIComponent(responseUrl)}`;

            // Buka URL skema. Ini akan mencoba meluncurkan aplikasi "Bluetooth Print".
            window.location.href = appSchemeUrl;

            // Beri umpan balik ke pengguna
            Swal.fire(
                'Mengirim Perintah Cetak!',
                'Aplikasi cetak akan diluncurkan. Pastikan printer terhubung.',
                'info'
            );

        } else if (result.isDenied) {
            // Jika Anda masih ingin opsi untuk mengunduh/melihat PDF biasa yang dibuat jsPDF
            // Ini akan kembali ke implementasi jsPDF sebelumnya
            const { jsPDF } = window.jspdf;
            // Penting: Jika Anda ingin format 50x20mm, pastikan Anda menggunakan ini lagi di sini
            const doc = new jsPDF({
                unit: 'mm',
                format: [20, 50]
            });

            doc.setFontSize(5); // Sesuaikan font size
            doc.text(`Nama: ${nama}`, 2, 5);
            doc.text(`No. KBGM: ${no_kbgm}`, 2, 9);
            doc.text(`No. HP: ${no_hp}`, 2, 13);
            // ... tambahkan konten PDF sesuai kebutuhan

            const pdfBlob = doc.output('blob');
            const pdfUrl = URL.createObjectURL(pdfBlob);

            const newWindow = window.open(pdfUrl, '_blank');
            if (newWindow) {
                newWindow.focus();
                Swal.fire({
                    title: 'Pratinjau Dokumen Dibuka',
                    html: 'Dokumen PDF telah dibuka di tab/jendela baru.<br>Silakan gunakan fungsi cetak bawaan browser Anda.',
                    icon: 'info',
                    showConfirmButton: true,
                    confirmButtonText: 'Tutup'
                });
            } else {
                Swal.fire(
                    'Gagal Membuka Pratinjau',
                    'Browser Anda memblokir popup. Harap izinkan popup untuk situs ini atau coba opsi "Simpan PDF".',
                    'error'
                );
            }
        }
    });
}