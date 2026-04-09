# AttendanceMS — Vulnerable Web Application

> ⚠️ **WARNING: This application is intentionally vulnerable. Do NOT deploy on a public server or production environment. For educational/CTF use only.**

A PHP-based Attendance Management System built with intentional security vulnerabilities for security education.

---

## Tech Stack

| Layer    | Technology         |
|----------|--------------------|
| Backend  | PHP 8.1            |
| Web Server | Apache 2.4       |
| Database | MySQL 8.0          |
| Container| Docker + Compose   |

---

## Project Structure

```
vuln-attendance/
├── docker-compose.yml        
├── Dockerfile                
├── mysql/
│   └── init.sql              
└── src/                      
    ├── index.php             
    ├── dashboard.php         
    ├── attendance.php        
    ├── records.php           
    ├── upload.php            
    ├── logout.php            
    ├── info.php              
    ├── exploit_cookie.php    
    ├── db.php                
    ├── auth.php              
    ├── css/
    │   └── style.css
    ├── partials/
    │   ├── header.php
    │   └── sidebar.php
    └── uploads/              
```

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed
- [VS Code](https://code.visualstudio.com/) (recommended: install the **Docker** extension)
- Git

---

## Deployment Instructions

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/vuln-attendance.git
cd vuln-attendance
```

### 2. Start the application

```bash
docker compose up --build
```

Wait for output like:
```
vuln_attendance_db   | ... MySQL init process done. Ready for connections.
vuln_attendance_web  | AH00558: apache2: Could not reliably determine...
```

### 3. Access the application

URL 
http://localhost:8080 
http://localhost:8080/info.php 
http://localhost:8080/upload.php 

### 4. Demo credentials

| Username | Password | Role |
|----------|----------|------|
| dr.smith | password123 | Lecturer |
| prof.tan | letmein | Lecturer |

### 5. Stop the application

```bash
docker compose down
```

To also delete the database volume:
```bash
docker compose down -v
```

---