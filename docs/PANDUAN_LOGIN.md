# Panduan Log Masuk — Sistem ALP DBKL (Pembangunan / UAT)

**URL log masuk:** http://localhost/alp/public/login  

**Kata laluan semua akaun:** `password`

> Untuk pembangunan & UAT tempatan sahaja. **Jangan** guna akaun ini di production.

---

## Cara pantas (3 langkah)

1. Buka: http://localhost/alp/public/login  
2. E-mel + kata laluan `password`  
3. Klik **Log Masuk**

---

## Akaun mengikut peranan (URS v1.2)

| Peranan URS | E-mel | Kata laluan | Untuk apa |
|-------------|-------|-------------|-----------|
| **Super Admin** (teknikal) | `superadmin@dbkl.test` | `password` | Semua menu, tetapan, bypass |
| **Admin JP / Pentadbir** | `sysadmin@dbkl.test` | `password` | Pengguna, ALP, FY, matriks, Polisi URS |
| **ALP (pemohon)** | `alp01@dbkl.test` … `alp15@dbkl.test` | `password` | 15 ALP rasmi DBKL (Jun 2026) |
| ALP contoh pantas | `alp01@dbkl.test` | `password` | Datuk Muhammad Azmi bin Mohd Zain |
| **Urus Setia ALP** | `urussetiaalp@dbkl.test` | `password` | Bantu ALP-01 (skop ALP) |
| **Pegawai JP** | `urussetia@dbkl.test` | `password` | Semakan Pegawai JP (M04) |
| **Kerani Kewangan JP** | `kewangan@dbkl.test` | `password` | Baucar penuh `/pembayaran` (M06) |
| **JKEW** | `jkew@dbkl.test` | `password` | Skop SEC-007 — hanya rekod dihantar JKEW |
| Pegawai Teknikal (legacy) | `teknikal@dbkl.test` | `password` | Tiada menu aktif URS |
| **Peraku / PEPU (Pelulus)** | `pelulus@dbkl.test` | `password` | Kelulusan aras Peraku (≤ RM3,000) |
| **PEPU / Pengurusan Tertinggi** | `pengurusan@dbkl.test` | `password` | Kelulusan aras PEPU (> RM3,000) |

---

## Cadangan akaun ikut tugasan

| Nak buat… | Log masuk sebagai |
|-----------|-------------------|
| Semak semua / tetapan Polisi URS | `superadmin@dbkl.test` |
| Buat & hantar permohonan | `alp01@dbkl.test` |
| Semak permohonan (Pegawai JP) | `urussetia@dbkl.test` |
| Kemas kini baucar / bayaran | `kewangan@dbkl.test` |
| Semakan silang JKEW (skop dihantar) | `jkew@dbkl.test` |
| Peraku (≤ RM3,000) | `pelulus@dbkl.test` |
| PEPU (> RM3,000) | `pengurusan@dbkl.test` |

---

## Copy-paste pantas

```
URL:      http://localhost/alp/public/login
E-mel:    superadmin@dbkl.test
Kata laluan: password
```

```
E-mel:    alp01@dbkl.test
Kata laluan: password
```

```
E-mel:    urussetia@dbkl.test
Kata laluan: password
```

```
E-mel:    pelulus@dbkl.test
Kata laluan: password
```

```
E-mel:    pengurusan@dbkl.test
Kata laluan: password
```

---

## Jika tidak boleh log masuk

1. Pastikan XAMPP **Apache + MySQL** berjalan.  
2. Pastikan DB di-seed:

```bash
C:\xampp2\php\php.exe artisan migrate --force
C:\xampp2\php\php.exe artisan db:seed --force
```

3. Semak `.env` DB: `alp_dbkl` / `alp_user` / `alp_pass`.  
4. Polisi URS lalai **ON** (BR-001 / BR-002 / BR-003 / BR-005).

---

## Skop JKEW (SEC-007)

| Akaun | Permission | Apa yang nampak di `/pembayaran` |
|-------|------------|----------------------------------|
| `kewangan@dbkl.test` | `payments.manage` | Barisan baucar **penuh** |
| `jkew@dbkl.test` | `payments.view` + `payments.jkew_scope` | Hanya rekod **dihantar ke JKEW** |

Selepas seed baharu: `php artisan db:seed --class=RolePermissionSeeder` kemudian `DevSeeder` (atau seed penuh) supaya role `pegawai_jkew` wujud.
