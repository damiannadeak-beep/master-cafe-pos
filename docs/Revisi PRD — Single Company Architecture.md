# Perubahan PRD untuk Versi Single Company

## 1. Executive Summary & Core Objectives

### 1.1 Objective

Memungkinkan perusahaan untuk merancang, mengelola, dan menerbitkan penghargaan (*award*) serta sertifikat digital bagi karyawannya.

Setiap sertifikat dilengkapi dengan **QR Code unik + Logo Nusawork** untuk memverifikasi keabsahan dokumen secara publik tanpa memerlukan autentikasi *login*.

Sistem ini dirancang sebagai aplikasi khusus untuk **satu perusahaan**, sehingga tidak menggunakan konsep multi-tenancy atau isolasi data antar-perusahaan.

---

## 2. User Roles & Access

### HR Admin / Company Admin

**Access:** Nusawork Core / Award Module  
**Authentication:** SSO via Nusawork Core  
**Scope:**

- Upload atau memilih template sertifikat.
- Mengatur anchor point pada template.
- Mengelola penerima award.
- Mengatur scheduled reminder.
- Menerbitkan sertifikat.

### Employee

**Access:** Nusawork App / Public Link  
**Authentication:** Login Nusawork / Public URL  
**Scope:**

- Menerima sertifikat.
- Mengunduh PDF/Image.
- Membagikan public verification link.

### Public User / Verifier

**Access:**

`https://award.nusawork.com/v/{certificate_uuid}`

**Authentication:** Tidak membutuhkan login.

**Scope:**

- Scan QR Code.
- Memverifikasi keaslian sertifikat.
- Melihat informasi sertifikat.
- Mengunduh dokumen resmi.

---

## 3. System Architecture

### 3.1 Tech Stack & Application Architecture

- **Framework:** Laravel.
- **Application Type:** Standalone Laravel application / independent service.
- **Company Scope:** Single Company.
- **Database Strategy:** Single application database.
- **Authentication Integration:** Admin menggunakan autentikasi dari Nusawork Core.
- **Public Portal:** `award.nusawork.com` melayani halaman verifikasi sertifikat tanpa membutuhkan session authentication.
- **Storage:** Private object/file storage untuk template dan generated certificate.
- **Queue:** Background queue digunakan untuk proses rendering sertifikat dan pekerjaan asynchronous lainnya.

Aplikasi tidak menggunakan Laravel multi-tenancy engine seperti `stancl/tenancy` karena seluruh data pada sistem hanya dimiliki oleh satu perusahaan.

Arsitektur sederhana:

```text
Nusawork Core
      │
      │ Authentication / SSO
      ▼
Award Service
award.nusawork.com
      │
      ├── Admin Module
      ├── Certificate Engine
      ├── Scheduler
      ├── Public Verification
      │
      ▼
Single Database
      │
      ├── Award Templates
      ├── Award Schedules
      └── Certificates
```

---

## 6. Database Schema Design

### award_templates

- id (uuid, Primary Key)
- title (string)
- background_path (string)
- font_settings (json)
- is_preset (boolean, default: false)
- created_at (timestamp)
- updated_at (timestamp)

### award_schedules

- id (uuid, Primary Key)
- title (string)
- frequency (enum: monthly, quarterly, yearly, custom)
- next_run_date (date)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)

### certificates

- id (uuid, Primary Key — digunakan sebagai identifier sertifikat)
- template_id (FK to award_templates)
- employee_id (string)
- employee_name (string)
- award_title (string)
- issued_date (date)
- certificate_number (string, unique)
- qr_code_path (string)
- pdf_path (string)
- created_at (timestamp)
- updated_at (timestamp)

Tidak diperlukan tabel `tenants` maupun kolom `tenant_id` karena aplikasi hanya digunakan oleh satu perusahaan.

---

## 7. Development Roadmap & Implementation Sprints

### Sprint 1: Architecture & Authentication

- Setup standalone Laravel project.
- Integrasi authentication / SSO dengan Nusawork Core.
- Database migration.
- Role-based access untuk HR Admin.

### Sprint 2: Canvas Builder & Rendering Engine

- Safe File Upload Pipeline.
- Drag-and-Drop Canvas UI.
- Text Anchoring.
- Grouped QR + Logo Nusawork.
- Image/PDF Generation Engine.

### Sprint 3: Recurrence, Public Viewer & OG Image

- Scheduler & Email Notification System.
- Public route `award.nusawork.com/v/{uuid}`.
- Public certificate verification page.
- Dynamic OpenGraph metadata/image.

### Sprint 4: Security Hardening & QA

- Penetration testing:
  - XSS.
  - File injection.
  - Unauthorized access.
  - UUID enumeration attempt.
  - Rate limit.
- End-to-End Testing.
- Production Deployment.