-- Database: kampus_db
-- Created: 2024
-- Description: Database schema for MAGNOLIA UNIVERSITY Campus Management System

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database creation
CREATE DATABASE IF NOT EXISTS `kampus_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kampus_db`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `mahasiswa`
CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `program_studi` varchar(100) NOT NULL,
  `tahun_masuk` year(4) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT 0.00,
  `total_sks` int(11) DEFAULT 0,
  `status_akademik` enum('aktif','cuti','lulus','dropout') DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_program_studi` (`program_studi`),
  KEY `idx_tahun_masuk` (`tahun_masuk`),
  CONSTRAINT `fk_mahasiswa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `dosen`
CREATE TABLE `dosen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nidn` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `bidang_keahlian` varchar(100) DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `jabatan_akademik` varchar(50) DEFAULT NULL,
  `status_kepegawaian` enum('tetap','kontrak','honorer') DEFAULT 'tetap',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nidn` (`nidn`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_bidang_keahlian` (`bidang_keahlian`),
  CONSTRAINT `fk_dosen_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `mata_kuliah`
CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mata_kuliah` varchar(20) NOT NULL,
  `nama_mata_kuliah` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `prasyarat` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_mata_kuliah` (`kode_mata_kuliah`),
  KEY `idx_program_studi` (`program_studi`),
  KEY `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `jadwal`
CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mata_kuliah_id` int(11) NOT NULL,
  `dosen_id` int(11) NOT NULL,
  `hari` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) NOT NULL,
  `kapasitas` int(11) DEFAULT 40,
  `tahun_akademik` varchar(10) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mata_kuliah` (`mata_kuliah_id`),
  KEY `idx_dosen` (`dosen_id`),
  KEY `idx_hari` (`hari`),
  KEY `idx_tahun_akademik` (`tahun_akademik`),
  CONSTRAINT `fk_jadwal_mata_kuliah` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jadwal_dosen` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `krs`
CREATE TABLE `krs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL,
  `tahun_akademik` varchar(10) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('diambil','batal','lulus') DEFAULT 'diambil',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_krs` (`mahasiswa_id`,`jadwal_id`,`tahun_akademik`,`semester`),
  KEY `idx_mahasiswa` (`mahasiswa_id`),
  KEY `idx_jadwal` (`jadwal_id`),
  KEY `idx_tahun_akademik` (`tahun_akademik`),
  CONSTRAINT `fk_krs_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_krs_jadwal` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `nilai`
CREATE TABLE `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `krs_id` int(11) NOT NULL,
  `tugas` decimal(5,2) DEFAULT NULL,
  `uts` decimal(5,2) DEFAULT NULL,
  `uas` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `nilai_huruf` varchar(2) DEFAULT NULL,
  `nilai_angka` decimal(3,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_id` (`krs_id`),
  CONSTRAINT `fk_nilai_krs` FOREIGN KEY (`krs_id`) REFERENCES `krs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `absensi`
CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `krs_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `pertemuan_ke` int(11) NOT NULL,
  `status` enum('hadir','tidak_hadir','izin','sakit') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_absensi` (`krs_id`,`tanggal`,`pertemuan_ke`),
  KEY `idx_tanggal` (`tanggal`),
  CONSTRAINT `fk_absensi_krs` FOREIGN KEY (`krs_id`) REFERENCES `krs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `info_kampus`
CREATE TABLE `info_kampus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `konten` longtext NOT NULL,
  `kategori` enum('pengumuman','berita','event','akademik') DEFAULT 'pengumuman',
  `status` enum('aktif','draft') DEFAULT 'draft',
  `gambar` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_info_kampus_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `sejarah`
CREATE TABLE `sejarah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `konten` longtext NOT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `prestasi`
CREATE TABLE `prestasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `tahun` year(4) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `tingkat` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tahun` (`tahun`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `log_aktivitas`
CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_aktivitas` (`aktivitas`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_log_aktivitas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `notifikasi` -- Nama tabel di SQL adalah 'notifikasi'
CREATE TABLE `notifikasi` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `tahun_akademik`
CREATE TABLE `tahun_akademik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` varchar(10) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'nonaktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tahun_semester` (`tahun`,`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Triggers for automatic GPA calculation
DELIMITER $$
CREATE TRIGGER `update_mahasiswa_gpa` AFTER INSERT ON `nilai` FOR EACH ROW
BEGIN
    DECLARE mahasiswa_id_var INT;
    DECLARE new_gpa DECIMAL(3,2);
    DECLARE new_total_sks INT;
        
    SELECT k.mahasiswa_id INTO mahasiswa_id_var 
    FROM krs k WHERE k.id = NEW.krs_id;
        
    SELECT 
        COALESCE(SUM(mk.sks * n.nilai_angka) / SUM(mk.sks), 0),
        COALESCE(SUM(mk.sks), 0)
    INTO new_gpa, new_total_sks
    FROM nilai n
    JOIN krs k ON n.krs_id = k.id
    JOIN jadwal j ON k.jadwal_id = j.id
    JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
    WHERE k.mahasiswa_id = mahasiswa_id_var AND n.nilai_angka > 0;
        
    UPDATE mahasiswa 
    SET ipk = new_gpa, total_sks = new_total_sks 
    WHERE id = mahasiswa_id_var;
END$$

CREATE TRIGGER `update_mahasiswa_gpa_on_update` AFTER UPDATE ON `nilai` FOR EACH ROW
BEGIN
    DECLARE mahasiswa_id_var INT;
    DECLARE new_gpa DECIMAL(3,2);
    DECLARE new_total_sks INT;
        
    SELECT k.mahasiswa_id INTO mahasiswa_id_var 
    FROM krs k WHERE k.id = NEW.krs_id;
        
    SELECT 
        COALESCE(SUM(mk.sks * n.nilai_angka) / SUM(mk.sks), 0),
        COALESCE(SUM(mk.sks), 0)
    INTO new_gpa, new_total_sks
    FROM nilai n
    JOIN krs k ON n.krs_id = k.id
    JOIN jadwal j ON k.jadwal_id = j.id
    JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
    WHERE k.mahasiswa_id = mahasiswa_id_var AND n.nilai_angka > 0;
        
    UPDATE mahasiswa 
    SET ipk = new_gpa, total_sks = new_total_sks 
    WHERE id = mahasiswa_id_var;
END$$
DELIMITER ;

-- Insert default admin user
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES 
('admin', 'admin@magnolia.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES 
('site_name', 'MAGNOLIA UNIVERSITY', 'Nama universitas'),
('site_description', 'Universitas Terdepan dalam Inovasi dan Keunggulan Akademik', 'Deskripsi universitas'),
('academic_year', '2024/2025', 'Tahun akademik aktif'),
('current_semester', 'Ganjil', 'Semester aktif'),
('registration_open', '1', 'Status pendaftaran (1=buka, 0=tutup)'),
('max_sks_per_semester', '24', 'Maksimal SKS per semester'),
('min_gpa_for_max_sks', '3.00', 'IPK minimal untuk mengambil SKS maksimal');

-- Insert sample sejarah
INSERT INTO `sejarah` (`konten`, `visi`, `misi`) VALUES 
('<p><strong>MAGNOLIA UNIVERSITY</strong> didirikan pada tahun 1985 dengan visi menjadi universitas terdepan yang menghasilkan lulusan berkualitas tinggi, berkarakter, dan mampu bersaing di tingkat nasional dan internasional.</p>

<p>Berawal dari sebuah institut teknologi kecil di Jakarta Selatan, MAGNOLIA UNIVERSITY kini telah berkembang menjadi universitas komprehensif yang memiliki berbagai fakultas unggulan termasuk Fakultas Teknologi Informasi, Fakultas Bisnis dan Manajemen, Fakultas Teknik, dan Fakultas Sains.</p>

<p>Dengan dukungan lebih dari 500 dosen berkualitas tinggi, fasilitas pembelajaran modern, dan kemitraan strategis dengan industri serta institusi pendidikan internasional, MAGNOLIA UNIVERSITY terus berkomitmen untuk menghasilkan lulusan yang tidak hanya unggul secara akademik tetapi juga memiliki karakter kepemimpinan dan jiwa entrepreneurship.</p>

<p>Kampus utama yang berlokasi di Jl. Magnolia Raya No. 88, Jakarta Selatan, dilengkapi dengan laboratorium canggih, perpustakaan digital, pusat riset, dan fasilitas olahraga yang mendukung pengembangan mahasiswa secara holistik.</p>', 

'Menjadi universitas terdepan yang menghasilkan lulusan berkualitas tinggi, berkarakter, dan mampu bersaing di tingkat nasional dan internasional dalam era digital dan globalisasi.', 

'1. Menyelenggarakan pendidikan tinggi yang berkualitas dan relevan dengan kebutuhan industri 4.0
2. Mengembangkan penelitian dan inovasi yang memberikan dampak positif bagi masyarakat
3. Melaksanakan pengabdian kepada masyarakat yang berkelanjutan dan bermakna
4. Membangun kemitraan strategis dengan industri dan institusi pendidikan internasional
5. Menciptakan lingkungan akademik yang kondusif untuk pengembangan karakter dan kepemimpinan
6. Menghasilkan lulusan yang memiliki kompetensi tinggi, jiwa entrepreneurship, dan integritas moral');

-- Insert sample prestasi
INSERT INTO `prestasi` (`judul`, `deskripsi`, `tahun`, `kategori`, `tingkat`) VALUES
('Juara 1 Kompetisi Inovasi Teknologi Nasional', 'Tim mahasiswa Fakultas Teknologi Informasi berhasil meraih juara 1 dalam kompetisi inovasi teknologi dengan aplikasi AI untuk smart city yang diselenggarakan oleh Kementerian Pendidikan.', 2024, 'Teknologi', 'Nasional'),

('Best Paper Award International Conference on Business Innovation', 'Penelitian dosen Fakultas Bisnis tentang sustainable business model mendapat pengakuan internasional dan dipublikasikan di jurnal bereputasi tinggi.', 2024, 'Penelitian', 'Internasional'),

('Juara 2 Lomba Karya Tulis Ilmiah Mahasiswa', 'Mahasiswa Program Studi Teknik Lingkungan meraih juara 2 dalam LKTM tingkat nasional dengan penelitian tentang pengolahan limbah plastik menjadi bahan bakar alternatif.', 2023, 'Akademik', 'Nasional'),

('Gold Medal International Robotics Competition', 'Tim robotika MAGNOLIA UNIVERSITY meraih medali emas dalam kompetisi robotika internasional di Singapura dengan robot autonomous untuk disaster response.', 2023, 'Teknologi', 'Internasional'),

('Juara 1 Business Plan Competition', 'Mahasiswa Fakultas Bisnis memenangkan kompetisi business plan tingkat ASEAN dengan startup di bidang fintech yang berfokus pada UMKM.', 2023, 'Akademik', 'Regional'),

('Outstanding Research Award', 'Pusat Penelitian MAGNOLIA UNIVERSITY mendapat penghargaan untuk penelitian breakthrough dalam bidang renewable energy yang berpotensi komersial tinggi.', 2022, 'Penelitian', 'Nasional'),

('Juara 1 Hackathon Nasional', 'Tim mahasiswa multidisiplin berhasil mengembangkan aplikasi mobile untuk edukasi anak berkebutuhan khusus dan memenangkan hackathon nasional.', 2022, 'Teknologi', 'Nasional'),

('Best University Partnership Award', 'MAGNOLIA UNIVERSITY mendapat penghargaan sebagai universitas dengan kemitraan industri terbaik dari Kamar Dagang dan Industri Indonesia.', 2022, 'Akademik', 'Nasional');

-- Insert sample info kampus
INSERT INTO `info_kampus` (`judul`, `konten`, `kategori`, `status`, `created_by`) VALUES
('Pembukaan Pendaftaran Mahasiswa Baru 2025/2026', 'MAGNOLIA UNIVERSITY dengan bangga mengumumkan pembukaan pendaftaran mahasiswa baru untuk tahun akademik 2025/2026. Tersedia berbagai program studi unggulan dengan fasilitas pembelajaran modern dan dosen berkualitas internasional.

Periode pendaftaran:
- Gelombang 1: 1 Januari - 31 Maret 2025
- Gelombang 2: 1 April - 31 Mei 2025
- Gelombang 3: 1 Juni - 31 Juli 2025

Dapatkan beasiswa hingga 100% untuk prestasi akademik dan non-akademik. Informasi lengkap dapat diakses melalui website resmi atau datang langsung ke kampus.', 'pengumuman', 'aktif', 1),

('MAGNOLIA UNIVERSITY Raih Akreditasi A untuk Semua Program Studi', 'Dalam evaluasi terbaru dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT), MAGNOLIA UNIVERSITY berhasil mempertahankan akreditasi A untuk seluruh program studi yang ada.

Pencapaian ini merupakan bukti komitmen universitas dalam memberikan pendidikan berkualitas tinggi dan terus melakukan perbaikan berkelanjutan dalam semua aspek akademik dan non-akademik.

Rektor MAGNOLIA UNIVERSITY menyampaikan terima kasih kepada seluruh civitas akademika yang telah berkontribusi dalam pencapaian prestasi gemilang ini.', 'berita', 'aktif', 1),

('Workshop "Digital Transformation in Education" - 15 Februari 2025', 'MAGNOLIA UNIVERSITY mengundang seluruh dosen, mahasiswa, dan praktisi pendidikan untuk mengikuti workshop bertema "Digital Transformation in Education: Preparing for the Future of Learning".

Acara ini akan menghadirkan pembicara internasional dari MIT dan Stanford University yang akan berbagi pengalaman tentang implementasi teknologi dalam pendidikan tinggi.

Waktu: 15 Februari 2025, 09:00 - 16:00 WIB
Tempat: Auditorium Utama MAGNOLIA UNIVERSITY
Pendaftaran: gratis untuk mahasiswa, Rp 150.000 untuk umum

Daftarkan diri Anda segera karena tempat terbatas!', 'event', 'aktif', 1),

('Pengumuman Jadwal Ujian Tengah Semester Ganjil 2024/2025', 'Kepada seluruh mahasiswa MAGNOLIA UNIVERSITY, berikut ini adalah pengumuman jadwal Ujian Tengah Semester (UTS) untuk semester ganjil tahun akademik 2024/2025:

Periode UTS: 25 November - 7 Desember 2024

Ketentuan UTS:
1. Mahasiswa wajib hadir 15 menit sebelum ujian dimulai
2. Membawa kartu mahasiswa dan kartu ujian
3. Tidak diperkenankan membawa alat komunikasi elektronik
4. Menggunakan alat tulis yang telah ditentukan

Jadwal detail per program studi dapat dilihat di portal akademik masing-masing. Semoga sukses untuk semua mahasiswa!', 'akademik', 'aktif', 1),

('Seminar Nasional "Sustainable Development Goals in Higher Education"', 'Pusat Penelitian dan Pengabdian Masyarakat MAGNOLIA UNIVERSITY menyelenggarakan seminar nasional dengan tema "Sustainable Development Goals in Higher Education: Challenges and Opportunities".

Acara ini akan menghadirkan keynote speaker dari PBB dan para ahli di bidang pembangunan berkelanjutan.

Waktu: 22 Maret 2025, 09:00 - 17:00 WIB
Tempat: Convention Center MAGNOLIA UNIVERSITY
Pendaftaran: Rp 250.000 untuk umum, gratis untuk mahasiswa dan dosen MAGNOLIA UNIVERSITY

Segera daftarkan diri Anda dan berkontribusi dalam mewujudkan masa depan yang lebih baik!', 'event', 'aktif', 1);

COMMIT;
