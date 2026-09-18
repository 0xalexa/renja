# Spesifikasi Sistem — Manajemen Dokumen PEP

> **Status:** Dokumen perencanaan (belum implementasi kode)  
> **Versi:** 1.0  
> **Tanggal:** 9 September 2026

---

## 1. Ringkasan Sistem

Sistem ini adalah aplikasi web **Manajemen Dokumen & Arsip PEP** (Perencanaan, Evaluasi, dan Pelaporan) untuk mengelola:

- Dokumen perencanaan kerja (**Renja**)
- Dokumen perjanjian kinerja (**PK**)
- Dokumen anggaran (**DPA**)
- Arsip persuratan (**Surat Masuk & Surat Keluar**)
- Laporan terintegrasi (**Simdapangda**)

Karakteristik utama: sebagian besar modul bersifat **manajemen dokumen/arsip** dengan operasi CRUD standar, upload file, dan fitur unduh/laporan.

---

## 2. Struktur Navigasi

Navigasi utama dapat diimplementasikan sebagai **Navbar horizontal** (atas) atau **Sidebar** (kiri). Struktur hierarki menu:

```
├── Beranda (Dashboard)
├── Renja
│   ├── Renja Murni
│   └── Renja Perubahan
├── Perjanjian Kinerja
│   ├── PK Murni
│   └── PK Perubahan
├── Anggaran
│   ├── DPA Murni
│   └── DPA Perubahan
├── Arsip PEP
│   ├── Surat Keluar
│   └── Surat Masuk
└── Laporan Simdapangda
```

### 2.1 Beranda (Dashboard)

Halaman sambutan dan ringkasan statistik singkat.

| Komponen | Keterangan |
|----------|------------|
| Ringkasan dokumen | Jumlah dokumen Renja, PK, DPA per tahun berjalan |
| Ringkasan surat | Jumlah surat masuk & keluar bulan ini |
| Grafik anggaran | Penyerapan anggaran (jika data tersedia) |
| Akses cepat | Shortcut ke modul yang sering diakses |

### 2.2 Renja (Rencana Kerja)

| Sub-menu | Deskripsi |
|----------|-----------|
| **Renja Murni** | Dokumen Rencana Kerja versi awal/murni |
| **Renja Perubahan** | Dokumen Rencana Kerja hasil perubahan/revisi |

### 2.3 Perjanjian Kinerja

| Sub-menu | Deskripsi |
|----------|-----------|
| **PK Murni** | Dokumen Perjanjian Kinerja versi awal |
| **PK Perubahan** | Dokumen Perjanjian Kinerja hasil perubahan |

### 2.4 Anggaran

| Sub-menu | Deskripsi |
|----------|-----------|
| **DPA Murni** | Dokumen Daftar Pelaksanaan Anggaran versi murni |
| **DPA Perubahan** | Dokumen DPA hasil perubahan |

### 2.5 Arsip PEP

| Sub-menu | Deskripsi |
|----------|-----------|
| **Surat Keluar** | Arsip surat yang dikirim/diterbitkan instansi |
| **Surat Masuk** | Arsip surat yang diterima instansi |

### 2.6 Laporan Simdapangda

Menu pelaporan gabungan/terintegrasi. Menyajikan rekapitulasi atau laporan yang menggabungkan data dari berbagai modul (Renja, PK, Anggaran, Persuratan) sesuai format Simdapangda.

---

## 3. Klasifikasi Modul

Modul dibagi menjadi **dua kategori** berdasarkan karakteristik data:

| Kategori | Modul | Pola Data |
|----------|-------|-----------|
| **A — Dokumen** | Renja Murni/Perubahan, PK Murni/Perubahan, DPA Murni/Perubahan | Upload dokumen perencanaan/anggaran |
| **B — Persuratan** | Surat Masuk, Surat Keluar | Pencatatan surat dengan metadata surat |

---

## 4. Alur Sistem & CRUD per Kategori

### 4.A Modul Dokumen (Renja, PK, Anggaran)

Contoh referensi: **Renja Murni** / **DPA Murni**. Pola CRUD identik; perbedaan hanya pada **tipe modul** dan **sub-kategori (Murni/Perubahan)**.

#### 4.A.1 Read — Halaman Daftar

Menampilkan tabel dokumen yang sudah di-upload.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| No | Auto increment / urut | Nomor urut baris |
| Tahun Anggaran | Integer (YYYY) | Tahun anggaran dokumen |
| Nama Dokumen / Program | String | Judul atau nama program |
| Keterangan | Text (opsional) | Catatan tambahan |
| File | Link/ikon | Tombol Lihat / Download PDF |
| Aksi | Button group | Edit, Hapus |

**Fitur tambahan halaman daftar:**
- Filter berdasarkan tahun anggaran
- Pencarian berdasarkan judul/nama dokumen
- Pagination jika data banyak
- Tombol **"Tambah Data"** di pojok kanan atas

#### 4.A.2 Create — Form Input

| Field | Tipe Input | Wajib | Validasi |
|-------|-----------|-------|----------|
| Tahun Anggaran | Select / Number | Ya | Format 4 digit, rentang wajar (mis. 2020–2035) |
| Judul Dokumen | Text | Ya | Min 3 karakter |
| Kategori | Hidden / Auto | Ya | Diisi otomatis dari sub-menu (Murni/Perubahan) |
| Keterangan | Textarea | Tidak | Maks 500 karakter |
| Upload File | File input | Ya | `.pdf`, `.xlsx`; maks ukuran (mis. 10 MB) |

**Alur Create:**
1. Pengguna klik **Tambah Data**
2. Isi form → upload file
3. Sistem validasi input & tipe file
4. File disimpan ke storage server (folder terorganisir per modul/tahun)
5. Metadata disimpan ke database
6. Redirect ke halaman daftar dengan notifikasi sukses

#### 4.A.3 Update — Form Edit

- Tombol **Edit** pada kolom Aksi membuka form yang sama dengan Create, terisi data existing
- Pengguna dapat mengubah: tahun, judul, keterangan
- Pengguna dapat **mengganti file** (file lama dihapus dari storage, file baru disimpan)
- Field kategori/tipe modul **tidak dapat diubah** (ditentukan oleh menu asal)

#### 4.A.4 Delete — Hapus Data

- Tombol **Hapus** dengan konfirmasi dialog ("Yakin hapus data ini?")
- Menghapus record di database **dan** file fisik di server
- Jika penghapusan file gagal, log error dan beri peringatan ke admin

#### 4.A.5 Output — Akses Dokumen

| Aktor | Akses |
|-------|-------|
| Pengguna umum / Pimpinan | Lihat daftar, klik **Download / Lihat** untuk membaca PDF |
| Admin / Operator | Full CRUD |

---

### 4.B Modul Persuratan (Arsip PEP)

Contoh referensi: **Surat Masuk**. Pola serupa untuk **Surat Keluar** dengan perbedaan field metadata.

#### 4.B.1 Read — Halaman Daftar Surat

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| No | Auto | Urutan |
| No. Surat | String | Nomor surat resmi |
| Tanggal Surat | Date | Tanggal surat |
| Perihal | String | Subjek / perihal surat |
| Asal / Tujuan | String | Surat Masuk: asal instansi/pengirim; Surat Keluar: tujuan instansi/penerima |
| File Lampiran | Link | Scan/foto surat (PDF/gambar) |
| Aksi | Button group | Edit, Hapus |

**Fitur tambahan:**
- Filter rentang tanggal
- Pencarian berdasarkan nomor surat / perihal
- Tombol **"Tambah Surat"**

#### 4.B.2 Create — Form Input Surat

**Surat Masuk:**

| Field | Tipe Input | Wajib | Validasi |
|-------|-----------|-------|----------|
| Nomor Surat | Text | Ya | Unik per tahun (disarankan) |
| Tanggal Surat | Date picker | Ya | Tidak boleh tanggal masa depan |
| Perihal | Text | Ya | Min 3 karakter |
| Asal Instansi / Pengirim | Text | Ya | Nama instansi/pengirim |
| Upload Scan Surat | File input | Ya | `.pdf`, `.jpg`, `.png`; maks 5 MB |

**Surat Keluar** — field sama kecuali:
- **Tujuan Instansi / Penerima** menggantikan Asal/Pengirim

#### 4.B.3 Update & Delete

- **Update:** Perbaiki nomor surat, tanggal, perihal, pengirim, atau ganti file lampiran
- **Delete:** Hapus record + file lampiran (dengan konfirmasi)

#### 4.B.4 Output — Laporan Persuratan

| Fitur | Keterangan |
|-------|------------|
| Rekapitulasi | Ringkasan jumlah surat per periode |
| Cetak laporan | Export PDF/Excel berdasarkan rentang tanggal |
| Filter laporan | Periode (bulan/tahun), jenis (masuk/keluar) |

---

## 5. Modul Laporan Simdapangda

Menu khusus pelaporan gabungan. Detail format mengikuti kebutuhan Simdapangda.

| Fitur | Keterangan |
|-------|------------|
| Rekapitulasi dokumen | Ringkasan Renja, PK, DPA per tahun |
| Rekapitulasi surat | Ringkasan surat masuk/keluar per periode |
| Export | PDF / Excel sesuai template Simdapangda |
| Filter | Tahun anggaran, periode, jenis dokumen |

> **Catatan:** Spesifikasi format laporan Simdapangda perlu dikonfirmasi dengan template resmi yang digunakan instansi.

---

## 6. Model Data (Rancangan Database)

### 6.1 Tabel `users`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | PK | |
| name | varchar | Nama lengkap |
| email | varchar | Unique |
| password | varchar | Hashed |
| role | enum | `admin`, `operator`, `viewer` |
| created_at | timestamp | |
| updated_at | timestamp | |

### 6.2 Tabel `dokumen`

Satu tabel untuk semua modul dokumen (Renja, PK, DPA).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | PK | |
| modul | enum | `renja`, `pk`, `anggaran` |
| kategori | enum | `murni`, `perubahan` |
| tahun_anggaran | year | |
| judul | varchar | |
| keterangan | text | Nullable |
| file_path | varchar | Path relatif di storage |
| file_name | varchar | Nama asli file |
| file_size | integer | Bytes |
| file_mime | varchar | application/pdf, dll |
| uploaded_by | FK → users.id | |
| created_at | timestamp | |
| updated_at | timestamp | |

**Index disarankan:** `(modul, kategori, tahun_anggaran)`

### 6.3 Tabel `surat`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | PK | |
| jenis | enum | `masuk`, `keluar` |
| nomor_surat | varchar | |
| tanggal_surat | date | |
| perihal | varchar | |
| instansi | varchar | Asal (masuk) / Tujuan (keluar) |
| file_path | varchar | |
| file_name | varchar | |
| file_size | integer | |
| file_mime | varchar | |
| uploaded_by | FK → users.id | |
| created_at | timestamp | |
| updated_at | timestamp | |

**Index disarankan:** `(jenis, tanggal_surat)`, `(nomor_surat)`

---

## 7. Struktur Penyimpanan File

```
storage/
├── dokumen/
│   ├── renja/
│   │   ├── murni/
│   │   │   └── {tahun}/
│   │   └── perubahan/
│   │       └── {tahun}/
│   ├── pk/
│   │   ├── murni/
│   │   └── perubahan/
│   └── anggaran/
│       ├── murni/
│       └── perubahan/
└── surat/
    ├── masuk/
    │   └── {tahun}/
    └── keluar/
        └── {tahun}/
```

**Konvensi penamaan file:** `{timestamp}_{slug-judul}.{ext}` — hindari spasi dan karakter khusus.

---

## 8. Hak Akses & Peran Pengguna

| Peran | Beranda | Lihat Dokumen | CRUD Dokumen | Lihat Surat | CRUD Surat | Laporan |
|-------|---------|---------------|--------------|-------------|------------|---------|
| **Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Operator** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Viewer** (Pimpinan) | ✅ | ✅ (download) | ❌ | ✅ (download) | ❌ | ✅ |

---

## 9. Routing (Rancangan URL)

| URL | Halaman | Modul |
|-----|---------|-------|
| `/` | Dashboard | Beranda |
| `/renja/murni` | Daftar + CRUD | Renja Murni |
| `/renja/perubahan` | Daftar + CRUD | Renja Perubahan |
| `/pk/murni` | Daftar + CRUD | PK Murni |
| `/pk/perubahan` | Daftar + CRUD | PK Perubahan |
| `/anggaran/murni` | Daftar + CRUD | DPA Murni |
| `/anggaran/perubahan` | Daftar + CRUD | DPA Perubahan |
| `/arsip/surat-masuk` | Daftar + CRUD | Surat Masuk |
| `/arsip/surat-keluar` | Daftar + CRUD | Surat Keluar |
| `/laporan/simdapangda` | Laporan | Simdapangda |
| `/login` | Autentikasi | — |

---

## 10. Wireframe Alur Pengguna

### 10.1 Alur Upload Dokumen (Renja/PK/DPA)

```
[Menu Sidebar/Navbar]
       │
       ▼
[Halaman Daftar — Tabel Dokumen]
       │
       ├── Klik "Tambah Data"
       │         │
       │         ▼
       │   [Form Input + Upload File]
       │         │
       │         ▼
       │   [Validasi → Simpan DB + File]
       │         │
       │         ▼
       │   [Redirect ke Daftar + Notifikasi Sukses]
       │
       ├── Klik "Edit" → [Form Edit] → Simpan
       ├── Klik "Hapus" → [Konfirmasi] → Hapus DB + File
       └── Klik "Download/Lihat" → [Tampilkan/Unduh PDF]
```

### 10.2 Alur Input Surat

```
[Menu Arsip PEP → Surat Masuk/Keluar]
       │
       ▼
[Halaman Daftar Surat]
       │
       ├── Klik "Tambah Surat" → [Form Input] → Simpan
       ├── Filter tanggal / Cari nomor surat
       ├── Klik "Cetak Laporan" → [Export PDF/Excel]
       └── CRUD standar (Edit / Hapus)
```

---

## 11. Validasi & Aturan Bisnis

| Aturan | Detail |
|--------|--------|
| File wajib | Tidak boleh simpan record dokumen/surat tanpa file lampiran |
| Tipe file | Whitelist: `.pdf`, `.xlsx` (dokumen); `.pdf`, `.jpg`, `.png` (surat) |
| Ukuran maks | Dokumen: 10 MB; Surat: 5 MB (dapat disesuaikan) |
| Tahun anggaran | Hanya tahun valid (4 digit) |
| Hapus file | Saat delete record, file fisik ikut dihapus |
| Nomor surat | Disarankan unik per jenis per tahun |
| Audit trail | Simpan `uploaded_by` dan timestamp untuk setiap record |

---

## 12. Komponen UI yang Dibutuhkan

| Komponen | Digunakan Di |
|----------|-------------|
| Layout (Navbar/Sidebar + Content) | Semua halaman |
| Data Table (sortable, searchable) | Daftar dokumen & surat |
| Form Input + File Upload | Create & Update |
| Modal Konfirmasi Hapus | Delete |
| Toast / Alert Notifikasi | Feedback sukses/error |
| Date Range Picker | Filter surat & laporan |
| PDF Viewer / Download Button | Output dokumen |
| Dashboard Cards + Chart | Beranda |
| Export Button (PDF/Excel) | Laporan |

---

## 13. Tahapan Implementasi (Roadmap)

| Fase | Cakupan | Prioritas |
|------|---------|-----------|
| **Fase 1** | Setup proyek, autentikasi, layout navigasi | Tinggi |
| **Fase 2** | CRUD modul dokumen (Renja → PK → Anggaran) | Tinggi |
| **Fase 3** | CRUD modul persuratan (Surat Masuk/Keluar) | Tinggi |
| **Fase 4** | Dashboard statistik | Sedang |
| **Fase 5** | Laporan & export Simdapangda | Sedang |
| **Fase 6** | Polish UI, testing, deployment | Sedang |

---

## 14. Pertanyaan Terbuka (Perlu Konfirmasi)

Sebelum coding, hal berikut perlu diklarifikasi:

1. **Tech stack** — PHP/Laravel (Laragon), Node.js, atau lainnya?
2. **Template laporan Simdapangda** — Apakah sudah ada format/template resmi?
3. **Multi-user** — Berapa peran pengguna? Apakah perlu approval workflow?
4. **Bahasa UI** — Full Bahasa Indonesia?
5. **Branding** — Logo, nama instansi, warna tema?
6. **Hosting** — Lokal (Laragon) saja atau akan di-deploy ke server?

---

## 15. Diagram Relasi Modul

```
                    ┌─────────────┐
                    │   Beranda   │
                    │ (Dashboard) │
                    └──────┬──────┘
                           │ statistik dari
           ┌───────────────┼───────────────┐
           ▼               ▼               ▼
    ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
    │   Dokumen   │ │  Persuratan │ │   Laporan   │
    │ Renja/PK/DPA│ │ Masuk/Keluar│ │ Simdapangda │
    └──────┬──────┘ └──────┬──────┘ └──────┬──────┘
           │               │               │
           └───────────────┴───────────────┘
                           │
                    ┌──────▼──────┐
                    │  Database   │
                    │  + Storage  │
                    └─────────────┘
```

---

*Dokumen ini menjadi acuan utama sebelum implementasi kode dimulai. Setiap perubahan kebutuhan harus diperbarui di file ini terlebih dahulu.*
