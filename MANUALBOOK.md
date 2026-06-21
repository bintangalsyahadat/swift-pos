## 📘 Daftar Isi Manual Book SwiftPos

### 1. Pendahuluan
- Deskripsi sistem SwiftPos
- Tujuan dan manfaat sistem
- Ruang lingkup aplikasi
- Istilah & definisi (POS, Sesi Kasir, Penyesuaian Stok, dll.)

### 2. Spesifikasi Sistem
- Kebutuhan perangkat keras (hardware minimum)
- Kebutuhan perangkat lunak (browser, OS)
- Akses URL / jaringan

### 3. Struktur Pengguna & Hak Akses
- Daftar role yang tersedia (super_admin, dll.)
- Tabel perbedaan hak akses tiap role (CRUD per modul)
- Cara mengelola role & permission (modul Roles)

### 4. Login & Autentikasi
- Cara login ke sistem
- Lupa password
- Edit profil & foto avatar
- Logout

### 5. Dashboard
- Penjelasan widget statistik: Total Pesanan, Pendapatan, Produk Terjual (hari ini)
- Grafik pesanan (Order Chart)
- Grafik metode pembayaran (Payment Method Chart)
- Tabel produk terlaris (Best Selling Products)
- Tabel pesanan terbaru (Latest Orders)

### 6. Manajemen Produk
#### 6.1 Merek (Brand)
- Tambah, lihat, ubah, hapus merek

#### 6.2 Kategori
- Tambah, lihat, ubah, hapus kategori

#### 6.3 Sub Kategori
- Tambah, lihat, ubah, hapus sub kategori

#### 6.4 Produk
- Tambah produk (nama, SKU, barcode, harga jual, harga dasar, gambar, merek, kategori, sub kategori, status aktif)
- Lihat detail produk
- Ubah data produk
- Nonaktifkan / aktifkan produk
- Melihat stok terkini (dihitung dari Stock Move)

### 7. Manajemen Pelanggan
- Tambah, lihat, ubah, hapus pelanggan
- Informasi pelanggan (nama, telepon, dll.)

### 8. Point of Sale
#### 8.1 Terminal Kasir (Cashier)
- Tambah, lihat, ubah, nonaktifkan terminal kasir
- Kode terminal & deskripsi

#### 8.2 Membuka Sesi POS
- Memilih terminal kasir
- Mengisi saldo awal (Opening Balance)
- Membuka sesi

#### 8.3 Transaksi di POS Terminal
- Antarmuka POS (layout tanpa sidebar)
- Mencari produk (nama, SKU, barcode)
- Menambah produk ke keranjang
- Mengubah jumlah / menghapus item di keranjang
- Memilih pelanggan
- Memberikan diskon
- Memilih metode pembayaran:
  - **Tunai (Cash)**: input nominal uang, hitung kembalian
  - **Non-Tunai via Xendit**: QR Code (QRIS), Virtual Account
- Proses checkout & cetak/tampil struk

#### 8.4 Pembayaran Xendit
- Alur pembayaran QR Code (QRIS)
- Alur pembayaran Virtual Account
- Pengecekan status pembayaran (polling otomatis)
- Penanganan pembayaran gagal / kedaluwarsa

#### 8.5 Struk / Receipt
- Tampilan modal struk setelah transaksi
- Informasi struk (nama toko, item, total, kembalian, footer)

#### 8.6 Menutup Sesi POS
- Mengisi saldo aktual (Actual Balance)
- Catatan penutupan (Closing Notes)
- Melihat selisih kas (Expected vs Actual)
- Menutup sesi

#### 8.7 Riwayat Sesi POS (POS Sessions)
- Melihat daftar sesi yang pernah dibuka/ditutup
- Detail sesi (kas awal, kas akhir, selisih, kasir, waktu)

#### 8.8 Pesanan (Orders)
- Melihat daftar semua pesanan
- Filter pesanan (status, tanggal, kasir, metode pembayaran)
- Detail pesanan & item pesanan
- Status pesanan & status pembayaran

### 9. Manajemen Inventaris
#### 9.1 Penyesuaian Stok (Inventory Adjustment)
- Membuat penyesuaian stok baru (nama, referensi, catatan)
- Menambah detail penyesuaian (produk, tipe: in/out, jumlah)
- Memvalidasi / menyelesaikan penyesuaian (status: done)
- Efek terhadap stok produk

#### 9.2 Pergerakan Stok (Stock Moves)
- Melihat riwayat semua pergerakan stok
- Filter berdasarkan produk, tipe, tanggal
- Relasi dengan penyesuaian stok & pesanan

### 10. Manajemen Pengguna (Users)
- Tambah pengguna baru
- Assign role ke pengguna
- Ubah data pengguna
- Nonaktifkan pengguna

### 11. Metode Pembayaran (Payment Methods)
- Melihat daftar metode pembayaran
- Tambah, ubah, nonaktifkan metode pembayaran

### 12. Pengaturan Sistem (Settings)
*(Hanya dapat diakses oleh super_admin)*

#### 12.1 Pengaturan Umum
- Nama toko
- Alamat toko
- Nomor telepon toko
- Mata uang (Currency)
- Zona waktu (Timezone)
- Footer struk
- Pelanggan default

#### 12.2 Integrasi Xendit
- Mengaktifkan/menonaktifkan Xendit
- Pilihan environment (sandbox / production)
- Mengisi Secret Key & Public Key
- Mengisi Webhook Token
- Test koneksi Xendit
- Konfigurasi Webhook URL di Xendit Dashboard

### 13. Panduan Troubleshooting
- Terminal kasir tidak aktif (403)
- Terminal sedang digunakan kasir lain
- Pembayaran Xendit gagal / expired
- Webhook tidak menerima notifikasi
- Token belum tersimpan / tidak sinkron
- Stok produk tidak sesuai

### 14. Lampiran
- Daftar singkatan & istilah teknis
- Alur kerja sistem (flowchart: login → buka sesi → transaksi → tutup sesi)
- ERD / Struktur data
- API Endpoints (untuk integrasi eksternal)
- Package & Dependensi
- Informasi kontak support
- Fitur tambahan (dark mode, API token, ekspor data, dll.)

---

---

## BAB 1 — Pendahuluan

### 1.1 Deskripsi Sistem

**SwiftPos** adalah aplikasi *Point of Sale* (POS) berbasis web yang dirancang untuk membantu bisnis ritel dalam mengelola proses penjualan secara efisien dan terpusat. Sistem ini dibangun menggunakan **Laravel 12** sebagai framework backend dan **Filament v5** sebagai antarmuka panel administrasi, sehingga menghasilkan tampilan yang modern, responsif, dan mudah digunakan.

SwiftPos menyediakan seluruh siklus operasional kasir mulai dari pembukaan sesi, pencatatan transaksi, pemrosesan pembayaran, hingga penutupan sesi dengan rekonsiliasi kas otomatis.

### 1.2 Tujuan dan Manfaat Sistem

**Tujuan:**
- Menyediakan platform terpadu untuk mengelola transaksi penjualan secara *real-time*
- Memudahkan pengelolaan produk, stok, pelanggan, dan laporan dalam satu sistem
- Mendukung metode pembayaran tunai maupun non-tunai (QRIS & Virtual Account via Xendit)

**Manfaat:**
- Proses transaksi lebih cepat dan akurat dibandingkan pencatatan manual
- Stok produk terupdate otomatis setiap kali terjadi penjualan atau penyesuaian
- Laporan penjualan harian tersedia langsung di Dashboard
- Sistem peran & hak akses memastikan keamanan data tiap pengguna

### 1.3 Ruang Lingkup Aplikasi

SwiftPos mencakup modul-modul berikut:

| Modul | Deskripsi Singkat |
|---|---|
| Dashboard | Statistik & grafik penjualan harian |
| Produk | Pengelolaan merek, kategori, sub kategori, dan produk |
| Pelanggan | Data pelanggan terdaftar |
| Point of Sale | Terminal kasir, transaksi, sesi POS, dan pesanan |
| Inventaris | Penyesuaian stok dan riwayat pergerakan stok |
| Pengguna | Manajemen akun dan role pengguna |
| Metode Pembayaran | Konfigurasi metode pembayaran |
| Pengaturan | Konfigurasi umum toko dan integrasi Xendit |

### 1.4 Istilah dan Definisi

| Istilah | Definisi |
|---|---|
| **POS (Point of Sale)** | Titik transaksi penjualan antara kasir dan pelanggan |
| **Terminal Kasir** | Perangkat/akun kasir yang digunakan untuk membuka sesi POS |
| **Sesi POS** | Periode kerja kasir dari saat membuka hingga menutup terminal |
| **Opening Balance** | Saldo uang tunai awal yang ada di laci kasir saat sesi dibuka |
| **Actual Balance** | Saldo uang tunai yang dihitung secara fisik saat sesi ditutup |
| **Expected Balance** | Saldo kas yang seharusnya ada (saldo awal + total penjualan tunai) |
| **Selisih Kas** | Perbedaan antara *Expected Balance* dan *Actual Balance* |
| **Stock Move** | Catatan pergerakan stok masuk (in) atau keluar (out) |
| **Inventory Adjustment** | Penyesuaian stok manual yang dilakukan di luar transaksi penjualan |
| **Xendit** | Payment gateway untuk memproses pembayaran QRIS dan Virtual Account |
| **QRIS** | Standar QR Code pembayaran nasional Indonesia |
| **Virtual Account** | Nomor rekening virtual untuk pembayaran transfer bank |
| **Role** | Kumpulan hak akses yang diberikan kepada pengguna (mis. `super_admin`) |
| **SKU** | *Stock Keeping Unit* — kode unik identifikasi produk |
| **Barcode** | Kode batang yang dapat dipindai untuk mengidentifikasi produk |

---

## BAB 2 — Spesifikasi Sistem

### 2.1 Kebutuhan Perangkat Keras (Hardware)

SwiftPos berjalan sepenuhnya berbasis web sehingga tidak memerlukan instalasi aplikasi khusus di sisi klien. Berikut spesifikasi minimum yang disarankan:

**Server / Komputer yang Menjalankan Aplikasi:**

| Komponen | Spesifikasi Minimum |
|---|---|
| Prosesor | Dual-core 2.0 GHz |
| RAM | 2 GB (disarankan 4 GB) |
| Penyimpanan | 10 GB ruang kosong |
| Koneksi Internet | Diperlukan untuk pembayaran Xendit (QRIS / VA) |

**Perangkat Klien (Operator/Kasir):**

| Komponen | Keterangan |
|---|---|
| Komputer / Laptop | Untuk akses panel admin dan POS Terminal |
| Tablet / Smartphone | Didukung — antarmuka responsif |
| Printer Struk | Opsional — struk dapat ditampilkan di layar |
| Barcode Scanner | Opsional — mendukung input via keyboard (HID mode) |

### 2.2 Kebutuhan Perangkat Lunak (Software)

**Di Sisi Server:**

| Software | Versi Minimum |
|---|---|
| PHP | >= 8.2 (disarankan 8.3) |
| Composer | >= 2.x |
| Node.js & NPM | Node.js >= 18.x |
| Database | SQLite (default), MySQL, atau PostgreSQL |
| Web Server | Laravel Built-in / Apache / Nginx |

**Di Sisi Klien (Browser yang Didukung):**

| Browser | Keterangan |
|---|---|
| Google Chrome | ✅ Direkomendasikan |
| Mozilla Firefox | ✅ Didukung |
| Microsoft Edge | ✅ Didukung |
| Safari | ✅ Didukung |

> JavaScript wajib diaktifkan di browser karena antarmuka POS menggunakan **Livewire** (real-time interaktif tanpa reload halaman).

### 2.3 Akses URL dan Navigasi

Setelah server berjalan, sistem dapat diakses melalui browser pada alamat:

```
http://localhost:8000/dashboard
```

Atau jika di-deploy ke server:

```
https://[domain-anda]/dashboard
```

**Halaman khusus POS Terminal** diakses dengan URL:

```
http://localhost:8000/pos?cashier_id=[ID_TERMINAL]
```

> URL POS Terminal tidak memiliki sidebar/navigasi — dirancang sebagai layar penuh untuk kemudahan operasional kasir.

### 2.4 Paket & Dependensi Utama

| Paket | Fungsi |
|---|---|
| `laravel/framework ^12.0` | Framework PHP utama |
| `filament/filament ^5.5` | Panel administrasi & komponen UI |
| `bezhansalleh/filament-shield ^4.2` | Manajemen role & permission |
| `joaopaulolndev/filament-edit-profile ^3.0` | Edit profil, avatar & manajemen sesi browser |
| `andreia/filament-ui-switcher ^1.0` | Toggle mode terang / gelap |
| `laravel/sanctum ^4.3` | Autentikasi API token |
| `spatie/laravel-permission` | Sistem role & permission berbasis database |

---

## BAB 3 — Struktur Pengguna & Hak Akses

### 3.1 Sistem Role & Permission

SwiftPos menggunakan **Spatie Laravel-Permission** yang dikombinasikan dengan **Filament Shield** untuk manajemen role dan permission secara otomatis. Filament Shield akan mendeteksi seluruh Resource dan Page yang terdaftar di panel admin, lalu menghasilkan permission CRUD (`view_any`, `view`, `create`, `update`, `delete`, `restore`, `force_delete`) secara otomatis.

### 3.2 Daftar Role Bawaan

| Role | Deskripsi | Hak Akses |
|---|---|---|
| **super_admin** | Administrator tertinggi — memiliki seluruh permission di sistem | Semua modul & pengaturan sistem |

> Role tambahan dapat dibuat sesuai kebutuhan melalui menu **Roles** di panel admin. Setiap role dapat diberi kombinasi permission yang berbeda.

### 3.3 Permission yang Dihasilkan per Modul

Filament Shield secara otomatis menghasilkan permission berikut untuk setiap Resource:

| Permission | Arti |
|---|---|
| `view_any_{resource}` | Melihat daftar/list data |
| `view_{resource}` | Melihat detail satu data |
| `create_{resource}` | Membuat data baru |
| `update_{resource}` | Mengubah data |
| `delete_{resource}` | Menghapus data |
| `restore_{resource}` | Mengembalikan data terhapus (soft delete) |
| `force_delete_{resource}` | Menghapus permanen |

**Permission untuk Page:**
| Permission | Arti |
|---|---|
| `page_{PageName}` | Mengakses halaman kustom (contoh: `page_SettingsPage`) |
| `widget_{WidgetName}` | Melihat widget di Dashboard |

### 3.4 Modul yang Dilindungi Permission

| Modul / Resource | Permission Prefix |
|---|---|
| Produk (ProductResource) | `view_any_product`, `view_product`, `create_product`, `update_product`, `delete_product` |
| Merek (BrandResource) | `view_any_brand`, `view_brand`, `create_brand`, `update_brand`, `delete_brand` |
| Kategori (CategoryResource) | `view_any_category`, `view_category`, `create_category`, `update_category`, `delete_category` |
| Sub Kategori (SubCategoryResource) | `view_any_sub-category`, `view_sub-category`, `create_sub-category`, `update_sub-category`, `delete_sub-category` |
| Pelanggan (CustomerResource) | `view_any_customer`, `view_customer`, `create_customer`, `update_customer`, `delete_customer` |
| Terminal Kasir (CashierResource) | `view_any_cashier`, `view_cashier`, `create_cashier`, `update_cashier`, `delete_cashier` |
| Pesanan (OrderResource) | `view_any_order`, `view_order`, `create_order`, `update_order`, `delete_order` |
| Riwayat Sesi POS (PosSessionResource) | `view_any_pos-session`, `view_pos-session` (hanya view — tidak bisa create/delete) |
| Metode Pembayaran (PaymentMethodResource) | `view_any_payment-method`, `view_payment-method`, `create_payment-method`, `update_payment-method`, `delete_payment-method` |
| Penyesuaian Stok (InventoryAdjustmentResource) | `view_any_inventory-adjustment`, `view_inventory-adjustment`, `create_inventory-adjustment`, `update_inventory-adjustment`, `delete_inventory-adjustment` |
| Pergerakan Stok (StockMoveResource) | `view_any_stock-move`, `view_stock-move` (hanya view — tidak bisa create/delete) |
| Pengguna (UserResource) | `view_any_user`, `view_user`, `create_user`, `update_user`, `delete_user` |
| Pengaturan Sistem (SettingsPage) | `page_SettingsPage` (**hanya super_admin**) |
| POS Terminal (PosTerminal) | `page_PosTerminal` |
| Dashboard | `widget_StatsWidget`, `widget_OrderChartWidget`, `widget_PaymentMethodChartWidget`, `widget_BestSellingProductsWidget`, `widget_LatestOrdersWidget` |

### 3.5 Cara Mengelola Role & Permission

Untuk membuka menu manajemen role, pengguna harus memiliki permission yang relevan (bawaan `super_admin`).

**Langkah mengakses menu Roles:**

1. Dari sidebar navigasi, buka menu **Roles** (jika tersedia di navigasi — tergantung konfigurasi Filament Shield).
2. Daftar role yang sudah ada akan ditampilkan dalam bentuk tabel.

> Apabila menu Roles tidak muncul, pastikan pengguna login memiliki permission `view_any_role` dan `view_role`.

**Langkah membuat role baru:**

1. Klik tombol **New Role**.
2. Isi **Nama Role** (contoh: `kasir`, `gudang`, `manajer`).
3. Centang permission yang ingin diberikan pada role tersebut.
4. Klik **Create**.

**Langkah mengubah permission role:**

1. Klik role yang ingin diubah dari daftar.
2. Centang atau hilangkan centang permission sesuai kebutuhan.
3. Klik **Save**.

**Langkah assign role ke pengguna:**

1. Buka menu **Users** di sidebar.
2. Klik pengguna yang ingin diberi role.
3. Pada form edit pengguna, pilih role di bagian **Roles**.
4. Klik **Save**.

> Perubahan role akan langsung berlaku pada sesi login berikutnya.

---

## BAB 4 — Login & Autentikasi

### 4.1 Cara Login ke Sistem

Untuk mengakses panel SwiftPos, pengguna harus melakukan autentikasi terlebih dahulu.

**Langkah-langkah login:**

1. Buka browser dan akses URL panel admin:
   ```
   http://[domain-anda]/dashboard
   ```
2. Sistem akan secara otomatis mengarahkan ke halaman login jika belum terautentikasi.
3. Pada halaman login, masukkan:
   - **Email** — alamat email yang terdaftar di sistem
   - **Password** — kata sandi akun
4. Klik tombol **Sign in** untuk masuk.
5. Jika berhasil, sistem akan mengarahkan langsung ke halaman **Dashboard**.

> **Catatan:** Jika mencoba mengakses halaman yang memerlukan izin khusus tanpa role yang sesuai, sistem akan menampilkan halaman *forbidden* (403).

---

### 4.2 Lupa Password

> ⚠️ **Fitur reset password via email belum diaktifkan** pada versi ini.

Jika pengguna lupa password, proses penggantian harus dilakukan melalui administrator sistem. Administrator dapat mereset password pengguna melalui:

1. Masuk ke menu **Manajemen Pengguna** (sidebar navigasi).
2. Cari dan buka data pengguna yang bersangkutan.
3. Klik tombol **Edit**.
4. Isi kolom **Kata Sandi** dengan password baru.
5. Klik **Simpan**.

> Password baru akan langsung berlaku. Informasikan password baru tersebut kepada pengguna terkait secara langsung.

---

### 4.3 Edit Profil & Foto Avatar

Setiap pengguna dapat mengubah data profilnya sendiri tanpa memerlukan bantuan administrator.

**Cara membuka halaman profil:**

1. Klik **nama pengguna / ikon akun** di pojok kanan atas panel (user menu).
2. Pilih menu yang menampilkan nama pengguna yang sedang login.
3. Sistem akan membuka halaman **Edit Profil**.

**Informasi yang dapat diubah pada halaman profil:**

| Bagian | Yang Dapat Diubah |
|---|---|
| **Informasi Umum** | Nama lengkap |
| **Alamat Email** | Email yang digunakan untuk login |
| **Foto Avatar** | Unggah foto profil (disimpan di server) |
| **Sesi Browser** | Lihat & akhiri sesi login yang aktif di perangkat lain |
| **API Tokens** | Kelola token API (untuk kebutuhan integrasi) |

**Cara mengubah foto avatar:**

1. Pada halaman Edit Profil, scroll ke bagian **Avatar**.
2. Klik area foto atau tombol upload.
3. Pilih file gambar dari perangkat (format JPG/PNG disarankan).
4. Klik **Simpan** untuk menyimpan perubahan.

> Foto avatar akan tampil di pojok kanan atas panel menggantikan inisial nama pengguna.

**Cara mengakhiri sesi browser lain:**

1. Pada halaman Edit Profil, scroll ke bagian **Browser Sessions**.
2. Daftar perangkat/sesi yang sedang aktif akan ditampilkan beserta informasi browser dan lokasi.
3. Klik tombol **Logout Other Browser Sessions** untuk mengakhiri semua sesi selain sesi saat ini.
4. Masukkan password untuk konfirmasi.

> Fitur ini berguna jika pengguna pernah login di komputer publik atau perangkat lain yang lupa di-logout.

---

### 4.4 Logout

Untuk keluar dari sistem dengan aman:

1. Klik **nama pengguna / ikon akun** di pojok kanan atas panel.
2. Pilih menu **Sign out** (Keluar).
3. Sistem akan mengakhiri sesi dan mengarahkan kembali ke halaman login.

> **Penting bagi kasir:** Pastikan untuk selalu **menutup sesi POS terlebih dahulu** sebelum logout, agar data kas dan transaksi tercatat dengan benar. Logout tanpa menutup sesi POS akan menyebabkan sesi tetap terbuka dan terminal tidak dapat digunakan kasir lain.

---

### 4.5 Keamanan Akun

Beberapa hal yang perlu diperhatikan untuk menjaga keamanan akun:

- **Jangan bagikan password** kepada orang lain, termasuk sesama rekan kerja.
- **Segera ganti password** jika merasa akun telah diakses oleh pihak yang tidak berwenang — minta bantuan administrator.
- **Gunakan fitur Browser Sessions** untuk memantau dan mengakhiri sesi yang mencurigakan.
- **Jangan login di komputer publik** tanpa melakukan logout setelah selesai.
- Sistem menggunakan **session berbasis cookie** yang terenkripsi — sesi akan berakhir otomatis setelah browser ditutup (tergantung konfigurasi server).

---

## BAB 5 — Dashboard

Dashboard adalah halaman utama yang tampil pertama kali setelah login. Halaman ini menyajikan ringkasan kondisi operasional toko secara *real-time* dalam satu tampilan, sehingga pengelola dapat memantau performa penjualan dengan cepat tanpa perlu membuka laporan secara manual.

Dashboard terdiri dari **5 komponen** yang tersusun dari atas ke bawah:

---

### 5.1 Widget Statistik Hari Ini

Widget ini berada paling atas dashboard dan menampilkan **3 kartu ringkasan** yang mencerminkan kondisi transaksi pada hari berjalan (hari ini).

> Data pada ketiga kartu ini hanya menghitung pesanan dengan status **Selesai (completed)** dan status pembayaran **Lunas (paid)**.

---

**🟡 Total Pesanan**

Menampilkan **jumlah transaksi** yang berhasil diselesaikan hari ini.

- Dihitung dari total pesanan dengan `status = completed` dan `payment_status = paid` yang dibuat pada hari ini.
- Contoh tampilan: `12` (artinya ada 12 pesanan selesai hari ini)
- Berguna untuk memantau seberapa ramai aktivitas penjualan dalam sehari.

---

**🟢 Pendapatan**

Menampilkan **total uang yang diterima** dari seluruh transaksi lunas hari ini.

- Dihitung dari penjumlahan kolom `total_payment` seluruh pesanan selesai hari ini.
- Nilai ditampilkan dalam format mata uang sesuai pengaturan sistem (default: IDR).
- Contoh tampilan: `IDR 1.250.000`
- Berguna untuk mengetahui omzet harian secara instan.

---

**🟠 Produk Terjual**

Menampilkan **total unit produk** yang terjual hari ini dari seluruh transaksi selesai.

- Dihitung dari penjumlahan kolom `quantity` pada semua item pesanan yang terkait dengan pesanan selesai hari ini.
- Contoh tampilan: `47` (artinya 47 unit produk terjual hari ini)
- Berguna untuk memantau volume penjualan dan kebutuhan restok.

---

### 5.2 Grafik Pesanan (Order Chart)

Widget ini menampilkan **grafik garis (line chart)** yang memvisualisasikan tren jumlah pesanan dari hari ke hari.

**Cara membaca grafik:**
- Sumbu **horizontal (X)** menampilkan tanggal (format: `Mon DD`, contoh: `Jun 07`)
- Sumbu **vertikal (Y)** menampilkan jumlah pesanan per hari
- Garis menunjukkan fluktuasi volume pesanan — naik berarti hari tersebut lebih ramai dari sebelumnya

**Filter periode yang tersedia:**

| Filter | Keterangan |
|---|---|
| **7 Hari Terakhir** | Menampilkan data 7 hari ke belakang dari hari ini (default) |
| **30 Hari Terakhir** | Menampilkan data sebulan terakhir |
| **90 Hari Terakhir** | Menampilkan data tiga bulan terakhir |

Klik dropdown filter di pojok kanan atas widget untuk mengubah periode tampilan.

> Grafik ini membantu mendeteksi pola tren penjualan — misalnya hari apa paling ramai, apakah ada penurunan, atau adanya lonjakan penjualan di tanggal tertentu.

---

### 5.3 Grafik Metode Pembayaran (Payment Method Chart)

Widget ini menampilkan **grafik donat (doughnut chart)** yang memvisualisasikan distribusi transaksi berdasarkan metode pembayaran yang digunakan pelanggan.

**Cara membaca grafik:**
- Setiap **potongan/segmen** mewakili satu metode pembayaran
- **Ukuran segmen** mencerminkan proporsi jumlah transaksi yang menggunakan metode tersebut
- **Legenda warna** di bawah grafik menjelaskan nama metode pembayaran masing-masing segmen

**Filter periode yang tersedia:**

| Filter | Keterangan |
|---|---|
| **7 Hari Terakhir** | Distribusi pembayaran 7 hari terakhir (default) |
| **30 Hari Terakhir** | Distribusi pembayaran 30 hari terakhir |
| **90 Hari Terakhir** | Distribusi pembayaran 90 hari terakhir |

- Hanya pesanan dengan status **Selesai** dan **Lunas** yang dihitung.
- Metode pembayaran yang belum pernah digunakan tidak akan muncul di grafik.

> Grafik ini berguna untuk mengetahui preferensi pembayaran pelanggan — misalnya apakah mayoritas membayar tunai, QRIS, atau transfer bank — sehingga dapat digunakan sebagai bahan evaluasi penyediaan layanan pembayaran.

---

### 5.4 Tabel Produk Terlaris (Best Selling Products)

Widget ini menampilkan **5 produk dengan penjualan tertinggi** berdasarkan jumlah unit yang terjual.

**Kolom yang ditampilkan:**

| Kolom | Keterangan |
|---|---|
| **Produk** | Nama produk |
| **Qty Terjual** | Total unit terjual dalam periode yang dipilih |
| **Pendapatan** | Total pendapatan dari produk tersebut dalam periode yang dipilih |

**Filter periode yang tersedia:**

| Filter | Keterangan |
|---|---|
| **7 Hari Terakhir** | Top 5 produk 7 hari terakhir |
| **30 Hari Terakhir** | Top 5 produk 30 hari terakhir |
| **90 Hari Terakhir** | Top 5 produk 90 hari terakhir |

- Klik ikon filter (🔽) di pojok kanan atas tabel untuk mengubah periode.
- Hanya transaksi dengan status **Selesai** dan **Lunas** yang dihitung.
- Tabel dapat diurutkan dengan mengklik judul kolom **Qty Terjual** atau **Pendapatan**.

> Informasi ini berguna untuk menentukan produk mana yang perlu diprioritaskan dalam pengadaan stok dan strategi promosi.

---

### 5.5 Tabel Pesanan Terbaru (Latest Orders)

Widget ini menampilkan **5 pesanan yang paling baru dibuat**, tanpa batasan status, sehingga termasuk pesanan yang masih pending atau dibatalkan.

**Kolom yang ditampilkan:**

| Kolom | Keterangan |
|---|---|
| **No. Pesanan** | Nomor unik pesanan (dapat disalin dengan klik ikon copy) |
| **Pelanggan** | Nama pelanggan terkait pesanan |
| **Total** | Nominal total pembayaran pesanan |
| **Status** | Status pesanan ditampilkan sebagai badge berwarna |
| **Tanggal** | Waktu pesanan dibuat (format: `DD Bulan YYYY, HH:mm`) |

**Keterangan badge status:**

| Status | Warna | Arti |
|---|---|---|
| `completed` | 🟢 Hijau | Pesanan selesai dan lunas |
| `pending` | 🟡 Kuning | Pesanan menunggu pembayaran atau konfirmasi |
| `cancelled` | 🔴 Merah | Pesanan dibatalkan |

**Tombol aksi:**
- Klik **"Lihat Semua Pesanan"** di pojok kanan atas widget untuk langsung berpindah ke halaman daftar lengkap semua pesanan.

> Widget ini memudahkan staf memantau transaksi terbaru secara cepat langsung dari Dashboard tanpa perlu masuk ke menu Pesanan.

---

### 5.6 Catatan Akses Widget

Seluruh widget pada Dashboard dilindungi oleh sistem **role & permission** (Filament Shield). Widget hanya ditampilkan kepada pengguna yang memiliki izin `view` pada widget tersebut sesuai role yang dimiliki. Jika suatu widget tidak muncul, kemungkinan besar role pengguna tersebut belum diberikan akses ke widget yang bersangkutan — hubungi administrator untuk pengaturan izin.

## BAB 6 — Manajemen Produk

Modul Manajemen Produk berada di grup navigasi **Manajemen Produk** pada sidebar. Modul ini mencakup empat sub-modul: Merek, Kategori, Sub Kategori, dan Produk. Keempatnya harus dikelola secara berurutan karena Produk bergantung pada Merek, Kategori, dan Sub Kategori.

---

### 6.1 Merek (Brand)

Menu **Merek** digunakan untuk mengelola daftar merek/pabrikan produk.

**Kolom data merek:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama merek (contoh: Indofood, Unilever) |
| Deskripsi | Deskripsi singkat merek (opsional) |
| Gambar | Logo atau gambar merek (opsional) |
| Status Aktif | Menentukan apakah merek dapat digunakan (`true`/`false`) |

**Menambah merek:**

1. Buka menu **Merek** dari sidebar.
2. Klik tombol **New Brand**.
3. Isi kolom **Nama** (wajib).
4. Isi **Deskripsi** jika diperlukan.
5. Unggah **Gambar** jika diperlukan.
6. Pastikan **Is Active** dalam keadaan aktif (centang).
7. Klik **Create**.

**Melihat detail merek:**

1. Pada halaman daftar merek, klik nama merek yang ingin dilihat.
2. Halaman detail akan menampilkan seluruh informasi merek.

**Mengubah merek:**

1. Pada halaman daftar merek, klik ikon pensil (✏️) pada baris merek — atau buka detail merek lalu klik **Edit**.
2. Ubah data yang diperlukan.
3. Klik **Save Changes**.

**Menonaktifkan merek:**

1. Buka form edit merek.
2. Matikan toggle **Is Active**.
3. Klik **Save Changes**.

> Merek yang dinonaktifkan tidak akan muncul di pilihan merek saat membuat produk baru, namun produk yang sudah terhubung tetap berfungsi normal.

---

### 6.2 Kategori (Category)

Menu **Kategori** digunakan untuk mengelompokkan produk berdasarkan jenisnya.

**Kolom data kategori:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama kategori (contoh: Makanan, Minuman, Elektronik) |
| Deskripsi | Deskripsi singkat (opsional) |
| Gambar | Gambar kategori (opsional) |
| Status Aktif | Status aktif/tidak |

Operasi **Tambah**, **Lihat**, **Ubah**, dan **Nonaktifkan/Aktifkan** sama seperti pada menu Merek (6.1).

---

### 6.3 Sub Kategori (Sub Category)

Menu **Sub Kategori** digunakan untuk pengelompokan yang lebih spesifik di bawah Kategori.

**Kolom data sub kategori:**

| Kolom | Keterangan |
|---|---|
| Kategori Induk | Kategori yang menaungi sub kategori ini (wajib) |
| Nama | Nama sub kategori (contoh: Makanan Ringan, Minuman Bersoda) |
| Deskripsi | Deskripsi singkat (opsional) |
| Gambar | Gambar (opsional) |
| Status Aktif | Status aktif/tidak |

**Menambah sub kategori:**

1. Buka menu **Sub Kategori** dari sidebar.
2. Klik **New Sub Category**.
3. Pilih **Kategori Induk** dari dropdown.
4. Isi **Nama** sub kategori.
5. Lengkapi field opsional lainnya.
6. Klik **Create**.

> Pastikan Kategori Induk sudah dibuat terlebih dahulu sebelum membuat Sub Kategori.

---

### 6.4 Produk (Product)

Menu **Produk** adalah inti dari modul manajemen produk. Setiap produk yang dijual harus didaftarkan di sini sebelum dapat ditransaksikan di POS Terminal.

**Kolom data produk:**

| Kolom | Keterangan | Wajib |
|---|---|---|
| Nama | Nama produk | ✅ |
| SKU | *Stock Keeping Unit* — kode unik internal produk | Tidak (tapi disarankan) |
| Barcode | Kode batang produk (dapat dipindai di POS) | Tidak |
| Harga Jual | Harga jual ke pelanggan (dalam mata uang sistem) | ✅ |
| Harga Dasar | Harga beli/modal produk (untuk referensi) | Tidak |
| Gambar | Foto produk | Tidak |
| Merek | Merek produk (dari daftar merek) | Tidak |
| Kategori | Kategori produk | Tidak |
| Sub Kategori | Sub kategori produk | Tidak |
| Status Aktif | Status aktif — jika nonaktif, produk tidak muncul di POS | ✅ |
| Stok Saat Ini | Stok terkini produk (dihitung otomatis, tidak bisa diedit manual) | (Otomatis) |

**Stok produk dihitung secara dinamis** berdasarkan seluruh catatan **Stock Move** (pergerakan stok) yang terkait. Sistem tidak memiliki kolom stok statis — setiap penjualan dan penyesuaian stok akan mencatat Stock Move `in` (masuk) atau `out` (keluar), dan stok terkini adalah selisih total `in` dikurangi total `out` dengan status `done`.

**Menambah produk:**

1. Buka menu **Produk** dari sidebar.
2. Klik **New Product**.
3. Isi **Nama** produk.
4. (Disarankan) Isi **SKU** — kode unik untuk identifikasi produk.
5. (Disarankan) Isi **Barcode** — dapat dipindai dengan barcode scanner di POS.
6. Isi **Harga Jual** (Price) — harga yang dibayar pelanggan.
7. (Opsional) Isi **Harga Dasar** (Base Price) — untuk referensi margin.
8. Unggah **Gambar** jika ada.
9. Pilih **Merek**, **Kategori**, dan **Sub Kategori** dari dropdown.
10. Pastikan **Is Active** aktif.
11. Klik **Create**.

**Melihat detail produk:**

1. Pada halaman daftar produk, klik nama produk.
2. Halaman detail menampilkan seluruh informasi produk termasuk **Stok Saat Ini** yang dihitung otomatis.

**Mengubah produk:**

1. Buka detail produk lalu klik **Edit**, atau klik ikon edit pada baris produk.
2. Ubah data yang diperlukan.
3. Klik **Save Changes**.

> Stok tidak dapat diubah langsung dari form edit produk. Untuk menambah atau mengurangi stok, gunakan modul **Penyesuaian Stok** (Bab 9).

**Menonaktifkan / mengaktifkan produk:**

1. Buka form edit produk.
2. Matikan atau nyalakan toggle **Is Active**.
3. Klik **Save Changes**.

> Produk yang dinonaktifkan **tidak akan muncul di pencarian POS Terminal**. Jika produk masih memiliki stok dan sedang dalam keranjang transaksi aktif, produk tetap dapat diproses.

---

## BAB 7 — Manajemen Pelanggan

Menu **Pelanggan** di sidebar (grup Point of Sale) digunakan untuk mengelola data pelanggan yang dapat dipilih saat transaksi di POS Terminal.

### 7.1 Data Pelanggan

**Kolom data pelanggan:**

| Kolom | Keterangan | Wajib |
|---|---|---|
| Nama | Nama lengkap pelanggan | ✅ |
| Email | Alamat email (harus unik jika diisi) | Tidak |
| Telepon | Nomor telepon (harus unik jika diisi) | Tidak |
| Alamat | Alamat pelanggan | Tidak |

### 7.2 Menambah Pelanggan

**Melalui panel admin:**

1. Buka menu **Pelanggan** dari sidebar.
2. Klik **New Customer**.
3. Isi **Nama** pelanggan (wajib).
4. (Opsional) Isi **Email**, **Telepon**, dan **Alamat**.
5. Klik **Create**.

**Langsung dari POS Terminal:**

Pelanggan juga dapat dibuat langsung saat transaksi melalui POS tanpa perlu membuka panel admin. Lihat **Bab 8.3.3 — Memilih Pelanggan**.

### 7.3 Melihat Detail Pelanggan

1. Pada daftar pelanggan, klik nama pelanggan.
2. Halaman detail menampilkan informasi pelanggan serta daftar pesanan (orders) yang pernah dilakukan.

### 7.4 Mengubah Data Pelanggan

1. Buka detail pelanggan lalu klik **Edit**.
2. Ubah data yang diperlukan.
3. Klik **Save Changes**.

### 7.5 Menghapus Pelanggan

1. Pada halaman daftar pelanggan, klik ikon hapus (🗑️) pada baris pelanggan.
2. Konfirmasi penghapusan.

> Pelanggan yang memiliki riwayat pesanan sebaiknya tidak dihapus agar data pesanan tetap utuh.

### 7.6 Pelanggan Default

Sistem memiliki pengaturan **Pelanggan Default** (di menu **Settings > General**) yang akan otomatis terpilih saat kasir tidak memilih pelanggan tertentu di POS Terminal. Fitur ini berguna untuk transaksi dengan pelanggan tidak dikenal (*walk-in customer*).

---

## BAB 8 — Point of Sale

Modul Point of Sale adalah jantung operasional SwiftPos. Modul ini mencakup manajemen terminal kasir, sesi kasir, antarmuka transaksi, pemrosesan pembayaran, dan riwayat pesanan.

---

### 8.1 Terminal Kasir (Cashier)

Menu **Terminal Kasir** digunakan untuk mendaftarkan terminal/pos kasir yang tersedia di toko. Setiap terminal memiliki kode unik yang digunakan untuk membuka sesi POS.

**Kolom data terminal:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama terminal (contoh: Kasir 1, Kasir 2) |
| Kode | Kode unik terminal (contoh: `CS01`, `CS02`) |
| Deskripsi | Keterangan tambahan (opsional) |
| Status Aktif | Status terminal aktif/nonaktif |

**Menambah terminal:**

1. Buka menu **Terminal Kasir** dari sidebar.
2. Klik **New Cashier**.
3. Isi **Nama** terminal.
4. Isi **Kode** — unik, tidak boleh sama dengan terminal lain.
5. Isi **Deskripsi** jika perlu.
6. Pastikan **Is Active** aktif.
7. Klik **Create**.

**Mengubah terminal:**

1. Pada daftar terminal, klik ikon edit.
2. Ubah data yang diperlukan.
3. Klik **Save Changes**.

**Menonaktifkan terminal:**

1. Edit terminal, matikan toggle **Is Active**.
2. Klik **Save Changes**.

> Terminal yang dinonaktifkan **tidak dapat digunakan untuk membuka sesi POS baru**. Sesi yang sedang berjalan pada terminal tersebut tetap dapat ditutup.

---

### 8.2 Membuka Sesi POS

Sebelum melakukan transaksi, kasir harus membuka sesi pada terminal yang dipilih. Setiap terminal hanya dapat memiliki **satu sesi terbuka** dalam satu waktu. Jika terminal sedang digunakan kasir lain, sistem akan menampilkan peringatan.

**Langkah membuka sesi POS:**

1. Akses URL POS Terminal dengan menyertakan ID terminal:
   ```
   http://[domain-anda]/pos?cashier_id=[ID_TERMINAL]
   ```
   > ID terminal dapat dilihat dari URL saat mengakses daftar terminal di panel admin.

2. Sistem akan menampilkan halaman **Buka Sesi**. Informasi yang ditampilkan:
   - Nama terminal yang dipilih
   - Status terminal (harus tersedia/tidak sedang digunakan)

3. Masukkan **Saldo Awal (Opening Balance)** — jumlah uang tunai yang ada di laci kasir saat memulai shift.

4. Klik tombol **Buka Sesi** / **Open Session**.

5. Jika berhasil, antarmuka POS Terminal akan tampil dan fase berubah menjadi **Operasional**.

**Hal yang perlu diperhatikan:**
- Saldo awal harus diisi dengan benar karena akan digunakan saat rekonsiliasi penutupan sesi.
- Jika terminal sedang digunakan kasir lain, akan muncul pesan: *"Terminal sedang digunakan oleh [Nama Kasir]."* Hubungi kasir tersebut untuk menutup sesinya terlebih dahulu.
- Jika terminal dinonaktifkan, sistem akan menampilkan error **403 Forbidden**.

---

### 8.3 Transaksi di POS Terminal

Setelah sesi terbuka, antarmuka POS Terminal siap digunakan untuk transaksi. Tampilan POS **tidak memiliki sidebar** agar layar penuh untuk efisiensi operasional.

**Komponen antarmuka POS:**

| Area | Isi |
|---|---|
| Panel Kiri | Area keranjang belanja — daftar item, total, diskon, pelanggan |
| Panel Kanan | Pencarian produk, daftar produk aktif, dan tombol aksi |

#### 8.3.1 Mencari dan Menambah Produk ke Keranjang

**Cara mencari produk:**

1. Gunakan kolom **Cari Produk** di panel kanan.
2. Pencarian dapat dilakukan berdasarkan:
   - **Nama produk** — ketik sebagian atau seluruh nama
   - **SKU** — kode SKU produk
   - **Barcode** — kode barcode (atau hasil scan barcode scanner)
3. Hasil pencarian menampilkan produk aktif yang sesuai beserta **stok terkini** dan **harga jual**.
4. Klik produk hasil pencarian untuk menambahkannya ke keranjang.

> Barcode scanner yang terhubung sebagai **HID keyboard** akan otomatis mengetikkan kode barcode ke kolom pencarian dan menekan Enter — produk akan langsung masuk keranjang.

**Menambah produk ke keranjang:**

- Klik produk dari hasil pencarian.
- Produk akan muncul di keranjang dengan jumlah awal **1** dan subtotal sesuai harga jual.
- Jika produk yang sama ditambahkan lagi, jumlahnya akan bertambah (increment).

#### 8.3.2 Mengelola Item di Keranjang

Setiap item di keranjang memiliki kontrol:

| Tombol | Fungsi |
|---|---|
| **+** | Menambah jumlah item (+1) |
| **−** | Mengurangi jumlah item (−1) |
| **🗑️** | Menghapus item dari keranjang |

Sistem akan selalu memvalidasi stok produk saat menambah jumlah — jika stok tidak mencukupi, penambahan akan ditolak.

#### 8.3.3 Memilih Pelanggan

1. Klik tombol **Pilih Pelanggan** di area keranjang.
2. Modal pencarian pelanggan akan muncul.
3. Cari pelanggan berdasarkan **Nama**, **Email**, atau **Telepon**.
4. Klik pelanggan yang dipilih.
5. Jika tidak memilih pelanggan, sistem akan otomatis menggunakan **Pelanggan Default** dari pengaturan.

**Membuat pelanggan baru langsung dari POS:**

Jika pelanggan belum terdaftar, kasir dapat menambahkan pelanggan baru tanpa meninggalkan antarmuka POS:

1. Buka modal **Pilih Pelanggan**.
2. Klik tombol **"+ Pelanggan Baru"** di bagian bawah modal.
3. Isi minimal **Nama** pelanggan (wajib).
4. (Opsional) Isi **Nomor HP**, **Email**, dan **Alamat**.
5. Klik **Simpan Pelanggan**.
6. Pelanggan langsung terpilih dan siap untuk transaksi.

#### 8.3.4 Memberikan Diskon

1. Klik tombol **Diskon** di area keranjang.
2. Masukkan persentase diskon (0–100%).
3. Sistem akan otomatis menghitung:
   - Potongan diskon = Total × (Diskon % / 100)
   - Total setelah diskon

#### 8.3.5 Memilih Metode Pembayaran

Setelah keranjang siap, pilih metode pembayaran:

1. Klik dropdown **Metode Pembayaran**.
2. Pilih salah satu metode yang tersedia:
   - **Cash** — pembayaran tunai
   - **Credit/Debit Card (EDC)** — pembayaran kartu via mesin EDC
   - **QRIS** — pembayaran via QR Code (offline dengan QR statis, atau online dengan Xendit)
   - Metode lain sesuai yang telah dikonfigurasi
3. Jika metode memiliki **biaya pembayaran** (payment fee), biaya tersebut akan otomatis ditambahkan ke total.

#### 8.3.6 Pembayaran Tunai (Cash)

1. Pilih metode **Cash**.
2. Sistem menampilkan **Total Pembayaran** yang harus dibayar.
3. Kasir memasukkan **Jumlah Uang Diterima** (*Cash Paid*).
4. Sistem otomatis menghitung **Kembalian** (*Change*).
5. Jika uang yang dimasukkan kurang dari total, sistem akan menolak dan menampilkan peringatan.
6. Klik **Proses Pembayaran** / **Bayar**.

**Hasil setelah pembayaran tunai berhasil:**
- Pesanan dibuat dengan status **completed** (selesai)
- Status pembayaran **paid** (lunas)
- Stock Move `out` otomatis dibuat untuk setiap item (status `done`)
- Modal struk ditampilkan

#### 8.3.7 Pembayaran Non-Tunai (Kartu EDC)

1. Pilih metode **Credit/Debit Card (EDC)**.
2. Klik **Proses Pembayaran**.
3. Lakukan transaksi di mesin EDC seperti biasa.
4. Setelah mesin EDC mengonfirmasi pembayaran, pesanan akan diproses.

> Pembayaran via EDC bersifat **offline** — sistem tidak terhubung langsung ke bank. Status pesanan akan langsung `completed` setelah kasir mengonfirmasi.

---

### 8.4 Pembayaran Xendit (QRIS & Virtual Account)

SwiftPos terintegrasi dengan **Xendit Payment Gateway** untuk memproses pembayaran non-tunai secara online. Fitur ini harus diaktifkan terlebih dahulu di menu **Settings → Xendit** (Bab 12.2).

**Prasyarat:**
- Xendit telah diaktifkan dan API key sudah dikonfigurasi
- Metode pembayaran online (QRIS/Virtual Account) telah disiapkan dengan Xendit Channel Code yang sesuai

#### 8.4.1 Alur Pembayaran QR Code (QRIS via Xendit)

1. Pada POS Terminal, pilih metode pembayaran bertipe **QR Code** (misalnya QRIS).
2. Klik **Proses Pembayaran**.
3. Sistem akan mengirim permintaan ke Xendit API untuk membuat **QR Code dinamis**.
4. Modal pembayaran akan menampilkan:
   - **QR Code** — dapat dipindai pelanggan menggunakan aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay, dll.)
   - **Jumlah yang harus dibayar**
   - **Waktu kedaluwarsa** — 30 menit sejak QR dibuat
5. Pelanggan memindai QR Code menggunakan aplikasi pembayaran.
6. Sistem secara otomatis melakukan **polling** (pengecekan berkala) ke Xendit untuk memeriksa status pembayaran.
7. Setelah pembayaran dikonfirmasi:
   - Status pesanan berubah menjadi `completed`
   - Status pembayaran berubah menjadi `paid`
   - Stock Move dibuat

**Tombol yang tersedia saat menunggu pembayaran:**

| Tombol | Fungsi |
|---|---|
| **Cek Status** | Memeriksa status pembayaran secara manual ke Xendit |
| **Batal** | Membatalkan pesanan (status menjadi `cancelled`) |

#### 8.4.2 Alur Pembayaran Virtual Account

1. Pilih metode pembayaran bertipe **Virtual Account** (misalnya BCA VA).
2. Klik **Proses Pembayaran**.
3. Sistem akan membuat **Virtual Account sekali pakai** (*single-use, closed*) melalui Xendit API.
4. Modal pembayaran akan menampilkan:
   - **Nomor Virtual Account** — disalin dan diberikan ke pelanggan
   - **Nama Bank** (contoh: BCA, BNI, BRI, Mandiri)
   - **Jumlah yang harus dibayar**
   - **Waktu kedaluwarsa** — 24 jam sejak VA dibuat
5. Pelanggan melakukan transfer ke nomor VA tersebut melalui mobile banking / ATM.
6. Sistem melakukan polling otomatis ke Xendit.
7. Setelah pembayaran terkonfirmasi, status pesanan berubah menjadi `completed`.

#### 8.4.3 Polling Otomatis

Saat menunggu pembayaran Xendit, sistem melakukan polling otomatis setiap beberapa detik untuk memeriksa status pembayaran:

- **QR Code**: Dicek melalui Xendit Transactions API untuk akurasi (karena QR bisa menjadi INACTIVE/EXPIRED meskipun sudah dibayar)
- **Virtual Account**: Dicek melalui endpoint status VA

#### 8.4.4 Penanganan Pembayaran Gagal / Kedaluwarsa

Jika pembayaran gagal atau kedaluwarsa:
- Status pembayaran berubah menjadi `failed`
- Status pesanan berubah menjadi `cancelled`
- Stok yang sudah direservasi (Stock Move draft) akan dikembalikan (status `cancelled`)
- Pesan notifikasi akan ditampilkan di antarmuka POS

#### 8.4.5 Webhook Xendit

Selain polling, sistem juga menerima notifikasi **webhook** dari Xendit saat terjadi pembayaran. Webhook diproses di endpoint:
```
https://[domain-anda]/api/webhooks/xendit
```
Webhook diverifikasi menggunakan **Webhook Token** yang dikonfigurasi di Settings. Pastikan URL webhook ini sudah didaftarkan di **Xendit Dashboard** (lihat Bab 12.2).

#### 8.4.6 Simulasi Pembayaran (Sandbox)

Saat environment Xendit diatur ke **Sandbox**, tombol **Simulasi Pembayaran** akan muncul di modal pembayaran. Tombol ini mensimulasikan pembayaran sukses untuk keperluan testing.

> Simulasi QR Code hanya mendukung channel **ID_DANA** di Xendit Sandbox. Jika menggunakan channel ID_QRIS, buat payment method dengan Xendit Channel Code = `ID_DANA` untuk testing.

---

### 8.5 Struk / Receipt

Setelah transaksi berhasil, modal **Struk** akan otomatis ditampilkan.

**Informasi yang ditampilkan di struk:**

| Bagian | Isi |
|---|---|
| Header | Nama toko, alamat, telepon |
| Nomor Pesanan | Format: `O/YYYY/MM/NNNN` |
| Tanggal & Waktu | Waktu transaksi |
| Kasir | Nama kasir (user) |
| Pelanggan | Nama pelanggan |
| Daftar Item | Nama produk, jumlah, harga satuan, subtotal |
| Subtotal | Total sebelum diskon |
| Diskon | Persentase & nilai diskon |
| Biaya Pembayaran | Biaya metode pembayaran (jika ada) |
| Total | Total yang harus dibayar |
| Tunai | Jumlah uang diterima (pembayaran cash) |
| Kembalian | Kembalian (pembayaran cash) |
| Metode Bayar | Metode pembayaran yang digunakan |
| Footer | Teks footer dari pengaturan toko |

Struk dapat ditutup dengan klik tombol **Tutup** atau area di luar modal. Setelah struk ditutup, antarmuka POS kembali ke keadaan awal (keranjang kosong) dan siap untuk transaksi berikutnya.

---

### 8.6 Menutup Sesi POS

Di akhir shift, kasir wajib menutup sesi POS untuk menyelesaikan rekonsiliasi kas.

**Langkah menutup sesi:**

1. Di antarmuka POS Terminal, klik tombol **Tutup Sesi** / **Close Session**.
2. Antarmuka akan berubah ke fase **Penutupan Sesi**.

**Informasi yang ditampilkan:**

| Informasi | Keterangan |
|---|---|
| Saldo Awal | Opening balance yang diinput saat buka sesi |
| Total Penjualan Tunai | Jumlah seluruh pembayaran tunai yang diterima selama sesi |
| Saldo yang Diharapkan | Opening Balance + Total Penjualan Tunai (Expected Balance) |

3. Hitung secara fisik uang tunai di laci kasir.
4. Masukkan **Saldo Aktual** (Actual Balance) — jumlah uang yang benar-benar ada.
5. Sistem akan otomatis menghitung **Selisih Kas**:
   ```
   Selisih = Saldo Diharapkan − Saldo Aktual
   ```
   - Nilai positif = uang kurang (defisit)
   - Nilai negatif = uang lebih (surplus)
   - Nilai nol = seimbang

6. Jika terdapat selisih, kolom **Catatan Penutupan** (Closing Notes) wajib diisi untuk menjelaskan penyebab selisih.
7. Klik **Tutup Sesi** / **Close Session**.

**Hasil setelah penutupan:**
- Status sesi berubah menjadi `closed`
- Sesi tidak lagi muncul di antarmuka POS
- Terminal dapat digunakan kasir lain untuk membuka sesi baru

> **Penting:** Kasir harus menutup sesi POS sebelum logout. Logout tanpa menutup sesi akan menyebabkan sesi tetap terbuka dan terminal terkunci.

---

### 8.7 Riwayat Sesi POS (POS Sessions)

Menu **Riwayat Sesi** menampilkan seluruh sesi POS yang pernah dibuka dan ditutup. Menu ini bersifat **read-only** — tidak dapat dibuat atau diedit secara manual.

**Kolom yang ditampilkan:**

| Kolom | Keterangan |
|---|---|
| Terminal | Nama terminal kasir |
| Kasir | Nama pengguna (user) yang membuka sesi |
| Dibuka | Waktu sesi dibuka |
| Ditutup | Waktu sesi ditutup (kosong jika masih terbuka) |
| Saldo Awal | Opening balance |
| Saldo Diharapkan | Expected balance (opening + cash sales) |
| Saldo Aktual | Actual balance (input kasir saat tutup) |
| Selisih | Difference (expected − actual) |
| Catatan | Closing notes |
| Status | `open` atau `closed` |

**Melihat detail sesi:**

1. Klik baris sesi pada tabel.
2. Halaman detail menampilkan seluruh informasi sesi termasuk daftar pesanan yang terkait dengan sesi tersebut.

---

### 8.8 Pesanan (Orders)

Menu **Pesanan** menampilkan seluruh transaksi yang pernah dilakukan di sistem, baik dari POS Terminal maupun yang disinkronkan dari aplikasi mobile (PWA). Di halaman daftar pesanan terdapat **widget statistik** (total pesanan, pendapatan, produk terjual hari ini).

**Kolom yang ditampilkan:**

| Kolom | Keterangan |
|---|---|
| No. Pesanan | Nomor unik pesanan — format `O/YYYY/MM/NNNN` |
| Pelanggan | Nama pelanggan |
| Kasir | Nama kasir / terminal |
| Total | Total pembayaran (setelah diskon + biaya) |
| Status | Status pesanan — badge berwarna |
| Status Bayar | Status pembayaran — badge berwarna |
| Metode Bayar | Metode pembayaran yang digunakan |
| Tanggal | Waktu pesanan dibuat |

**Status pesanan (badge):**

| Status | Warna | Arti |
|---|---|---|
| `new` | 🔵 Biru | Pesanan baru dibuat, menunggu pembayaran |
| `processing` | 🟡 Kuning | Pembayaran sedang diproses (Xendit: menunggu konfirmasi) |
| `completed` | 🟢 Hijau | Pesanan selesai dan pembayaran lunas |
| `cancelled` | 🔴 Merah | Pesanan dibatalkan |

**Status pembayaran (badge):**

| Status | Warna | Arti |
|---|---|---|
| `paid` | 🟢 Hijau | Pembayaran lunas |
| `unpaid` | 🟡 Kuning | Belum dibayar |
| `failed` | 🔴 Merah | Pembayaran gagal |

**Filter pesanan:**

Pesanan dapat difilter berdasarkan:
- Status pesanan (new, processing, completed, cancelled)
- Tanggal pesanan
- Kasir / terminal
- Metode pembayaran

**Melihat detail pesanan:**

1. Klik nomor pesanan pada tabel.
2. Halaman detail menampilkan:
   - Informasi umum pesanan (nomor, tanggal, status)
   - Data pelanggan
   - Data kasir dan sesi POS
   - **Daftar item pesanan** — nama produk, jumlah, harga, subtotal
   - Informasi pembayaran:
     - **Tunai**: cash paid, change amount
     - **Xendit**: external ID, invoice ID, QR string/VA number, checkout URL, waktu kedaluwarsa

**Membuat pesanan manual:**

1. Klik **New Order** di halaman daftar pesanan.
2. Pilih pelanggan, kasir, dan sesi POS.
3. Tambahkan item pesanan (produk, jumlah).
4. Isi data pembayaran.
5. Klik **Create**.

> Fitur ini berguna untuk mencatat pesanan yang terjadi di luar POS Terminal atau untuk koreksi data.

---

## BAB 9 — Manajemen Inventaris

Modul inventaris mengelola stok produk melalui dua mekanisme: **Penyesuaian Stok** (manual) dan **Pergerakan Stok** (riwayat otomatis).

---

### 9.1 Penyesuaian Stok (Inventory Adjustment)

Penyesuaian stok digunakan untuk mengoreksi atau menyesuaikan stok produk secara manual — misalnya untuk mencatat stok awal, stok hasil opname, barang rusak, atau barang masuk dari supplier.

**Siklus penyesuaian stok:**
```
Draft → (isi detail) → Validasi → Done → Stok terupdate
```

**Kolom penyesuaian stok:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama/judul penyesuaian (contoh: "Stok Opname Juni 2026") |
| Referensi | Kode referensi (opsional, untuk pelacakan) |
| Status | `draft` atau `done` |
| Pengguna | User yang membuat penyesuaian |
| Catatan | Keterangan tambahan |

**Membuat penyesuaian stok baru:**

1. Buka menu **Penyesuaian Stok** dari sidebar.
2. Klik **New Inventory Adjustment**.
3. Isi **Nama** penyesuaian.
4. (Opsional) Isi **Referensi** dan **Catatan**.
5. Status otomatis **Draft**.
6. Klik **Create**.

**Menambah detail penyesuaian:**

Setelah penyesuaian dibuat (status draft):

1. Buka detail penyesuaian stok.
2. Pada bagian **Detail Penyesuaian**, klik **New Detail**.
3. Pilih **Produk** dari dropdown.
4. Pilih **Tipe**:
   - **In** — menambah stok (barang masuk)
   - **Out** — mengurangi stok (barang keluar)
5. Isi **Jumlah** (quantity) — angka positif.
6. Isi **Catatan** jika perlu.
7. Klik **Add**.
8. Ulangi untuk produk lainnya.

**Memvalidasi / menyelesaikan penyesuaian:**

1. Pastikan semua detail sudah diisi dengan benar.
2. Klik tombol **Validasi** / **Mark as Done**.
3. Sistem akan:
   - Membuat **Stock Move** untuk setiap detail (status `done`)
   - Mengubah status penyesuaian menjadi `done`
   - Memperbarui stok produk secara otomatis

> **Peringatan:** Penyesuaian yang sudah berstatus `done` **tidak dapat diedit lagi**. Pastikan data sudah benar sebelum memvalidasi.

---

### 9.2 Pergerakan Stok (Stock Moves)

Stock Move adalah catatan otomatis setiap perubahan stok produk. **Setiap pertambahan dan pengurangan stok** akan tercatat di sini — baik dari penjualan (POS), penyesuaian stok, maupun mekanisme lainnya.

> Menu Stock Move **tidak muncul di sidebar navigasi** secara default, namun dapat diakses melalui halaman detail produk (dengan filter per produk).

**Kolom Stock Move:**

| Kolom | Keterangan |
|---|---|
| Produk | Nama produk |
| Tipe | `in` (masuk) atau `out` (keluar) |
| Jumlah | Jumlah unit yang bergerak |
| Status | `draft`, `done`, atau `cancelled` |
| Pengguna | User yang memicu pergerakan (jika ada) |
| Referensi | Keterangan sumber (misal: nomor pesanan, nama penyesuaian) |
| Tanggal | Waktu pergerakan stok |
| Catatan | Keterangan tambahan |

**Sumber Stock Move:**

| Sumber | Tipe | Status | Keterangan |
|---|---|---|---|
| POS — Pesanan baru (Xendit) | `out` | `draft` | Stok direservasi saat pesanan dibuat |
| POS — Pembayaran sukses | `out` | `done` | Stok dikurangi saat pembayaran terkonfirmasi |
| POS — Pembayaran tunai | `out` | `done` | Stok langsung dikurangi |
| POS — Pesanan dibatalkan | — | `cancelled` | Reservasi stok dikembalikan |
| Penyesuaian Stok (done) | `in`/`out` | `done` | Sesuai tipe detail penyesuaian |

**Cara melihat riwayat stok per produk:**

1. Buka menu **Produk** dari sidebar.
2. Klik nama produk yang ingin dilihat riwayat stoknya.
3. Halaman detail produk akan menampilkan **daftar Stock Move** yang terkait dengan produk tersebut.

---

## BAB 10 — Manajemen Pengguna (Users)

Menu **Users** di sidebar (grup User Management) digunakan untuk mengelola akun pengguna yang dapat mengakses sistem.

**Kolom data pengguna:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama lengkap pengguna |
| Email | Alamat email untuk login (harus unik) |
| Password | Kata sandi (minimal 8 karakter) |
| Avatar | Foto profil (opsional) |
| Role | Role yang diberikan ke pengguna |

### 10.1 Menambah Pengguna Baru

1. Buka menu **Users** dari sidebar.
2. Klik **New User**.
3. Isi **Nama** lengkap pengguna.
4. Isi **Email** — akan digunakan sebagai username login.
5. Isi **Password** — minimal 8 karakter.
6. (Opsional) Unggah **Avatar**.
7. Pilih **Role** untuk pengguna dari daftar role yang tersedia.
8. Klik **Create**.

### 10.2 Mengubah Data Pengguna

1. Pada daftar users, klik ikon edit pada baris pengguna.
2. Ubah data yang diperlukan.
3. Jika ingin mengganti password, isi kolom **Password** dengan password baru (kosongkan jika tidak ingin mengubah).
4. Ubah **Role** jika diperlukan.
5. Klik **Save Changes**.

### 10.3 Menonaktifkan Pengguna

Saat ini sistem tidak memiliki kolom `is_active` pada users. Untuk membatasi akses pengguna, lakukan salah satu:
- **Hapus role** dari pengguna tersebut (pengguna tetap bisa login tetapi tidak bisa mengakses modul apa pun)
- **Hapus pengguna** melalui tombol delete di daftar users

### 10.4 Akun Default

Saat instalasi pertama, sistem membuat akun administrator default:

| Field | Nilai |
|---|---|
| Email | `admin@swiftpos.com` |
| Password | `12345678` |
| Role | `super_admin` |

> **Penting:** Segera ganti password default setelah instalasi untuk keamanan.

---

## BAB 11 — Metode Pembayaran (Payment Methods)

Menu **Metode Pembayaran** digunakan untuk mengelola metode pembayaran yang tersedia di POS Terminal, baik pembayaran tunai, kartu, maupun non-tunai via Xendit.

**Kolom data metode pembayaran:**

| Kolom | Keterangan |
|---|---|
| Nama | Nama metode (contoh: Cash, QRIS, BCA Virtual Account) |
| Kode | Kode unik/slug (contoh: `cash`, `qris`, `bca_va`) |
| Deskripsi | Keterangan metode pembayaran |
| Ikon | Ikon untuk ditampilkan di POS |
| Gambar QR | Gambar QR statis (untuk QRIS offline) |
| Tipe | Kategori: `cash`, `card`, `qr_code`, `virtual_account`, `ewallet` |
| Online | Apakah metode ini memproses pembayaran via Xendit (online)? |
| Xendit Channel Type | Tipe channel Xendit: `QR_CODE` atau `VIRTUAL_ACCOUNT` |
| Xendit Channel Code | Kode channel Xendit (contoh: `ID_DANA`, `ID_QRIS`, `BCA`, `BNI`) |
| Xendit Channel Properties | Properti tambahan JSON (jika diperlukan channel tertentu) |
| Tipe Biaya | `flat` (nominal tetap) atau `percentage` (persentase dari total) |
| Nilai Biaya | Nilai biaya — nominal untuk flat, persen untuk percentage |
| Status Aktif | Status aktif/nonaktif |
| Urutan | Urutan tampil di dropdown POS |

### 11.1 Daftar Metode Pembayaran Bawaan

| Nama | Kode | Tipe | Online | Biaya |
|---|---|---|---|---|
| Cash | `cash` | cash | Tidak | — |
| Credit / Debit Card (EDC) | `card_edc` | card | Tidak | — |
| QRIS | `qris` | qr_code | Tidak (offline) | 0.70% |

> QRIS bawaan dikonfigurasi sebagai **offline** (QR statis). Untuk menggunakan QRIS dinamis via Xendit, buat metode pembayaran baru dengan **is_online = true** dan Xendit Channel Code yang sesuai (misal `ID_DANA` untuk Sandbox).

### 11.2 Menambah Metode Pembayaran Baru

1. Buka menu **Metode Pembayaran** dari sidebar.
2. Klik **New Payment Method**.
3. Isi **Nama** — misal: "QRIS Xendit", "BCA Virtual Account".
4. Isi **Kode** — unik, tanpa spasi (contoh: `qris_xendit`, `bca_va`).
5. Pilih **Tipe** sesuai jenis pembayaran.
6. Jika metode menggunakan Xendit:
   - Aktifkan **Is Online**.
   - Pilih **Xendit Channel Type**: `QR_CODE` untuk QRIS, `VIRTUAL_ACCOUNT` untuk VA.
   - Isi **Xendit Channel Code** — lihat daftar channel yang didukung Xendit (contoh: `ID_DANA`, `BCA`, `BNI`, `BRI`, `MANDIRI`).
7. Atur **Fee Type** dan **Fee Value** jika metode ini mengenakan biaya tambahan.
8. Atur **Sort Order** untuk menentukan posisi di dropdown pembayaran.
9. Klik **Create**.

### 11.3 Mengubah Metode Pembayaran

1. Pada daftar metode, klik ikon edit.
2. Ubah data yang diperlukan.
3. Klik **Save Changes**.

### 11.4 Menonaktifkan Metode Pembayaran

1. Edit metode pembayaran, matikan toggle **Is Active**.
2. Klik **Save Changes**.

> Metode yang dinonaktifkan tidak akan muncul di dropdown pembayaran POS Terminal.

---

## BAB 12 — Pengaturan Sistem (Settings)

Menu **Settings** (ikon ⚙️) hanya dapat diakses oleh pengguna dengan role **super_admin**. Menu ini terletak di grup navigasi **Sistem**.

---

### 12.1 Pengaturan Umum (General)

Bagian ini mengatur informasi dasar toko yang akan muncul di struk dan antarmuka sistem.

| Pengaturan | Deskripsi | Default |
|---|---|---|
| **Nama Toko** | Nama toko — muncul di header struk dan judul aplikasi | `SwiftPOS` |
| **Alamat Toko** | Alamat toko — muncul di struk | — |
| **Telepon Toko** | Nomor telepon toko — muncul di struk | — |
| **Mata Uang** | Kode mata uang (IDR, USD, SGD, MYR) | `IDR` |
| **Zona Waktu** | Zona waktu toko | `Asia/Jakarta` |
| **Footer Struk** | Teks bagian bawah struk | `Thank you for shopping with us!` |
| **Pelanggan Default** | Pelanggan yang otomatis dipilih jika kasir tidak memilih pelanggan | — |

**Cara mengubah pengaturan umum:**

1. Buka menu **Settings** (ikon ⚙️ di sidebar bawah).
2. Tab **General** akan tampil pertama kali.
3. Ubah pengaturan yang diperlukan.
4. Klik **Save**.

> Perubahan pengaturan langsung berlaku tanpa perlu restart.

---

### 12.2 Integrasi Xendit

Bagian ini mengatur koneksi dengan Xendit Payment Gateway untuk pembayaran non-tunai (QRIS & Virtual Account).

| Pengaturan | Deskripsi | Default |
|---|---|---|
| **Enable Xendit** | Mengaktifkan/menonaktifkan integrasi Xendit | Nonaktif |
| **Environment** | `Sandbox` (testing) atau `Production` (live) | `sandbox` |
| **Secret Key** | API Secret Key dari Xendit Dashboard | — |
| **Public Key** | API Public Key dari Xendit Dashboard | — |
| **Webhook Token** | Token verifikasi webhook — isi dengan string acak | — |

**Cara mengaktifkan Xendit:**

1. Buka menu **Settings**.
2. Klik tab **Xendit**.
3. Aktifkan toggle **Enable Xendit**.
4. Pilih **Environment**:
   - **Sandbox** — untuk testing, tidak ada uang sungguhan
   - **Production** — untuk transaksi live
5. Masukkan **Secret Key** dari Xendit Dashboard.
6. (Opsional) Masukkan **Public Key**.
7. Isi **Webhook Token** dengan string acak yang aman (contoh: `swiftpos-webhook-secret-2026`).
8. Klik **Test Connection** untuk memverifikasi API key valid.
   - **Sukses**: Menampilkan pesan hijau "API Key valid ✓" (+ saldo akun jika berhasil membaca)
   - **Gagal**: Menampilkan pesan error — periksa kembali Secret Key dan environment
9. Klik **Save** untuk menyimpan konfigurasi.

**Mendaftarkan Webhook di Xendit Dashboard:**

Setelah webhook token disimpan, sistem akan menampilkan **Webhook URL** yang harus didaftarkan di Xendit Dashboard:

```
https://[domain-anda]/api/webhooks/xendit
```

1. Buka [Xendit Dashboard](https://dashboard.xendit.co) → **Settings** → **Webhooks**.
2. Klik **Add Webhook**.
3. Paste URL webhook dari SwiftPos.
4. Pilih event yang ingin diterima (disarankan: `qr.payment`, `fva.payment`).
5. Pastikan **Webhook Token** yang diisi di SwiftPos sama dengan yang diatur di Xendit Dashboard (atau biarkan Xendit mengirim token yang akan diverifikasi oleh SwiftPos).

> **Catatan:** Di environment Sandbox, URL webhook harus dapat diakses dari internet publik. Gunakan tunnel seperti **ngrok** (`ngrok http 8000`) untuk testing lokal.

---

## BAB 13 — Panduan Troubleshooting

### 13.1 Terminal Kasir Tidak Aktif (Error 403)

**Gejala:** Saat mengakses POS Terminal muncul halaman forbidden/403.

**Penyebab:** Terminal kasir dinonaktifkan (is_active = false).

**Solusi:**
1. Buka menu **Terminal Kasir** di panel admin.
2. Edit terminal yang dimaksud.
3. Aktifkan toggle **Is Active**.
4. Simpan.

---

### 13.2 Terminal Sedang Digunakan Kasir Lain

**Gejala:** Saat membuka sesi muncul pesan "Terminal sedang digunakan oleh [Nama]".

**Penyebab:** Sesi sebelumnya belum ditutup — status sesi masih `open`.

**Solusi:**
1. Minta kasir sebelumnya untuk login kembali dan menutup sesi POS.
2. Atau — administrator dapat memeriksa menu **Riwayat Sesi**, mencari sesi dengan status `open`, dan meminta kasir terkait menutupnya.

---

### 13.3 Pembayaran Xendit Gagal / Expired

**Gejala:** QR Code atau Virtual Account tidak bisa dibayar, pesanan berstatus `failed`.

**Kemungkinan penyebab & solusi:**

| Penyebab | Solusi |
|---|---|
| API key salah atau environment tidak sesuai | Periksa Settings → Xendit. Pastikan Secret Key sesuai environment (Sandbox/Production). |
| Pelanggan belum membayar dalam batas waktu | QR Code kedaluwarsa 30 menit, VA 24 jam. Buat transaksi baru. |
| Channel code tidak valid | Periksa Xendit Channel Code pada metode pembayaran. Untuk Sandbox QR, gunakan `ID_DANA`. |
| Koneksi internet server terputus | Periksa koneksi internet server. |

---

### 13.4 Webhook Tidak Menerima Notifikasi

**Gejala:** Pembayaran Xendit sukses tetapi status pesanan di SwiftPos tidak berubah (tetap `unpaid`/`new`).

**Kemungkinan penyebab & solusi:**

| Penyebab | Solusi |
|---|---|
| Webhook URL belum didaftarkan di Xendit | Daftarkan URL `https://[domain]/api/webhooks/xendit` di Xendit Dashboard. |
| Webhook token tidak cocok | Pastikan token di Settings SwiftPos sama dengan token di Xendit webhook config. |
| URL tidak dapat diakses publik | Gunakan ngrok atau tunnel jika testing di localhost. |
| URL salah atau typo | Periksa kembali URL — pastikan path `/api/webhooks/xendit` benar. |

> **Fallback:** Sistem melakukan **polling otomatis** di antarmuka POS, jadi meskipun webhook gagal, status tetap akan terupdate saat polling berjalan. Webhook hanya diperlukan untuk transaksi yang ditinggalkan (user menutup halaman POS sebelum pembayaran selesai).

---

### 13.5 Token Belum Tersimpan / Tidak Sinkron

**Gejala:** Pesan error terkait token atau data tidak tersimpan saat transaksi.

**Penyebab:** Masalah pada session atau cache sistem.

**Solusi:**
1. Logout dan login kembali.
2. Jika menggunakan aplikasi mobile (PWA), pastikan koneksi internet stabil saat sinkronisasi.
3. Administrator dapat membersihkan cache Laravel:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

---

### 13.6 Stok Produk Tidak Sesuai

**Gejala:** Produk memiliki stok minus atau stok tidak sesuai dengan fisik.

**Penyebab:** Selisih stok karena penjualan atau penyesuaian yang tidak tercatat dengan benar.

**Solusi:**
1. Periksa riwayat Stock Move produk dari halaman detail produk.
2. Lakukan **Penyesuaian Stok** untuk mengoreksi jumlah stok ke nilai yang benar.

---

## BAB 14 — Lampiran

### 14.1 Daftar Singkatan & Istilah Teknis

| Singkatan/Istilah | Kepanjangan |
|---|---|
| POS | Point of Sale |
| SKU | Stock Keeping Unit |
| QRIS | Quick Response Code Indonesian Standard |
| VA | Virtual Account |
| EDC | Electronic Data Capture (mesin gesek kartu) |
| API | Application Programming Interface |
| PWA | Progressive Web Application |
| CRUD | Create, Read, Update, Delete |
| FK | Foreign Key (relasi database) |
| JSON | JavaScript Object Notation |
| UUID | Universally Unique Identifier |
| UI | User Interface |
| IDR | Indonesian Rupiah |
| HID | Human Interface Device (mode keyboard barcode scanner) |

---

### 14.2 Alur Kerja Sistem (Flowchart)

```
┌──────────────┐
│   LOGIN      │  Email & Password
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  DASHBOARD   │  Statistik penjualan, grafik, top produk
└──────┬───────┘
       │
       ├──► MANAJEMEN PRODUK (Merek → Kategori → Sub Kategori → Produk)
       ├──► MANAJEMEN PELANGGAN (Tambah/Ubah data pelanggan)
       ├──► MANAJEMEN PENGGUNA (Tambah/Ubah user & role)
       ├──► METODE PEMBAYARAN (Konfigurasi metode bayar)
       ├──► PENGATURAN SISTEM (Nama toko, Xendit, dll.)
       │
       ▼
┌──────────────────────────────────────────────────┐
│              ALUR POS TERMINAL                    │
│                                                   │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    │
│  │ BUKA     │───►│ OPERASI- │───►│ TUTUP    │    │
│  │ SESI     │    │ ONAL     │    │ SESI     │    │
│  │          │    │          │    │          │    │
│  │ • Saldo  │    │ • Cari   │    │ • Saldo   │    │
│  │   Awal   │    │   Produk │    │   Aktual  │    │
│  │          │    │ • Keran- │    │ • Selisih │    │
│  │          │    │   jang   │    │ • Catatan │    │
│  │          │    │ • Bayar  │    │          │    │
│  └──────────┘    └────┬─────┘    └──────────┘    │
│                       │                           │
│              ┌────────┴────────┐                  │
│              ▼                 ▼                  │
│        ┌──────────┐    ┌──────────────┐           │
│        │ TUNAI    │    │ NON-TUNAI    │           │
│        │          │    │ (Xendit)     │           │
│        │ • Cash   │    │ • QR Code    │           │
│        │ • EDC    │    │ • Virtual    │           │
│        │          │    │   Account    │           │
│        └────┬─────┘    └──────┬───────┘           │
│             │                 │                   │
│             └────────┬────────┘                   │
│                      ▼                            │
│               ┌──────────┐                        │
│               │ STRUK    │                        │
│               └──────────┘                        │
└──────────────────────────────────────────────────┘
```

---

### 14.3 Struktur Data (Ringkasan ERD)

Berikut adalah hubungan antar entitas utama dalam sistem:

```
┌─────────┐     ┌─────────────┐     ┌───────────────┐
│  Brand  │────<│   Product   │>────│   Category    │
└─────────┘     └──────┬──────┘     └───────┬───────┘
                       │>───────────────────│
                 ┌─────┴─────┐       ┌──────┴──────┐
                 │  StockMove│       │ SubCategory │
                 └─────┬─────┘       └─────────────┘
                       │
┌──────────┐    ┌──────┴──────┐    ┌──────────────┐
│ Customer │────<│   Order    │>────│ PaymentMethod│
└──────────┘    └──────┬──────┘    └──────────────┘
                       │
                 ┌─────┴──────┐
                 │OrderDetail │
                 └─────┬──────┘
                       │
                 ┌─────┴──────┐
                 │ Inventory  │
                 │ Adjustment │
                 └────────────┘

┌──────────┐    ┌──────────────┐
│ Cashier  │────<│ PosSession  │
└──────────┘    └──────┬───────┘
                       │
                       │ (belongsTo)
                       ▼
                  ┌──────────┐
                  │  Order   │
                  └──────────┘
```

**Relasi utama:**
- **Product** memiliki Brand, Category, SubCategory
- **Order** memiliki Customer, Cashier, PosSession, PaymentMethod, dan banyak OrderDetail
- **OrderDetail** menghubungkan Order dengan Product
- **StockMove** terkait dengan Product, User, dan OrderDetail (jika dari penjualan)
- **InventoryAdjustment** memiliki banyak InventoryAdjustmentDetail (per produk)
- **PosSession** dimiliki oleh Cashier (terminal) dan User (kasir)

---

### 14.4 API Endpoints (Untuk Integrasi Eksternal)

SwiftPos menyediakan REST API untuk integrasi dengan aplikasi mobile (PWA) atau sistem eksternal. Semua endpoint (kecuali login & webhook) memerlukan autentikasi **Sanctum token** via header `Authorization: Bearer {token}`.

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| `POST` | `/api/login` | Publik | Login — mengembalikan Sanctum token |
| `GET` | `/api/init-data` | Sanctum | Data master: produk, merek, kategori, sub kategori, metode pembayaran, terminal |
| `POST` | `/api/session/open` | Sanctum | Membuka sesi POS baru |
| `POST` | `/api/session/close` | Sanctum | Menutup sesi POS dengan saldo aktual |
| `POST` | `/api/orders/sync` | Sanctum | Sinkronisasi transaksi offline (bulk) — mendukung deduplikasi via `pos_reference` |
| `POST` | `/api/webhooks/xendit` | Publik | Webhook Xendit — diverifikasi dengan token |

---

### 14.5 Package & Dependensi

| Package | Versi | Fungsi |
|---|---|---|
| Laravel Framework | ^12.0 | Framework PHP backend |
| Filament | ^5.5 | Panel admin & komponen UI |
| Filament Shield | ^4.2 | Manajemen role & permission |
| Filament Edit Profile | ^3.0 | Edit profil, avatar, sesi browser |
| Filament UI Switcher | ^1.0 | Toggle mode terang/gelap |
| Laravel Sanctum | ^4.3 | Autentikasi API token |
| Spatie Laravel-Permission | * | Role & permission engine |

---

### 14.6 Informasi Kontak Support

Untuk bantuan teknis, pertanyaan, atau pelaporan bug, silakan hubungi tim pengembang melalui repository proyek.

> **Catatan:** Sebelum menghubungi support, pastikan Anda telah:
> 1. Membaca bab troubleshooting (Bab 13) yang relevan dengan masalah Anda
> 2. Mencatat langkah-langkah yang sudah dilakukan
> 3. Menyiapkan screenshot atau deskripsi error yang muncul

---

### 14.7 Fitur Tambahan

Fitur-fitur berikut tersedia di SwiftPos tetapi tidak tercakup dalam bab utama:

| Fitur | Deskripsi | Lokasi |
|---|---|---|
| **Mode Gelap/Terang** | Toggle Dark/Light mode di pojok kanan atas | Semua halaman |
| **API Token Management** | Generate & manage Sanctum token untuk integrasi | Edit Profil → API Tokens |
| **Browser Sessions** | Lihat dan akhiri sesi login di perangkat lain | Edit Profil → Browser Sessions |
| **Ekspor Data** | Ekspor data tabel ke CSV/Excel (via Filament) | Tombol ekspor di setiap halaman daftar |
| **Soft Delete & Restore** | Hapus sementara dan kembalikan data | Tersedia untuk resource tertentu |

