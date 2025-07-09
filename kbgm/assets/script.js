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

// Fungsi untuk download PDF langsung ke perangkat
function downloadPdf(no_kbgm, nama, no_hp) { // Hilangkan buttonElement dari parameter jika tidak digunakan
    Swal.fire({
        title: 'Membuat PDF...',
        html: 'Mohon tunggu, dokumen PDF sedang dibuat.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        unit: 'mm',
        orientation: 'landscape',
        format: [50, 20] // Lebar 50mm, Tinggi 20mm (Landscape)
    });

    const cardWidth = 50;
    const marginX = 2;
    const textStartX = marginX;
    const maxTextWidth = cardWidth - (2 * marginX);

    let yOffset = 5;
    const defaultFontSize = 5;

    doc.setFontSize(defaultFontSize);

    const nameLabel = 'Nama: ';
    let displayName = nama;

    const smartTruncateNamePdf = (fullName, maxWidth, fontSize) => {
        const words = fullName.split(' ').filter(w => w.length > 0);
        if (words.length === 0) return fullName;

        doc.setFontSize(fontSize);

        let tempText = nameLabel + fullName;
        let currentWidth = doc.getStringUnitWidth(tempText) * fontSize / doc.internal.scaleFactor;
        if (currentWidth <= maxWidth) {
            return fullName;
        }

        if (words.length >= 2) {
            tempText = nameLabel + words[0] + ' ' + words[words.length - 1];
            currentWidth = doc.getStringUnitWidth(tempText) * fontSize / doc.internal.scaleFactor;
            if (currentWidth <= maxWidth) {
                return words[0] + ' ' + words[words.length - 1];
            }
        }
        
        if (words.length >= 2) {
            tempText = nameLabel + words[0] + ' ' + words[words.length - 1].charAt(0) + '.';
            currentWidth = doc.getStringUnitWidth(tempText) * fontSize / doc.internal.scaleFactor;
            if (currentWidth <= maxWidth) {
                return words[0] + ' ' + words[words.length - 1].charAt(0) + '.';
            }
        }

        let truncated = words[0];
        tempText = nameLabel + truncated + '...';
        currentWidth = doc.getStringUnitWidth(tempText) * fontSize / doc.internal.scaleFactor;
        while (currentWidth > maxWidth && truncated.length > 0) {
            truncated = truncated.substring(0, truncated.length - 1);
            tempText = nameLabel + truncated + '...';
            currentWidth = doc.getStringUnitWidth(tempText) * fontSize / doc.internal.scaleFactor;
        }
        return truncated + '...';
    };

    displayName = smartTruncateNamePdf(nama, maxTextWidth - doc.getStringUnitWidth(nameLabel) * defaultFontSize / doc.internal.scaleFactor, defaultFontSize);
    
    const finalNameText = nameLabel + displayName;

    const splitName = doc.splitTextToSize(finalNameText, maxTextWidth);
    doc.text(splitName, textStartX, yOffset);
    yOffset += (splitName.length * defaultFontSize / 2.8) + 2;

    doc.text(`No. KBGM: ${no_kbgm}`, textStartX, yOffset);
    yOffset += (defaultFontSize / 2.8) + 2;

    doc.text(`No. HP: ${no_hp}`, textStartX, yOffset);
    yOffset += (defaultFontSize / 2.8) + 2;

    doc.save(`KBGM_Member_${no_kbgm}.pdf`);

    Swal.close();
    Swal.fire({
        title: 'Download Dimulai!',
        text: 'Dokumen PDF Anda sedang diunduh.',
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    });
}

// Fungsi untuk download JPEG langsung ke perangkat
function downloadJpeg(no_kbgm, nama, no_hp) { // Hilangkan buttonElement dari parameter jika tidak digunakan
    Swal.fire({
        title: 'Membuat Gambar JPEG...',
        html: 'Mohon tunggu, gambar sedang dibuat.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const imgWidthPx = 500;
    const imgHeightPx = 200;

    const canvas = document.createElement('canvas');
    canvas.width = imgWidthPx;
    canvas.height = imgHeightPx;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const paddingPx = 15;
    const textStartX = paddingPx;
    const maxTextWidthPx = imgWidthPx - (2 * paddingPx);

    let yOffsetPx = 30;

    const defaultFontSizePx = 25;
    ctx.font = `bold ${defaultFontSizePx}px Arial`;
    ctx.fillStyle = 'black';

    const nameLabel = 'Nama: ';
    let displayName = nama;

    const smartTruncateNameCanvas = (fullName, maxWidthPxMeasure, fontSizePx) => {
        const words = fullName.split(' ').filter(w => w.length > 0);
        if (words.length === 0) return fullName;

        ctx.font = `bold ${fontSizePx}px Arial`;

        let tempText = nameLabel + fullName;
        let currentWidthPxMeasure = ctx.measureText(tempText).width;
        if (currentWidthPxMeasure <= maxWidthPxMeasure) {
            return fullName;
        }

        if (words.length >= 2) {
            tempText = nameLabel + words[0] + ' ' + words[words.length - 1];
            currentWidthPxMeasure = ctx.measureText(tempText).width;
            if (currentWidthPxMeasure <= maxWidthPxMeasure) {
                return words[0] + ' ' + words[words.length - 1];
            }
        }
        
        if (words.length >= 2) {
            tempText = nameLabel + words[0] + ' ' + words[words.length - 1].charAt(0) + '.';
            currentWidthPxMeasure = ctx.measureText(tempText).width;
            if (currentWidthPxMeasure <= maxWidthPxMeasure) {
                return words[0] + ' ' + words[words.length - 1].charAt(0) + '.';
            }
        }

        let truncated = words[0];
        tempText = nameLabel + truncated + '...';
        currentWidthPxMeasure = ctx.measureText(tempText).width;
        while (currentWidthPxMeasure > maxWidthPxMeasure && truncated.length > 0) {
            truncated = truncated.substring(0, truncated.length - 1);
            tempText = nameLabel + truncated + '...';
            currentWidthPxMeasure = ctx.measureText(tempText).width;
        }
        return truncated + '...';
    };

    displayName = smartTruncateNameCanvas(nama, maxTextWidthPx, defaultFontSizePx);
    
    const splitName = [];
    let currentLine = '';
    const wordsInFinalName = (nameLabel + displayName).split(' ');
    for (const word of wordsInFinalName) {
        const testLine = currentLine === '' ? word : currentLine + ' ' + word;
        if (ctx.measureText(testLine).width > maxTextWidthPx && currentLine !== '') {
            splitName.push(currentLine);
            currentLine = word;
        } else {
            currentLine = testLine;
        }
    }
    splitName.push(currentLine);

    for (const line of splitName) {
        ctx.fillText(line, textStartX, yOffsetPx);
        yOffsetPx += defaultFontSizePx + 8;
    }

    ctx.font = `bold ${defaultFontSizePx}px Arial`;
    ctx.fillText(`No. KBGM: ${no_kbgm}`, textStartX, yOffsetPx);
    yOffsetPx += defaultFontSizePx + 8;

    ctx.fillText(`No. HP: ${no_hp}`, textStartX, yOffsetPx);
    yOffsetPx += defaultFontSizePx + 8;

    canvas.toBlob((blob) => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `KBGM_Member_${no_kbgm}.jpeg`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        Swal.close();
        Swal.fire({
            title: 'Download Dimulai!',
            text: 'Dokumen JPEG Anda sedang diunduh.',
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }, 'image/jpeg', 0.9);
}

// NEW: Fungsi untuk menampilkan opsi download (PDF atau JPEG)
function showDownloadOptions(buttonElement, no_kbgm, nama, no_hp) {
    Swal.fire({
        title: 'Pilih Format Download',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Download PDF',
        denyButtonText: 'Download JPEG',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Pengguna memilih Download PDF
            downloadPdf(no_kbgm, nama, no_hp);
        } else if (result.isDenied) {
            // Pengguna memilih Download JPEG
            downloadJpeg(no_kbgm, nama, no_hp);
        }
        // Jika batal, tidak ada aksi
    });
}