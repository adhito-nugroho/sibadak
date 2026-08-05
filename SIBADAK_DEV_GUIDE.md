# SIBADAK — Panduan Pengembangan Aplikasi
**Sistem Informasi Basis Data Kehutanan**  
CDK Wilayah Bojonegoro · Dinas Kehutanan Provinsi Jawa Timur

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Arsitektur & Stack Teknologi](#2-arsitektur--stack-teknologi)
3. [Struktur Direktori](#3-struktur-direktori)
4. [Setup Lingkungan Pengembangan](#4-setup-lingkungan-pengembangan)
5. [Database MySQL](#5-database-mysql)
6. [Modul Aplikasi](#6-modul-aplikasi)
7. [Panduan Pengembangan dengan AI](#7-panduan-pengembangan-dengan-ai)
8. [Prompt Siap Pakai per Modul](#8-prompt-siap-pakai-per-modul)
9. [Konvensi Kode](#9-konvensi-kode)
10. [Keamanan](#10-keamanan)
11. [Checklist Pengembangan](#11-checklist-pengembangan)
12. [Referensi File](#12-referensi-file)

---

## 1. Gambaran Umum

### Tentang Aplikasi

SIBADAK adalah aplikasi web berbasis **PHP + MySQL** untuk mengelola data Kelompok Tani Hutan (KTH) di bawah naungan CDK Wilayah Bojonegoro, mencakup **4 Kabupaten**: Bojonegoro, Tuban, Lamongan, dan Gresik.

### Cakupan Data

| Modul | Deskripsi | Sheet Sumber |
|-------|-----------|--------------|
| KTH | Data kelompok tani hutan beserta kelas | KTH Bojonegoro/Tuban/Lamongan/Gresik |
| Kelembagaan | Pengurus & anggota (NIK, KK, Koordinat) | Kelembagaan |
| KPS | Kelompok Perhutanan Sosial (HKm/HD/HTR/Kulin KK/IPHPS) | Data KPS |
| RKT | Rencana Kerja Tahunan KPS 2023–2030 | RKT |
| RHL | Rehabilitasi Hutan & Lahan | RHL |
| KBR | Kebun Bibit Rakyat | KBR |
| AEP | Alat & Sarana Ekonomi Produktif | AEP |
| HHK | Hasil Hutan Kayu | HHK |
| HHBK | Hasil Hutan Bukan Kayu | HHBK |
| MPTS | Multi-Purpose Tree Species | MPTS |
| NTE | Nilai Transaksi Ekonomi (bulanan) | NTE |
| Jasa Lingkungan | Wisata, air, karbon, kehati | Jasa Lingkungan |
| DPN | Dam Penahan | DPN |
| Gully Plug | Gully Plug | Gully Plug |
| UPSA | Usaha Produktif Skala Kecil | UPSA |

### Target Pengguna

- **Admin** — akses penuh semua data dan kabupaten
- **Operator** — input & edit data, terbatas per kabupaten
- **Viewer** — hanya baca & export laporan

---

## 2. Arsitektur & Stack Teknologi

```
┌─────────────────────────────────────────────────┐
│                  BROWSER                        │
│         HTML + Tailwind CSS + JS                │
└──────────────────┬──────────────────────────────┘
                   │ HTTP
┌──────────────────▼──────────────────────────────┐
│                PHP 8.2+                         │
│   Router → Controller → Model → View           │
│         (arsitektur MVC ringan)                 │
└──────────────────┬──────────────────────────────┘
                   │ PDO
┌──────────────────▼──────────────────────────────┐
│              MySQL 8.0+                         │
│          db_kth_cdk_bjn                         │
└─────────────────────────────────────────────────┘
```

### Stack yang Digunakan

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Backend | PHP | 8.2+ |
| Database | MySQL | 8.0+ |
| Frontend CSS | Tailwind CSS | CDN 3.x |
| Font | Syne + DM Sans | Google Fonts |
| Ikon | Heroicons (SVG inline) | 2.x |
| Chart | Chart.js | 4.x CDN |
| Tabel | DataTables.js | 1.13 CDN |
| Export Excel | PhpSpreadsheet | 1.x Composer |
| Export PDF | TCPDF atau DomPDF | Composer |
| Session/Auth | PHP Native Session | — |
| Web Server | Apache / Nginx | — |

---

## 3. Struktur Direktori

```
sibadak/
├── index.php                   # Entry point & router utama
├── .htaccess                   # URL rewriting (Apache)
├── config/
│   ├── database.php            # Koneksi PDO MySQL
│   ├── config.php              # Konstanta global (APP_URL, dll)
│   └── auth.php                # Helper session & role check
├── controllers/
│   ├── AuthController.php      # Login, logout, ganti password
│   ├── DashboardController.php # Rekap & statistik
│   ├── KthController.php       # CRUD KTH
│   ├── KelembagaanController.php
│   ├── KpsController.php
│   ├── RhlController.php
│   ├── KbrController.php
│   ├── AepController.php
│   ├── HhkController.php
│   ├── HhbkController.php
│   ├── MptsController.php
│   ├── NteController.php
│   ├── JasaLingkunganController.php
│   ├── DpnController.php
│   ├── GullyPlugController.php
│   ├── UpsaController.php
│   ├── PetaController.php
│   ├── LaporanController.php
│   └── UserController.php
├── models/
│   ├── BaseModel.php           # Abstrak, PDO wrapper
│   ├── Kth.php
│   ├── KthAnggota.php
│   ├── Kps.php
│   ├── KpsRkt.php
│   ├── Rhl.php
│   ├── Kbr.php
│   ├── Aep.php
│   ├── Hhk.php
│   ├── Hhbk.php
│   ├── Mpts.php
│   ├── Nte.php
│   ├── JasaLingkungan.php
│   ├── Dpn.php
│   ├── GullyPlug.php
│   ├── Upsa.php
│   └── User.php
├── views/
│   ├── layouts/
│   │   ├── main.php            # Layout utama (sidebar + topbar)
│   │   └── auth.php            # Layout halaman login
│   ├── partials/
│   │   ├── sidebar.php
│   │   ├── topbar.php
│   │   └── flash.php           # Pesan sukses/error
│   ├── dashboard/
│   │   └── index.php
│   ├── auth/
│   │   └── login.php
│   ├── kth/
│   │   ├── index.php           # Daftar KTH (DataTables)
│   │   ├── create.php          # Form tambah KTH
│   │   ├── edit.php            # Form edit KTH
│   │   └── show.php            # Detail KTH + semua tab
│   ├── kps/
│   ├── rhl/
│   ├── nte/
│   ├── laporan/
│   │   ├── index.php
│   │   ├── rekap_kth.php
│   │   └── rekap_nte.php
│   └── users/
├── assets/
│   ├── css/
│   │   └── custom.css          # Override Tailwind jika perlu
│   ├── js/
│   │   ├── app.js              # Inisialisasi global
│   │   ├── charts.js           # Konfigurasi Chart.js
│   │   └── maps.js             # Leaflet.js untuk peta
│   └── img/
│       └── logo.png
├── helpers/
│   ├── functions.php           # Helper global (format Rp, format Ha, dll)
│   ├── validation.php          # Validasi input form
│   └── export.php              # Export Excel & PDF
├── vendor/                     # Composer autoload
├── composer.json
└── SIBADAK_DEV_GUIDE.md       # File ini
```

---

## 4. Setup Lingkungan Pengembangan

### Prasyarat

```bash
# Cek versi
php --version       # >= 8.2
mysql --version     # >= 8.0
composer --version  # >= 2.x
```

### Langkah Instalasi

```bash
# 1. Clone / buat folder proyek
mkdir sibadak && cd sibadak

# 2. Install dependensi Composer
composer require phpoffice/phpspreadsheet
composer require tecnickcom/tcpdf
composer require vlucas/phpdotenv

# 3. Copy .env
cp .env.example .env
# Edit .env: isi DB_HOST, DB_NAME, DB_USER, DB_PASS

# 4. Import database
mysql -u root -p < database_kth_cdk_bjn.sql

# 5. Pastikan mod_rewrite aktif (Apache)
a2enmod rewrite
service apache2 restart
```

### File `.env`

```ini
APP_NAME=SIBADAK
APP_TAGLINE="Sistem Informasi Basis Data Kehutanan"
APP_URL=http://localhost:8000/sibadak
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_NAME=db_kth_cdk_bjn
DB_USER=root
DB_PASS=

SESSION_LIFETIME=7200
TIMEZONE=Asia/Jakarta
```

### Koneksi Database (`config/database.php`)

```php
<?php
class Database {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']
            );
            self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
```

---

## 5. Database MySQL

> File lengkap: **`database_kth_cdk_bjn.sql`**

### Tabel Utama & Relasi

```
kabupaten (4 baris seed)
    └── kecamatan
            └── desa
                    └── kth ──────────────── kth_anggota
                    │                        hhk → hhk_detail
                    │                        hhbk → hhbk_detail
                    │                        mpts
                    │                        nte
                    │                        jasa_lingkungan
                    └── kps ──────────────── kps_rkt
                            └─────────────── kps_anggota
                            rhl → rhl_bibit
                            kbr → kbr_tanaman
                            aep
                            dpn
                            gully_plug
                            upsa
roles
    └── users → activity_log
```

### Views Tersedia

| View | Kegunaan |
|------|----------|
| `v_rekap_kth_per_kabupaten` | Jumlah KTH per kabupaten & kelas |
| `v_rekap_rhl_per_tahun` | Luas RHL per kegiatan per tahun |
| `v_rekap_kps` | KPS per kabupaten & skema |
| `v_nte_tahunan` | Total transaksi per KTH per tahun |

### Query Penting

```sql
-- Dashboard: rekap cepat
SELECT k.nama, COUNT(kth.id) AS total
FROM kth JOIN kabupaten k ON kth.kabupaten_id = k.id
GROUP BY k.id;

-- KTH dengan ketua
SELECT kth.nama, kth.kelas, a.nama AS ketua, kth.jumlah_anggota
FROM kth
LEFT JOIN kth_anggota a ON a.kth_id = kth.id AND a.posisi = 'Ketua'
WHERE kth.kabupaten_id = ?;

-- NTE bulanan per KTH
SELECT bulan, SUM(nilai_rp) AS total
FROM nte WHERE kth_id = ? AND tahun = ?
GROUP BY bulan ORDER BY bulan;
```

---

## 6. Modul Aplikasi

### 6.1 Autentikasi

- **Login** dengan username + password (bcrypt)
- **Session** PHP native dengan regenerasi ID
- **Role-based access**: admin / operator / viewer
- **Middleware** cek session di setiap controller

```php
// config/auth.php — cek session
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        die('Akses ditolak');
    }
}
```

### 6.2 Dashboard

Tampilkan kartu statistik dari **Views** MySQL:

- Total KTH, KPS, Anggota, Luas RHL
- Bar chart distribusi KTH per kabupaten (Chart.js)
- Donut skema KPS
- Progress bar kelas KTH
- Tabel 10 KTH terbaru
- KPS terbaru

### 6.3 Data KTH (CRUD Utama)

**Halaman Daftar** — DataTables dengan fitur:
- Filter per kabupaten, kecamatan, kelas
- Search nama KTH / kode register
- Export Excel & PDF

**Halaman Detail KTH** — Tab-based:
- Tab Info Umum
- Tab Pengurus & Anggota
- Tab KPS (jika ada)
- Tab RHL
- Tab HHK / HHBK / MPTS
- Tab NTE (grafik bulanan)
- Tab Jasa Lingkungan
- Tab Peta (koordinat)

### 6.4 Laporan & Export

```
Laporan Tersedia:
├── Rekap KTH per Kabupaten (Excel)
├── Rekap KPS per Skema (Excel)
├── Realisasi RHL per Kegiatan (Excel + PDF)
├── Nilai Transaksi Ekonomi Tahunan (Excel)
├── Daftar Anggota per KTH (Excel)
└── Summary Dashboard (PDF)
```

### 6.5 Peta

Gunakan **Leaflet.js** (gratis, open source) untuk menampilkan titik koordinat KTH dan bangunan konservasi (DPN, Gully Plug, UPSA).

---

## 7. Panduan Pengembangan dengan AI

### Cara Terbaik Menggunakan AI di Code Editor

Gunakan **Claude Code** (terminal) atau **ekstensi AI** (VS Code/JetBrains) untuk mempercepat pengembangan. Berikut strategi yang direkomendasikan:

#### A. Konteks Selalu Diberikan

Sebelum meminta kode, pastikan AI punya konteks yang cukup:

```
Selalu sertakan di awal sesi chat AI:
1. Stack: PHP 8.2, MySQL 8.0, Tailwind CSS CDN, tanpa framework
2. Pola arsitektur: MVC ringan, router manual di index.php
3. Nama database & tabel yang relevan
4. Konvensi penamaan yang digunakan
```

#### B. Urutan Pengembangan yang Disarankan

```
Fase 1 — Fondasi (Minggu 1–2)
  ├── Setup folder & .htaccess
  ├── config/database.php, config.php, auth.php
  ├── BaseModel.php
  ├── Layout: main.php, sidebar.php, topbar.php
  └── AuthController + views/auth/login.php

Fase 2 — Modul Inti (Minggu 3–5)
  ├── Master wilayah (kabupaten, kecamatan, desa)
  ├── CRUD KTH lengkap
  ├── Kelembagaan (pengurus & anggota)
  └── Dashboard + Chart.js

Fase 3 — Sub-modul (Minggu 6–9)
  ├── KPS + RKT
  ├── RHL + KBR + AEP
  ├── HHK + HHBK + MPTS
  └── NTE + Jasa Lingkungan

Fase 4 — Output (Minggu 10–11)
  ├── Laporan Excel (PhpSpreadsheet)
  ├── Export PDF (TCPDF)
  └── Peta Leaflet.js

Fase 5 — Finalisasi (Minggu 12)
  ├── Manajemen User
  ├── Activity Log
  ├── Testing & bug fix
  └── Deployment
```

---

## 8. Prompt Siap Pakai per Modul

Salin prompt berikut langsung ke chat AI di code editor Anda. Sesuaikan nama file/tabel jika perlu.

---

### 🔧 Fondasi

#### Router & index.php

```
Buatkan file index.php sebagai router utama untuk aplikasi PHP MVC tanpa framework.
Gunakan $_SERVER['REQUEST_URI'] dan $_SERVER['REQUEST_METHOD'] untuk routing.
Route yang dibutuhkan:
- GET  /              → DashboardController::index
- GET  /login         → AuthController::loginForm
- POST /login         → AuthController::login
- GET  /logout        → AuthController::logout
- GET  /kth           → KthController::index
- GET  /kth/create    → KthController::create
- POST /kth/store     → KthController::store
- GET  /kth/{id}      → KthController::show
- GET  /kth/{id}/edit → KthController::edit
- POST /kth/{id}/update → KthController::update
- POST /kth/{id}/delete → KthController::delete
Sertakan autoload manual (spl_autoload_register) untuk folder controllers/ dan models/.
```

#### BaseModel.php

```
Buatkan abstract class BaseModel untuk PHP 8.2.
Harus punya:
- constructor yang menerima instance PDO
- method findAll(array $conditions = [], string $orderBy = 'id DESC', int $limit = 0): array
- method findById(int $id): array|false
- method create(array $data): int  (return lastInsertId)
- method update(int $id, array $data): bool
- method delete(int $id): bool
- method paginate(int $page, int $perPage = 20, array $conditions = []): array
  (return ['data'=>[], 'total'=>0, 'pages'=>0, 'current'=>1])
- method query(string $sql, array $params = []): array
Semua query menggunakan PDO prepared statements.
Property abstract: protected string $table.
```

---

### 🏠 Dashboard

```
Buatkan DashboardController.php dan views/dashboard/index.php untuk aplikasi SIBADAK.

Stack: PHP 8.2, MySQL, Tailwind CSS CDN (light theme), Chart.js 4 via CDN.
Layout: include views/layouts/main.php.

Controller harus mengambil data dari MySQL:
1. Total KTH (tabel: kth)
2. Total KPS (tabel: kps)
3. Total anggota (SUM jumlah_anggota dari tabel kth)
4. Total luas RHL dalam Ha (SUM luas_ha dari tabel rhl)
5. KTH per kabupaten & kelas: gunakan view v_rekap_kth_per_kabupaten
6. KPS per skema: GROUP BY skema dari tabel kps
7. 7 KTH terbaru: JOIN dengan kabupaten, kecamatan
8. 5 KPS terbaru: JOIN dengan kabupaten, kecamatan

View (index.php) harus menampilkan:
- 4 kartu statistik dengan ikon SVG Heroicons
- Bar chart: KTH per kabupaten (stacked: Utama/Madya/Pemula) dengan Chart.js
- Donut chart: distribusi skema KPS
- Tabel KTH terbaru dengan badge kelas (Utama=hijau, Madya=kuning, Pemula=biru)
- List KPS terbaru

Gunakan palet warna hijau hutan (#3a8f3a) dan cokelat earth (#a87638) sebagai aksen utama.
Sertakan Google Fonts: Syne untuk heading, DM Sans untuk body.
```

---

### 📋 CRUD KTH

#### Controller

```
Buatkan KthController.php untuk CRUD KTH lengkap.
Database: tabel kth, kabupaten, kecamatan, desa.

Method yang dibutuhkan:
- index(): tampilkan daftar dengan filter kabupaten_id, kelas, dan search nama
  → render views/kth/index.php
- create(): form tambah KTH, load data dropdown kabupaten
  → render views/kth/create.php
- store(): validasi & simpan data baru, redirect ke index dengan flash message
- show(int $id): detail KTH beserta anggota (JOIN kth_anggota), render views/kth/show.php
- edit(int $id): form edit, render views/kth/edit.php
- update(int $id): validasi & update, redirect ke show
- delete(int $id): soft-delete (set is_active=0), redirect ke index

Validasi wajib: nama (max 150), kode_register (unik), kelas (enum), kabupaten_id, kecamatan_id, desa_id.
Gunakan helper requireRole('admin','operator') di semua method kecuali index & show.
Catat di activity_log setiap create/update/delete.
```

#### View Daftar (index.php)

```
Buatkan views/kth/index.php untuk menampilkan daftar KTH.

Fitur yang harus ada:
1. Filter bar: dropdown kabupaten, dropdown kelas, input search (submit GET)
2. Tabel DataTables (CDN) dengan kolom:
   No | Nama KTH | Kabupaten | Kecamatan | Desa | Kelas | Anggota | Aksi
3. Badge kelas: Utama=hijau, Madya=kuning, Pemula=biru
4. Tombol aksi: Detail (biru), Edit (abu), Hapus (merah) — Hapus hanya untuk admin
5. Tombol "Tambah KTH" di kanan atas
6. Tombol "Export Excel" dan "Export PDF"
7. Flash message (sukses/error) di atas tabel

Gunakan Tailwind CSS. Ikuti layout main.php (include sidebar dan topbar).
DataTables: language Indonesian, pageLength 25, responsive.
```

#### View Form (create.php & edit.php)

```
Buatkan views/kth/create.php — form tambah KTH baru.

Field yang dibutuhkan (sesuai tabel kth):
- Nama KTH* (text, max 150)
- Kode Register* (text, unik, max 60, contoh: 35/22/01/KTH.001/2020)
- Kabupaten* (select dropdown dari DB)
- Kecamatan* (select, dinamis via AJAX berdasarkan kabupaten)
- Desa* (select, dinamis via AJAX berdasarkan kecamatan)
- Dusun/Blok (text opsional)
- Kelas* (select: Pemula / Madya / Utama)
- Jenis Usaha (text opsional)
- Jumlah Anggota (number, default 0)
- Koordinat LS (decimal, opsional)
- Koordinat BT (decimal, opsional)
- SK Kepala Dinas (text opsional)
- Link SK KTH (url opsional, teks bantu: link Google Drive)
- Keterangan/Catatan (textarea opsional)

Sertakan AJAX untuk load kecamatan dan desa saat kabupaten/kecamatan dipilih.
Endpoint: /api/kecamatan?kabupaten_id=X dan /api/desa?kecamatan_id=X
Validasi client-side minimal (field required, format koordinat desimal).
```

---

### 👥 Kelembagaan (Anggota KTH)

```
Buatkan KelembagaanController.php dan views/kelembagaan/ untuk manajemen anggota KTH.
Tabel: kth_anggota (kolom: id, kth_id, posisi, nama, nik, no_kk, alamat, pekerjaan, gender, no_telpon, luas_garapan_ha, koordinat_ls, koordinat_bt)

Method:
- index(): daftar semua anggota dengan filter kth_id, kabupaten_id, posisi
- store(): tambah anggota, validasi NIK unik
- update(int $id): edit anggota
- delete(int $id): hapus anggota
- importExcel(): upload & parsing file Excel untuk import massal anggota

Untuk halaman daftar: tampilkan tabel dengan DataTables, kolom NIK bisa di-mask (tampilkan 6 digit pertama+***)
Untuk import Excel: gunakan PhpSpreadsheet, baca dari baris ke-2, skip baris kosong.
Sertakan template Excel yang bisa didownload.
```

---

### 🌿 Kelompok Perhutanan Sosial (KPS)

```
Buatkan KpsController.php dan views untuk modul KPS.
Tabel utama: kps, kps_rkt (status RKT per tahun 2023–2030)

Halaman index:
- Filter: kabupaten, skema (HKm/HD/HTR/Kulin KK/IPHPS)
- Tabel: Nama Lembaga | Kabupaten | Kecamatan | Skema | Luas (Ha) | Jml KK | Status RKT | Aksi
- Badge warna per skema: HKm=hijau, HD=cokelat, HTR=biru, Kulin KK=ungu, IPHPS=teal
- Rekap ringkas di atas tabel: total per skema (5 badge dengan angka)

Form tambah/edit KPS:
- Field: kabupaten, kecamatan, desa, skema, nama lembaga, no SK, luas wilayah, link bukti SK (Google Drive), nama pendamping, jumlah KK, link RKPS, jumlah KUPS, penandaan batas areal, penandaan batas andil

Halaman detail KPS:
- Info utama
- Tabel status RKT per tahun (2023–2030) dengan toggle Sudah/Belum/Proses
- Update status RKT via AJAX (POST /kps/{id}/rkt/{tahun})
```

### 👥 Anggota KPS (Andil Garapan)

- Tabel: `kps_anggota` (relasi ke `kps`)
- Kategori data:
  - `ruang_perlindungan_komunal`
  - `andil_garapan`
- Kolom utama mengikuti format dokumen: `no_andil`, `nama_penggarap`, `no_kk`, `nik`, `koordinat_bt/ls`, batas (barat/utara/selatan/timur), wilayah, pengukur, tanggal, komoditas, `luas_ha`.

---

### 📊 Nilai Transaksi Ekonomi (NTE)

```
Buatkan NteController.php dan views/nte/ untuk modul NTE.
Tabel: nte (kolom: id, kth_id, tahun, bulan, jenis_barang, produk, jumlah, satuan, nilai_rp)

Halaman index:
- Filter: kabupaten_id, kth_id (typeahead), tahun
- Tabel ringkasan: KTH | Total Jan | Feb | ... | Des | Grand Total
- Format nilai: Rp 1.250.000 (fungsi helper number_format IDR)

Form input NTE:
- Pilih KTH (select2/typeahead)
- Pilih Tahun (select)
- Untuk setiap bulan (Jan–Des): jenis barang, produk, jumlah, satuan, nilai (Rp)
- Bisa tambah baris dinamis (add row) per bulan

Chart di halaman detail KTH:
- Line chart NTE per bulan (Chart.js), sumbu Y: nilai Rp, sumbu X: bulan
- Toggle per tahun

Helper format IDR:
function formatRupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
```

---

### 📤 Export Laporan

#### Export Excel

```
Buatkan helpers/export.php — class ExportHelper dengan method exportKthExcel().
Gunakan PhpSpreadsheet.

Method exportKthExcel(array $data, string $kabupaten = 'Semua'):
- Header baris 1: "DAFTAR KELOMPOK TANI HUTAN (KTH)"
- Header baris 2: "CDK Wilayah Bojonegoro — " + $kabupaten
- Header baris 3: "Per " + tanggal sekarang (format Indonesia)
- Baris kosong
- Header kolom (bold, background hijau #2c722c, teks putih):
  No | Kode Register | Nama KTH | Kabupaten | Kecamatan | Desa | Kelas | Jml Anggota | Jenis Usaha | Koordinat LS | Koordinat BT
- Data dari $data
- Total anggota di baris akhir
- Auto-size kolom
- Border tipis semua sel data
- Freeze baris header kolom
- Return response download (.xlsx)
```

#### Export PDF

```
Buatkan method exportDashboardPdf() di ExportHelper menggunakan TCPDF.

PDF harus berisi:
- Header: logo instansi (jika ada), judul "REKAP DATA KTH", nama CDK, tanggal cetak
- Tabel rekap KTH per kabupaten & kelas
- Tabel rekap KPS per skema
- Total luas RHL per kegiatan
- Footer: nomor halaman

Ukuran kertas: A4 portrait.
Font: Helvetica.
Warna header tabel: hijau #2c722c.
```

---

### 🗺️ Peta (Leaflet.js)

```
Buatkan views/peta/index.php — halaman peta interaktif menggunakan Leaflet.js 1.9 (CDN).

Peta harus menampilkan:
1. Marker KTH (ikon daun hijau) dari tabel kth (koordinat_ls, koordinat_bt)
2. Marker DPN (ikon bangunan biru) dari tabel dpn
3. Marker Gully Plug (ikon batu cokelat) dari tabel gully_plug
4. Marker UPSA dari tabel upsa

Sumber data: endpoint AJAX /api/peta?layer=kth|dpn|gullyplug|upsa
Setiap marker punya popup: Nama, Desa/Kecamatan, Kelas/Skema (untuk KTH), Luas (untuk KPS/RHL)

Kontrol peta:
- Toggle layer per jenis (KTH / DPN / Gully Plug / UPSA)
- Filter kabupaten (hide/show marker per kabupaten)
- Zoom ke kabupaten terpilih (gunakan bounds dari koordinat marker)

Basemap: OpenStreetMap (default) + pilihan satelit via Esri.
Sertakan clustering marker (Leaflet.markercluster CDN) untuk KTH.
```

---

### 🔐 Manajemen User

```
Buatkan UserController.php dan views/users/ untuk manajemen pengguna.

Role sistem: admin (akses semua), operator (terbatas kabupaten), viewer (read-only).

Fitur:
- Daftar user: tabel nama, username, email, role, kabupaten, status aktif, last login
- Tambah user: nama, username, email, password (auto-generate atau manual), role, kabupaten
- Edit user: semua field kecuali password (ada tombol "Reset Password" terpisah)
- Reset password: generate password random 10 karakter, tampilkan sekali, hash dengan bcrypt
- Toggle aktif/nonaktif user
- Hanya admin yang bisa akses modul ini

Password hashing:
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
Verifikasi: password_verify($input, $hash)
```

---

### 📝 Activity Log

```
Buatkan fungsi logActivity() di helpers/functions.php.

function logActivity(PDO $db, string $modul, string $aksi, string $detail = ''): void {
    // Insert ke tabel activity_log
    // user_id dari $_SESSION['user_id']
    // ip_address dari $_SERVER['REMOTE_ADDR']
}

Panggil di setiap controller saat create/update/delete:
logActivity($db, 'kth', 'create', "Tambah KTH: {$nama} (ID: {$id})");

Buatkan juga halaman views/log/index.php:
- Tabel log: Waktu | User | Modul | Aksi | Detail | IP
- Filter: user, modul, tanggal (date range picker)
- Hapus log > 90 hari (tombol admin)
```

---

### ⚡ AJAX API Endpoints

```
Buatkan controllers/ApiController.php untuk endpoint AJAX internal.

Endpoints yang dibutuhkan:
1. GET /api/kecamatan?kabupaten_id=X
   → JSON array [{id, nama}] dari tabel kecamatan

2. GET /api/desa?kecamatan_id=X
   → JSON array [{id, nama}] dari tabel desa

3. GET /api/kth-search?q=keyword&kabupaten_id=X
   → JSON array [{id, nama, kode_register, kabupaten_nama}] untuk typeahead pencarian KTH (min 2 karakter)

4. GET /api/peta?layer=kth
   → GeoJSON FeatureCollection dengan properties: nama, kelas, kabupaten, kecamatan

5. POST /api/kps/{id}/rkt/{tahun}
   → Update status RKT, return {"success":true, "status":"sudah"}

Semua endpoint return Content-Type: application/json.
Validasi session sebelum response (return 401 jika belum login).
Sanitasi semua input dengan htmlspecialchars / intval.
```

---

## Penyesuaian Implementasi (Mei 2026)

- **Dokumen via upload file (bukan URL)**:
  - KPS: `bukti_sk_link`, `rkps_link` menyimpan path file hasil upload ke `uploads/kps/{kpsId}/`
  - KTH: `link_sk_kth`, `link_sk_kades`, `link_sertifikat` menyimpan path file hasil upload ke `uploads/kth/{kthId}/`
  - RKT: `kps_rkt.dokumen_link` menyimpan path file hasil upload ke `uploads/rkt/{rktId}/`
- **Relasi opsional KPS → KTH**:
  - Kolom `kps.kth_id` (nullable) + pencarian KTH via endpoint `/api/kth-search`
- **Anggota KPS**:
  - Tabel `kps_anggota` + halaman CRUD di `/kps/{id}/anggota`

---

## 9. Konvensi Kode

### PHP

```php
// Penamaan
class KthController {}          // PascalCase untuk class
function findById(int $id) {}   // camelCase untuk method
$kthData = [];                  // camelCase untuk variabel
const MAX_UPLOAD_SIZE = 5242880; // UPPER_SNAKE untuk konstanta

// Selalu gunakan strict types
declare(strict_types=1);

// Selalu type hint parameter & return
public function findById(int $id): array|false {}

// Prepared statement — WAJIB, tidak boleh query string langsung
$stmt = $pdo->prepare("SELECT * FROM kth WHERE id = ?");
$stmt->execute([$id]);
```

### HTML/View

```php
// Escape output — WAJIB
<?= htmlspecialchars($kth['nama'], ENT_QUOTES, 'UTF-8') ?>

// Flash message
$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data berhasil disimpan'];

// Redirect setelah POST (PRG pattern)
header('Location: /kth');
exit;
```

### Penamaan File

| Jenis | Konvensi | Contoh |
|-------|----------|--------|
| Controller | PascalCase + Controller | `KthController.php` |
| Model | PascalCase | `Kth.php` |
| View | snake_case | `index.php`, `create.php` |
| Helper | snake_case | `functions.php` |
| Asset JS | kebab-case | `app.js`, `charts.js` |

---

## 10. Keamanan

### Wajib Diimplementasikan

- [x] **Prepared Statements** — semua query PDO
- [x] **Password Hashing** — `password_hash()` dengan BCRYPT cost 12
- [x] **Session Regeneration** — `session_regenerate_id(true)` saat login
- [x] **CSRF Token** — setiap form POST sertakan token tersembunyi
- [x] **Input Sanitization** — `htmlspecialchars()` semua output
- [x] **Role Check** — `requireRole()` di semua controller sensitif
- [x] **File Upload** — validasi tipe & ukuran, simpan di luar webroot
- [x] **Error Display** — matikan di production (`display_errors = Off`)
- [x] **HTTPS** — redirect semua ke HTTPS di .htaccess

### CSRF Token Helper

```php
// Generate token
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Di view form:
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

// Verifikasi di controller:
function verifyCsrf(): void {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('CSRF token tidak valid');
    }
}
```

---

## 11. Checklist Pengembangan

### Fase 1 — Fondasi

- [ ] Setup folder struktur sesuai direktori di atas
- [ ] `.htaccess` URL rewriting
- [ ] `composer.json` dan install dependensi
- [ ] `.env` dan `.env.example`
- [ ] `config/database.php` — koneksi PDO
- [ ] `config/auth.php` — session helper
- [ ] `index.php` — router utama
- [ ] `models/BaseModel.php`
- [ ] `views/layouts/main.php` — template utama
- [ ] `views/layouts/auth.php`
- [ ] `views/partials/sidebar.php`, `topbar.php`, `flash.php`
- [ ] `AuthController` + `views/auth/login.php`
- [ ] Import `database_kth_cdk_bjn.sql`
- [ ] Test login dengan user admin seed

### Fase 2 — Modul Inti

- [ ] `KthController` — index, create, store, show, edit, update, delete
- [ ] `views/kth/` — index, create, edit, show
- [ ] AJAX kecamatan & desa (ApiController)
- [ ] DataTables di halaman index KTH
- [ ] `KelembagaanController` — CRUD anggota
- [ ] Import Excel anggota (PhpSpreadsheet)
- [ ] `DashboardController` + Chart.js
- [ ] Views dashboard dengan 4 stat card + chart

### Fase 3 — Sub-modul

- [ ] `KpsController` + views + badge skema
- [ ] `KpsRkt` — toggle status per tahun via AJAX
- [ ] `RhlController` + `RhlBibit`
- [ ] `KbrController` + `KbrTanaman`
- [ ] `AepController`
- [ ] `HhkController` + `HhkDetail`
- [ ] `HhbkController` + `HhbkDetail`
- [ ] `MptsController`
- [ ] `NteController` + chart bulanan
- [ ] `JasaLingkunganController`
- [ ] `DpnController`
- [ ] `GullyPlugController`
- [ ] `UpsaController`

### Fase 4 — Output

- [ ] Export KTH → Excel (PhpSpreadsheet)
- [ ] Export KPS → Excel
- [ ] Export NTE → Excel
- [ ] Export Dashboard → PDF (TCPDF)
- [ ] Peta Leaflet.js dengan clustering
- [ ] Filter per layer di peta
- [ ] Template Excel download untuk import

### Fase 5 — Finalisasi

- [ ] `UserController` — CRUD user & reset password
- [ ] `activity_log` — fungsi logActivity()
- [ ] Halaman log viewer
- [ ] Semua form dengan CSRF token
- [ ] Validasi server-side semua form
- [ ] Responsif mobile (test di HP)
- [ ] Test semua role (admin/operator/viewer)
- [ ] Matikan `display_errors` di production
- [ ] Backup script database

---

## 12. Referensi File

| File | Deskripsi |
|------|-----------|
| `database_kth_cdk_bjn.sql` | Skema MySQL lengkap + seed data wilayah |
| `dashboard.html` | Mockup HTML dashboard (referensi UI) |
| `database_KTH_CDK_BJN_2026.xlsx` | Data sumber asli dari CDK Bojonegoro |
| `SIBADAK_DEV_GUIDE.md` | File panduan ini |

### Sumber Daya Eksternal

| Resource | URL |
|----------|-----|
| Tailwind CSS CDN | `https://cdn.tailwindcss.com` |
| Chart.js CDN | `https://cdn.jsdelivr.net/npm/chart.js` |
| DataTables CDN | `https://cdn.datatables.net` |
| Leaflet.js CDN | `https://unpkg.com/leaflet` |
| Leaflet MarkerCluster | `https://unpkg.com/leaflet.markercluster` |
| Google Fonts (Syne + DM Sans) | `https://fonts.googleapis.com` |
| Heroicons SVG | `https://heroicons.com` |
| PhpSpreadsheet Docs | `https://phpspreadsheet.readthedocs.io` |
| TCPDF Docs | `https://tcpdf.org` |

---

> **Dibuat untuk:** CDK Wilayah Bojonegoro — Dinas Kehutanan Provinsi Jawa Timur  
> **Versi dokumen:** 1.0 · Mei 2026  
> **Teknologi:** PHP 8.2 · MySQL 8.0 · Tailwind CSS · Chart.js · Leaflet.js
