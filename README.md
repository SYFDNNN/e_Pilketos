# e-Pilketos: Sistem E-Voting OSIS Berbasis Web dengan Docker 

Selamat datang di repositori e-Pilketos! Ini adalah sebuah aplikasi web yang dirancang untuk memfasilitasi pemilihan ketua OSIS secara online. Aplikasi ini dibangun dengan tumpukan teknologi PHP & MySQL dan dikemas sepenuhnya menggunakan Docker, sehingga proses instalasi dan deployment menjadi sangat mudah dan konsisten di berbagai lingkungan.

![Screenshot Halaman Hasil e-Pilketos](https://drive.google.com/uc?export=view&id=10gQc3BVtJh52Nm7-91F3CJjejU3Nw7CO)

## Fitur Utama

-   **Dua Peran Pengguna**: Sistem membedakan antara **Admin** dan **Siswa** dengan hak akses yang berbeda.
-   **Panel Admin Komprehensif**:
    -   Mengelola data pengguna (CRUD - Create, Read, Update, Delete).
    -   Mengelola data kandidat (CRUD), termasuk upload foto.
    -   Melihat hasil pemilihan suara secara real-time dengan visualisasi grafik.
-   **Fitur untuk Siswa**:
    -   Registrasi dan Login yang aman.
    -   Melihat profil lengkap setiap kandidat (visi, misi, foto).
    -   Memberikan suara (hanya satu kali per siswa).
    -   Melihat hasil perolehan suara secara publik.
-   **Keamanan**: Menggunakan *prepared statements* untuk mencegah SQL Injection dan *password hashing* untuk keamanan kata sandi.

## Tumpukan Teknologi (Tech Stack)

Aplikasi ini dibangun menggunakan teknologi berikut:

-   **Backend**: PHP 8.1
-   **Database**: MySQL 8.0
-   **Web Server**: Apache
-   **Frontend**: HTML, CSS, JavaScript (Vanilla)
-   **Kontainerisasi**: Docker & Docker Compose

## Prasyarat

Sebelum memulai, pastikan perangkat Anda sudah terinstal:
-   [Docker](https://www.docker.com/products/docker-desktop/)
-   [Docker Compose](https://docs.docker.com/compose/install/) (biasanya sudah termasuk dalam Docker Desktop)
-   [Git](https://git-scm.com/)

## Instalasi & Penyiapan Lokal (Cara Otomatis)

Proyek ini menyertakan sebuah skrip untuk mengotomatiskan seluruh proses instalasi. Cukup ikuti langkah-langkah berikut:

**1. Clone Repositori**
Buka terminal Anda dan jalankan perintah berikut:
```bash
git clone https://github.com/SYFDNNN/e_Pilketos.git
cd e-Pilketos
```


**2. Jadikan Skrip Dapat Dieksekusi**
(Hanya perlu dilakukan sekali) Berikan izin eksekusi pada skrip setup.sh:

```bash
chmod +x setup.sh
```

**3. Jalankan Skrip Setup**
Eksekusi skrip untuk memulai instalasi:

```bash
./setup.sh
```

Skrip akan secara otomatis memeriksa prasyarat, membuat file .env yang dibutuhkan, serta membangun dan menjalankan semua kontainer Docker.

**4. Selesai!**
Lingkungan pengembangan Anda sudah siap!

## Penggunaan & Kredensial Default
Setelah semua kontainer berjalan, Anda bisa mengakses aplikasi:

- Aplikasi Utama: Buka browser dan kunjungi ****```bash http://localhost ```****

- phpMyAdmin (untuk manajemen database): Kunjungi ****```bash http://localhost:8080 ```****

Kredensial Default:
Database sudah diisi dengan satu akun admin secara default.

- Username: ****```bash admin ```****

- Password: ****```bash admin ```****

Untuk pengguna Siswa, silakan gunakan fitur registrasi yang tersedia di halaman utama untuk membuat akun baru.
