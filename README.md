# 🔍 SmartFound — UTM Lost & Found Platform

> A role-based web platform that centralises the lost-and-found process for the UTM community — replacing scattered WhatsApp groups with a structured, searchable workflow.

---

## 👥 Team DiCoding

| Name | Role | Responsibilities |
|------|------|-----------------|
| 🧑‍💼 **Tegar Insan Tohaga** | Project Manager & QA | Coordination, Git, end-to-end testing |
| 🎨 **Humaira Sheyla Nurfaiza** | Frontend Lead | Vue SPA, all UI pages, forms, Axios, jQuery |
| ⚙️ **Fathan Auzan Asykur** | Backend Lead | PHP Slim, JWT, REST endpoints, middleware |
| 🗄️ **Muhammad Rosyid Ridho Indrianto** | Database & Security | MySQL schema, PDO, XSS/CSRF/SQLi defense |

---

## ⚙️ Tech Stack

![Vue 3](https://img.shields.io/badge/Vue-3.x-42b883?style=flat-square&logo=vue.js)
![Vite](https://img.shields.io/badge/Vite-5.x-646cff?style=flat-square&logo=vite)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38bdf8?style=flat-square&logo=tailwindcss)
![jQuery](https://img.shields.io/badge/jQuery-3.7-0769ad?style=flat-square&logo=jquery)
![PHP](https://img.shields.io/badge/PHP-8.x-777bb4?style=flat-square&logo=php)
![Slim 4](https://img.shields.io/badge/Slim-4.x-74b545?style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479a1?style=flat-square&logo=mysql)
![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=flat-square&logo=jsonwebtokens)

---

## 🚀 Features

- 📋 **Unified reports feed** — browse all lost & found items in one searchable, filterable view
- 🔐 **Role-based access control** — Student / Officer / Admin with server-side enforcement
- 🖼️ **Image uploads** — attach a photo to every report for easy identification
- 💬 **Comment threads** — community-driven claiming and communication on each report
- 🔒 **Google OAuth 2.0** — one-click login with UTM Google accounts
- 🛡️ **Security-first** — JWT auth, bcrypt, PDO prepared statements, XSS/CSRF protection

---

## 🗺️ How It Works

```
User submits report (Lost / Found)
         ↓
   Vue 3 SPA (port 5173)
         ↓  REST + JWT
   PHP Slim 4 API (port 8080)
         ↓  PDO
      MySQL 8 Database
         ↓
  Community browses → comments → item reunited → report closed ✅
```

---

## 🏗️ Architecture

```
smartfound/
├── frontend/          → Vue 3 + Vite + Tailwind + Pinia + Axios + jQuery
└── backend/           → PHP Slim 4 + JWT + Google OAuth + PDO + MySQL
    ├── public/        → Entry point (index.php)
    ├── src/
    │   ├── Application/  Actions · Middleware · Routes
    │   ├── Domain/       Models · Repository interfaces
    │   └── Infrastructure/  PDO repos · JwtService · GoogleOAuthService
    └── database/      → schema.sql + seed.sql
```

---

## 🛠️ Setup

### Prerequisites
- PHP 8.1+, Composer
- Node.js 18+, npm
- MySQL 8

### Database

```bash
mysql -u root -p -e "CREATE DATABASE smartfound CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p smartfound < backend/database/schema.sql
mysql -u root -p smartfound < backend/database/seed.sql
```

### Backend

```bash
cd backend
composer install
cp .env.example .env      # fill in DB credentials, APP_SECRET, Google OAuth keys
php -S localhost:8080 -t public
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env      # set VITE_API_BASE_URL=http://localhost:8080/api/v1
npm run dev               # starts on http://localhost:5173
```

### Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `Admin@123` |

---

## 📡 API Overview

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/auth/register` | Public | Register account |
| POST | `/api/v1/auth/login` | Public | Login, returns JWT |
| GET | `/api/v1/auth/google/redirect` | Public | Google OAuth flow |
| GET | `/api/v1/reports` | JWT | List open reports (filterable) |
| POST | `/api/v1/reports` | JWT | Submit a new report |
| GET | `/api/v1/reports/{id}` | JWT | Report detail + comments |
| PATCH | `/api/v1/reports/{id}/status` | JWT (owner) | Close own report |
| DELETE | `/api/v1/reports/{id}` | JWT (officer\|admin) | Remove report |
| GET | `/api/v1/reports/closed` | JWT | Closed reports archive |
| GET | `/api/v1/users` | JWT (admin) | Manage all users |

---

## 🔒 Security

| Threat | Mitigation |
|--------|------------|
| SQL Injection | PDO prepared statements throughout |
| XSS | `htmlspecialchars()` on output; Vue template auto-escaping |
| CSRF | SameSite cookie on refresh token; JWT in memory |
| Broken Auth | 15-min access token + 7-day httpOnly refresh cookie |
| File upload abuse | MIME type check, 2 MB limit, UUID rename |

---

## 🗓️ Roadmap

- [x] User authentication (username/password + Google OAuth)
- [x] Lost & Found report submission with image upload
- [x] Browse, filter & search reports
- [x] Comment threads on reports
- [x] Close/resolve own reports
- [x] Officer moderation (delete reports)
- [x] Admin dashboard (manage users & reports)
- [ ] Email notification on new matching report
- [ ] Full-text search across item names
- [ ] Mobile-responsive PWA

---

## 🙌 Built With

- [Vue 3](https://vuejs.org/) + [Vite](https://vitejs.dev/) + [Pinia](https://pinia.vuejs.org/)
- [Slim Framework 4](https://www.slimframework.com/)
- [firebase/php-jwt](https://github.com/firebase/php-jwt)
- [league/oauth2-google](https://github.com/thephpleague/oauth2-google)
- [Tailwind CSS](https://tailwindcss.com/)

---

> 📚 Course: SECJ3483-03 Web Technology · UTM Johor Bahru · June 2026
