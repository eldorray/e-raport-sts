# E-Raport STS (Sistem Informasi Raport Sekolah)

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)

---

## 📖 Deskripsi

**E-Raport STS** adalah aplikasi sistem informasi manajemen raport digital berbasis web untuk sekolah. Aplikasi ini memudahkan pengelolaan data siswa, guru, mata pelajaran, penilaian, dan pencetakan raport secara digital.

### ✨ Fitur Utama

- 👥 **Manajemen Pengguna** - Admin, Guru, dan Siswa dengan role-based access
- 🎓 **Manajemen Data Siswa** - CRUD data siswa dengan fitur import Excel
- 👨‍🏫 **Manajemen Data Guru** - Kelola data guru dan penugasan mengajar
- 📚 **Manajemen Mata Pelajaran** - Kelola daftar mata pelajaran
- 🏫 **Manajemen Kelas** - Kelola kelas dan rombongan belajar
- 📊 **Penilaian** - Input nilai oleh guru per mata pelajaran
- 🎭 **Penilaian Ekstrakurikuler** - Input nilai kegiatan ekstrakurikuler
- 📝 **Cetak Raport** - Generate dan cetak raport dalam format PDF
- 📅 **Tahun Ajaran** - Kelola periode tahun ajaran dan semester
- ⚙️ **Pengaturan Sekolah** - Konfigurasi profil dan identitas sekolah
- 💾 **Backup Database** - Fitur backup dan restore database

---

## 🚀 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB
- Git

---

## 📥 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/eldorray/e-raport-sts.git
cd e-raport-sts
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_raport
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi dan Seeding Database

```bash
# Jalankan migrasi database
php artisan migrate

# (Opsional) Jalankan seeder untuk data dummy
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

---

## 👤 Akun Default

Tidak ada akun bawaan. Isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` (minimal 12 karakter) di `.env`, lalu buat akun admin pertama:

```bash
php artisan db:seed --class=AdminSeeder
```

Seeder tidak mengubah akun yang sudah ada. Setelah akun dibuat, kosongkan lagi `ADMIN_PASSWORD` di `.env`.

---

## 📂 Struktur Modul

| Modul           | Deskripsi                                 |
| --------------- | ----------------------------------------- |
| Dashboard       | Ringkasan statistik dan informasi sekolah |
| Pengguna        | Manajemen user admin                      |
| Guru            | Data guru dan penugasan mengajar          |
| Siswa           | Data siswa dengan import Excel            |
| Kelas           | Manajemen kelas dan rombel                |
| Mata Pelajaran  | Daftar mata pelajaran                     |
| Penilaian       | Input nilai oleh guru                     |
| Ekstrakurikuler | Penilaian kegiatan ekskul                 |
| Raport          | Generate dan cetak raport                 |
| Tahun Ajaran    | Pengaturan periode akademik               |
| Profil Sekolah  | Identitas dan pengaturan sekolah          |
| Backup          | Backup dan restore database               |

---

## 🔧 Perintah Artisan Berguna

```bash
# Membersihkan cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Menjalankan dalam mode development
npm run dev

# Membuat storage link
php artisan storage:link
```

---

## 📄 Lisensi

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan buat Pull Request atau laporkan Issue jika menemukan bug.

---

## 📧 Kontak

Untuk pertanyaan atau bantuan, silakan hubungi melalui:

- GitHub Issues: [Issues](https://github.com/eldorray/e-raport-sts/issues)

---

<p align="center">
  <b>E-Raport STS</b> - Dibuat dengan ❤️ menggunakan Laravel
</p>
