-- ============================================================
--  Database Aplikasi Pengelolaan Laundry
--  File   : database.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. tb_outlet
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_outlet (
    id_outlet       INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama_outlet     VARCHAR(100)    NOT NULL,
    alamat          TEXT            NOT NULL,
    telepon         VARCHAR(20)     NOT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. tb_user
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_user (
    id_user         INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    id_outlet       INT UNSIGNED    NOT NULL,
    nama            VARCHAR(100)    NOT NULL,
    username        VARCHAR(50)     NOT NULL UNIQUE,
    password        VARCHAR(255)    NOT NULL,
    role            ENUM('admin','kasir','owner') NOT NULL DEFAULT 'kasir',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_outlet
        FOREIGN KEY (id_outlet) REFERENCES tb_outlet(id_outlet)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ------------------------------------------------------------
-- 3. tb_member
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_member (
    id_member       INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(100)    NOT NULL,
    telepon         VARCHAR(20)     NOT NULL,
    alamat          TEXT,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 4. tb_paket
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_paket (
    id_paket        INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama_paket      VARCHAR(100)    NOT NULL,
    jenis           ENUM('kiloan','satuan') NOT NULL DEFAULT 'kiloan',
    harga           DECIMAL(10,2)   NOT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 5. tb_transaksi
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id_transaksi    INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    id_outlet       INT UNSIGNED    NOT NULL,
    id_user         INT UNSIGNED    NOT NULL,
    id_member       INT UNSIGNED,
    kode_transaksi  VARCHAR(30)     NOT NULL UNIQUE,
    tanggal_masuk   DATE            NOT NULL,
    tanggal_selesai DATE,
    tanggal_ambil   DATE,
    status          ENUM('baru','proses','selesai','diambil') NOT NULL DEFAULT 'baru',
    total_harga     DECIMAL(12,2)   NOT NULL DEFAULT 0,
    keterangan      TEXT,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trx_outlet
        FOREIGN KEY (id_outlet) REFERENCES tb_outlet(id_outlet)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_trx_user
        FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_trx_member
        FOREIGN KEY (id_member) REFERENCES tb_member(id_member)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 6. tb_detail_transaksi
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_detail_transaksi (
    id_detail       INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    id_transaksi    INT UNSIGNED    NOT NULL,
    id_paket        INT UNSIGNED    NOT NULL,
    qty             DECIMAL(8,2)    NOT NULL,
    harga_satuan    DECIMAL(10,2)   NOT NULL,
    subtotal        DECIMAL(12,2)   NOT NULL,
    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi) REFERENCES tb_transaksi(id_transaksi)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_paket
        FOREIGN KEY (id_paket) REFERENCES tb_paket(id_paket)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ------------------------------------------------------------
-- Data awal: outlet
-- ------------------------------------------------------------
INSERT INTO tb_outlet (nama_outlet, alamat, telepon) VALUES
('Laundry Bersih', 'Jl. Merdeka No. 1', '081200000001');

-- ------------------------------------------------------------
-- Data awal: user (password di-hash dengan password_hash bcrypt)
-- admin123  → hash bcrypt di bawah
-- kasir123  → hash bcrypt di bawah
--
-- Hash di bawah di-generate dengan:
--   password_hash('admin123', PASSWORD_BCRYPT)
--   password_hash('kasir123', PASSWORD_BCRYPT)
--   password_hash('owner123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO tb_user (id_outlet, nama, username, password, role) VALUES
(1, 'Administrator', 'admin',
 '$2y$10$l276rkGDW10SxeDktU9iVOAvbMsPUNEjbnNY2yXa/TP0n3LzdYbQS',
 'admin'),
(1, 'Budi Kasir', 'kasir',
 '$2y$10$6DKEBRbw9wotQQMErW8CFO7Gi3RNuG7YN/UxT2EYAAmqMZAPNZo0y',
 'kasir'),
(1, 'Siti Owner', 'owner',
 '$2y$10$WCwoPzy7wlFTlj1LZAeXzeRKdN8FbO0m3qIcCBkvCTUSvMcXlY3My',
 'owner');

-- ------------------------------------------------------------
-- Data awal: paket
-- ------------------------------------------------------------
INSERT INTO tb_paket (nama_paket, jenis, harga) VALUES
('Cuci + Setrika', 'kiloan', 7000),
('Cuci Saja',      'kiloan', 5000),
('Setrika Saja',   'kiloan', 4000),
('Cuci Sepatu',    'satuan', 25000),
('Express',        'kiloan', 12000);
