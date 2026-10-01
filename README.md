# Aplikasi Laundry

## Hosting di InfinityFree

Persyaratan: PHP 8.0 atau lebih baru dengan ekstensi PDO MySQL aktif.

1. Buat akun hosting dan domain/subdomain di InfinityFree.
2. Dari **Control Panel > MySQL Databases**, buat database. Catat nama database, username, password, dan hostname MySQL yang ditampilkan panel.
3. Buka **Online File Manager** atau FTP, lalu unggah isi aplikasi ke folder `htdocs`. Jangan unggah folder `laravel-12`; aplikasi utama menggunakan PHP mandiri di folder root.
4. Di **phpMyAdmin**, pilih database yang dibuat lalu impor file `database.sql` dari komputer. Jangan membuat database lain saat import.
5. Salin `config/database.local.example.php` menjadi `config/database.local.php`, lalu isi nilainya sesuai panel InfinityFree. File lokal tersebut tidak ikut diunggah ke GitHub. Jika hosting menyediakan environment variables, `DB_HOST`, `DB_NAME`, `DB_USER`, dan `DB_PASS` akan dipakai otomatis.
6. Buka domain aplikasi. URL aplikasi otomatis menyesuaikan bila dipasang di domain utama ataupun subfolder.

InfinityFree biasanya memberi hostname MySQL tersendiri; jangan gunakan `localhost` kecuali panel memang menunjukkan nilai tersebut. Pastikan PHP memiliki ekstensi PDO MySQL aktif. File `.htaccess` mencegah akses web ke konfigurasi, dump database, log, dan project Laravel yang bukan aplikasi aktif.

### Akun awal

- Admin: `admin` / `admin123`
- Kasir: `kasir` / `kasir123`
- Owner: `owner` / `owner123`

Ganti password awal sebelum aplikasi digunakan dengan data sungguhan.

## Fitur aplikasi

- **Admin:** menambah dan mengubah data member serta paket pada menu **Member & paket**.
- **Kasir:** membuat transaksi dengan paket, jumlah, member opsional, dan catatan. Total dihitung dari harga paket di server. Detail pesanan disimpan bersama transaksi; status bergerak dari Baru, Diproses, Selesai, hingga Diambil. Struk dapat dicetak atau disimpan sebagai PDF dari browser.
- **Owner:** melihat omzet bulanan, jumlah pesanan per status, dan transaksi terbaru. Owner hanya memiliki akses baca.

Data tersimpan di MySQL: member di `tb_member`, paket di `tb_paket`, pesanan di `tb_transaksi`, dan rincian paket pesanan di `tb_detail_transaksi`.