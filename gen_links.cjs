const zlib = require('zlib');

function encodeMermaid(text) {
    const data = Buffer.from(text, 'utf8');
    const compressed = zlib.deflateSync(data, { level: 9 });
    const encoded = compressed.toString('base64')
        .replace(/\+/g, '-')
        .replace(/\//g, '_');
    return 'https://mermaid.ink/img/pako:' + encoded;
}

const uc = `flowchart LR
    Admin([Admin / Pelatih])
    Wali([Wali Murid])
    subgraph Sistem["Sistem Monitoring Kelas Catur"]
        direction TB
        UC1([Login])
        UC2([Kelola Data Siswa])
        UC3([Kelola Jadwal Latihan])
        UC4([Input Absensi])
        UC5([Input Materi & Nilai])
        UC6([Cetak Rapot Siswa])
        UC7([Lihat Grafik Nilai & Absensi])
        UC8([Unduh Rapot Anak])
    end
    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Wali --> UC1
    Wali --> UC7
    Wali --> UC8`;

const act = `flowchart TD
    A([Mulai]) --> B[Buka Halaman Input Nilai]
    B --> C[Pilih Nama Siswa]
    C --> D[Isi Materi, Skor Taktik, & Skor Sparing]
    D --> E{Apakah Data Valid?}
    E -- Tidak --> F[Tampilkan Pesan Error]
    F --> D
    E -- Ya --> G[Simpan ke Database]
    G --> H[Tampilkan Notifikasi Berhasil]
    H --> I([Selesai])`;

const seq = `sequenceDiagram
    actor Wali as Wali Murid
    participant View as Halaman Portal (UI)
    participant Ctrl as Controller Laporan
    participant DB as Database (MySQL)
    Wali->>View: 1. Klik tombol "Unduh Rapot"
    View->>Ctrl: 2. Request cetak (Kirim ID Siswa)
    Ctrl->>DB: 3. Query SELECT data absensi & nilai
    DB-->>Ctrl: 4. Return data siswa
    Ctrl->>Ctrl: 5. Generate format PDF
    Ctrl-->>View: 6. Kirim file PDF Rapot
    View-->>Wali: 7. File berhasil diunduh (Save As)`;

const cls = `classDiagram
    class User {
        +int id_user
        +String username
        +String password
        +String role
        +login()
        +logout()
    }
    class Siswa {
        +int id_siswa
        +String nis
        +String nama_lengkap
        +Date tgl_lahir
        +String alamat
        +tambahSiswa()
        +editSiswa()
        +hapusSiswa()
    }
    class Absensi {
        +int id_absensi
        +int id_siswa
        +Date tanggal
        +String status
        +catatHadir()
        +rekapAbsensi()
    }
    class Penilaian {
        +int id_nilai
        +int id_siswa
        +Date tanggal
        +String materi
        +int skor_taktik
        +int skor_sparing
        +String catatan_pelatih
        +inputNilai()
        +generateRapot()
    }
    User "1" -- "*" Siswa : mengelola (Admin)
    User "1" -- "1" Siswa : memantau (Wali)
    Siswa "1" *-- "*" Absensi : memiliki
    Siswa "1" *-- "*" Penilaian : mendapatkan`;

console.log('UC|' + encodeMermaid(uc));
console.log('ACT|' + encodeMermaid(act));
console.log('SEQ|' + encodeMermaid(seq));
console.log('CLS|' + encodeMermaid(cls));
