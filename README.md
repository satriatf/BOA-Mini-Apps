# Team Management System

Aplikasi manajemen tim dengan Laravel dan Filament Admin Panel.

## 🚀 Fitur Utama

### 📊 Dashboard

-   Overview sistem manajemen tim

### 👥 Employee Management

-   CRUD karyawan dengan level hierarki (Staff, SH)

### 📅 Calendar System

-   **Monthly**: Kalender bulanan
-   **Yearly**: Kalender tahunan
-   **Project Timeline**: Timeline proyek

### 📋 Task Management

-   **Projects**: Manajemen proyek dengan status tracking
-   **Non-Projects**: Manajemen tugas non-proyek (MTC)

### 🗂️ Master Data Management

-   **Project Statuses**: Status proyek (PEMBAHASAN, DEV, UAT, dll.)
-   **Non-Project Types**: Jenis tugas (PROBLEM, INCIDENT, dll.)
-   **Applications**: Daftar aplikasi (Ad1Forflow, BPKBLib, dll.)
-   **Holidays**: Manajemen hari libur

## 🛠️ Teknologi

-   **Backend**: Laravel 11
-   **Admin Panel**: Filament 3
-   **Database**: MySQL
-   **Frontend**: Livewire + Alpine.js

## 📁 CRUD Pages

### 👥 Employees

-   List, Create, Edit, Delete

### 📋 Projects

-   List, Create (Submit/Draft/Cancel), Edit (Save Changes/Cancel), Delete

### 📋 Non-Projects (MTC)

-   List, Create (Submit/Draft/Cancel), Edit (Save Changes/Cancel), Delete

### 🗂️ Master Data

-   **Project Statuses**: CRUD lengkap
-   **Non-Project Types**: CRUD lengkap
-   **Applications**: CRUD lengkap
-   **Holidays**: CRUD lengkap

## 🗄️ Database Tables

-   `users` - Data karyawan
-   `projects` - Data proyek
-   `mtcs` - Data tugas non-proyek
-   `master_project_statuses` - Status proyek
-   `master_non_project_types` - Jenis tugas
-   `master_applications` - Daftar aplikasi
-   `holidays` - Hari libur

## 🎯 Form Features

### Project Form

-   Project Ticket No, Project Name, Project Status (dropdown)
-   Technical Lead (dropdown), PIC (multiple)
-   Start Date, End Date, Total Days, Percent Done

### Non-Project Form (MTC)

-   Created By, No. Ticket, Description
-   Type (dropdown), Resolver PIC, Solution
-   Application (dropdown), Date, Attachments

### Master Data Forms

-   **Project Status**: Name
-   **Non-Project Type**: Name
-   **Application**: Name
-   **Holiday**: Date, Description

## 🚀 Installation

```bash
git clone [repository-url]
cd BOA-Mini-Apps
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=MasterDataSeeder
npm run build
php artisan serve
```

## 📝 Usage

1. Login ke `/admin`
2. Setup Master Data di menu **Master**
3. Kelola Projects di **Tasks > Projects**
4. Kelola Non-Projects di **Tasks > Non-Projects**

## 🔧 Configuration

### Navigation Groups

-   **Calendar**: Monthly, Yearly, Project Timeline
-   **Tasks**: Projects, Non-Projects
-   **Master**: Project Statuses, Non-Project Types, Applications, Holidays

### File Upload

-   Path: `public/storage/mtc_attachments`
-   Max Size: 10MB per file

---

**Team Management System** - Built with ❤️ using Laravel & Filament
