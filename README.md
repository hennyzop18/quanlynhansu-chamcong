---
title: QLNS - Human Resource Management System
emoji: 👥
colorFrom: blue
colorTo: indigo
sdk: docker
app_port: 7860
pinned: false
---

# QLNS - Human Resource Management System

A comprehensive, production-ready HR and Attendance Management System built with Laravel, featuring AI-powered FaceID recognition, automated payroll, and cloud deployment.

## 🚀 Key Features

- **Employee Management (HRM):** Full CRUD for employee profiles, departments, and positions.
- **AI FaceID Attendance:** Real-time facial recognition for employee check-in/out using `face-api.js`.
- **Automated Payroll:** Multi-mode salary calculations (Fixed, Hourly, and Override) with automated payslip generation.
- **Leave & Overtime Management:** Employee request workflow with administrator approval system.
- **Work Schedules:** Flexible shift management and registration for employees.

## 🛠 Tech Stack

- **Backend:** Laravel 11.x, PHP 8.2+
- **Frontend:** Tailwind CSS, Vite, Alpine.js
- **Database:** PostgreSQL (Supabase)
- **AI/ML:** Face-api.js (TensorFlow.js)
- **Deployment:** Docker on Hugging Face Spaces (Nginx & PHP-FPM)

## 🪟 Run on Windows 10/11 (one click)

1. `git clone -b windows-support https://github.com/hennyzop18/quanlynhansu-chamcong.git`
2. Double-click **`setup-windows.bat`** (once). It installs XAMPP 8.2 + Node LTS via winget if missing, enables PHP extensions, creates the `qlns` database, configures `QLNS/.env`, downloads face-api models, runs migrate + seed, builds assets and installs the Kiosk app.
3. Double-click **`start-windows.bat`** every time: starts MySQL, `php artisan serve` on http://127.0.0.1:8000, opens the browser and the Kiosk app.

Sample login: `admin@example.com` / `password`.
For the Kiosk on Windows 11 24H2+, turn on *Settings → Privacy & security → Location* (and "Let desktop apps access your location"), otherwise Windows hides the Wi-Fi name. Add your Wi-Fi name and IP to `QLNS/config/hr.php`.

## ⚙️ Installation & Setup

### 1. Clone & Environment
```bash
git clone https://github.com/hennyzop18/QLNS.git
cd QLNS
cp .env.example .env
```

### 2. Local Setup
```bash
composer install && npm install
npm run build
php artisan migrate --seed
php artisan serve
```

---

## 🔄 Application Workflow

### Flow 1: Login & Authorization
```text
User access http://[domain]
|
▼
Not logged in? → Redirect → /login
|
▼ (After authentication)
Check User Role
├── Admin    → /admin/employees (HR Management Dashboard)
└── Employee → /employee/attendance/history (Personal Attendance History)
```

### Flow 2: AI Attendance via Face ID (Kiosk)
```text
[Kiosk-Helper-App]           [Laravel Backend]                     [Kiosk page - browser]
      | 1. Read Wi-Fi SSID          |                                       |
      | 2. POST /api/kiosk/request-token {ssid}                             |
      |---------------------------->| check office IP + SSID                |
      |<----------------------------| 3. otp_token (valid KIOSK_TOKEN_TTL_MINUTES)
      | 4. open /kiosk/attendance?token=...  ------------------------------->|
      |                             |        5. face-api.js detects a face  |
      |                             |           and computes a 128-d vector |
      |                             |<--- 6. POST /api/kiosk/record-attendance {otp_token, descriptor}
      |                             | 7. Euclidean distance vs. all registered faces
      |                             |    (<= KIOSK_FACE_MATCH_THRESHOLD) -> employee
      |                             | 8. first scan of the day = check-in, later = check-out
      |                             |    (repeat scans within cooldown are ignored)
      |                             |--- 9. {name, action, message} -------->|
```

### Flow 3: Payroll Generation & Distribution
```text
[HR Admin]                      [Payroll Engine]                [Employee Email]
      |                               |                                 |
      | 1. Trigger Month-End Process  |                                 |
      |------------------------------>|                                 |
      |                               | 2. Aggregate Attendance Hours   |
      |                               | 3. Apply Salary Formula/PIT     |
      |                               | 4. Generate PDF Payslip         |
      |                               |-------------------------------->|
      | 5. Confirm & Distribute       |                                 | 6. Receive Digital Payslip
      |<------------------------------|                                 |
```

### Flow 4: Work Schedule Registration
```text
[Employee]                      [Work Schedule System]          [HR Admin]
      |                               |                                 |
      | 1. Submit Shift Registration  |                                 |
      |------------------------------>|                                 |
      |                               | 2. Notify Pending Request       |
      |                               |-------------------------------->|
      |                               |                                 | 3. Review & Approve/Reject
      |                               |<--------------------------------|
      | 4. Receive Notification       |                                 |
      |<------------------------------|                                 |
```

## 📁 Main Project Structure

```text
QLNS/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          ← Controllers for HR, Salary, and Reports
│   │   ├── Api/            ← AI (FaceID) and Kiosk API endpoints
│   │   └── Employee/       ← Controllers for Employee self-service
│   └── Models/             ← Eloquent Models (Employee, Attendance, Salary...)
├── database/
│   ├── migrations/         ← Database schema definitions
│   └── seeders/            ← Sample data generators
├── resources/
│   ├── views/
│   │   ├── admin/          ← Admin dashboard templates
│   │   ├── employee/       ← Employee portal templates
│   │   └── layouts/        ← Base layouts (Responsive configurations)
│   └── css/                ← Tailwind CSS and Custom UI styles
├── public/
│   └── models/             ← Pre-trained AI weights (SSD MobileNet)
├── docker/                 ← Nginx & PHP-FPM configuration files
├── Dockerfile              ← Optimized build for Hugging Face Spaces
└── .env                    ← Environment configuration
```

## 🔐 System Permissions

- **Administrator:** Full access to employee records, payroll settings, approval workflows, and system reports.
- **Employee:** Personal dashboard for attendance tracking, profile updates, and payslip viewing.

## ⚙ Critical Environment Variables

| Variable | Description |
|---|---|
| `DB_HOST` | Supabase Transaction Pooler host (Port 6543) |
| `DB_PASSWORD` | Database access password |
| `APP_URL` | The public URL of your Hugging Face Space |
| `FORCE_JAVASCRIPT_ACTIONS_TO_NODE24` | GitHub Actions compatibility |

## 🤝 Agile Methodology
This project follows the **Scrum** framework, featuring 1-week Sprints and task management via GitHub Projects.

## 📄 License
Licensed under the [MIT license](https://opensource.org/licenses/MIT).
