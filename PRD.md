# Product Requirement Document (PRD)
## Sistem Manajemen Aset Digital (Digital Asset Management - DAM)
**Mata Kuliah:** Cloud Computing  
**Kelompok:** 5  
**Arsitektur:** Laravel Microservices & Docker Containerization  

---

## 1. Pendahuluan & Latar Belakang
Sistem Manajemen Aset Digital (DAM) adalah platform enterprise-grade yang dirancang untuk mengelola, menyimpan, mengorganisasi, dan membagikan aset digital (seperti gambar, dokumen, dan media lainnya) secara efisien dan aman. Sistem ini dikembangkan menggunakan arsitektur modern berbasis microservices untuk memastikan skalabilitas, ketersediaan tinggi, dan isolasi kegagalan komponen aplikasi.

## 2. Arsitektur Sistem & Spesifikasi Teknologi
Sistem ini dipecah menjadi 3 service utama yang berjalan secara independen di dalam container Docker tersendiri:

1. **Gateway / Frontend Service:**
   - **Teknologi:** Laravel Blade + Tailwind CSS.
   - **Tanggung Jawab:** Antarmuka pengguna (UI), API Routing, Load Balancing, dan interaksi client ke backend service.
2. **Auth Service:**
   - **Teknologi:** Laravel + JWT Core Framework (`tymon/jwt-auth`).
   - **Tanggung Jawab:** Manajemen akun, registrasi, login, penerbitan token JWT, dan validasi Role-Based Access Control (RBAC).
3. **Project / Main Service:**
   - **Teknologi:** Laravel API.
   - **Tanggung Jawab:** Menangani semua proses bisnis inti dari manajemen aset digital.

### Desain Database Terpisah
* **User Database (PostgreSQL/MySQL):** Khusus menyimpan data kredensial user, profil, dan role untuk Auth Service.
* **Project Database (PostgreSQL/MySQL):** Khusus menyimpan metadata aset, kategori, tag, riwayat versi, dan token link sharing untuk Project Service.

---

## 3. Spesifikasi Fitur Utama

### 3.1. Service Autentikasi (Auth Service)
* **Registrasi Pengguna:** Pendaftaran akun baru dengan validasi email unik dan enkripsi password.
* **Login & Penerbitan Token:** Autentikasi user berbasis kredensial yang menghasilkan token JWT untuk digunakan pada request berikutnya.
* **Role-Based Access Control (RBAC):** Pembagian hak akses pengguna (misal: Admin, Kontributor, Client) untuk membatasi aksi di dalam sistem.

### 3.2. Service Manajemen Aset (Project Service)
* **Upload File & Validasi:** Mengunggah file aset digital dengan batasan ukuran dan tipe file tertentu ke dalam penyimpanan lokal/cloud.
* **Kategori & Tagging:** Mengelompokkan aset ke dalam kategori/folder digital serta menyematkan label (tag) meta untuk memudahkan pencarian cepat.
* **Versioning File:** Menyimpan riwayat perubahan file. Ketika pengguna mengunggah revisi baru dari file yang sama, sistem akan mencatatnya sebagai versi baru (v2, v3, dst.) tanpa menghapus versi lama.
* **Preview Thumbnail:** Menghasilkan preview visual atau gambar mini (thumbnail) secara otomatis untuk file gambar atau dokumen yang didukung.
* **Generate Link Sharing:** Membuat tautan (URL) unik dan aman yang dapat dibagikan kepada pihak eksternal/klien agar mereka dapat mengunduh atau melihat aset tanpa perlu login.

---

## 4. Kebutuhan Non-Fungsional (Non-Functional Requirements)
* **Containerization:** Semua service wajib dikemas menggunakan Dockerfile dan diorkestrasi menggunakan Docker Compose melalui jaringan internal Docker.
* **Security:** Kredensial database dan kunci JWT tidak boleh di-hardcode dalam kode aplikasi, melainkan wajib menggunakan Environment Variables (`.env`).
* **Deployment:** Aplikasi di-deploy sepenuhnya ke Ubuntu Server (VM/VPS) yang dapat diakses melalui port jaringan publik yang ditentukan di Gateway.