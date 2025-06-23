# CMS - Content Management System

Ein modernes, PHP-basiertes Content Management System mit visueller Page-Builder-Funktionalität, Media-Management und benutzerfreundlicher Admin-Oberfläche.

## 📋 Inhaltsverzeichnis

- [Übersicht](#übersicht)
- [Features](#features)
- [Systemanforderungen](#systemanforderungen)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Verwendung](#verwendung)
- [Projektstruktur](#projektstruktur)
- [API-Dokumentation](#api-dokumentation)
- [Entwicklung](#entwicklung)
- [Troubleshooting](#troubleshooting)
- [Lizenz](#lizenz)

## 🎯 Übersicht

Dieses CMS ist ein vollständiges Content Management System, das entwickelt wurde, um die Erstellung und Verwaltung von Webseiten zu vereinfachen. Es bietet einen visuellen Page Builder, umfassendes Media-Management, Menüverwaltung und eine intuitive Admin-Oberfläche.

### Hauptmerkmale

- **Visueller Page Builder**: Drag & Drop Interface für die Seitenerstellung
- **Media Management**: Upload und Verwaltung von Bildern, Videos und Dokumenten
- **Menüverwaltung**: Dynamische Menüerstellung und -verwaltung
- **Benutzerverwaltung**: Rollenbasierte Zugriffskontrolle (Admin/Author)
- **Responsive Design**: Mobile-first Ansatz
- **SEO-freundlich**: Optimierte URLs und Meta-Tags

## ✨ Features

### 🎨 Page Builder
- Drag & Drop Block-System
- Vorschau in Echtzeit
- Verschiedene Block-Typen (Text, Bild, etc.)
- Responsive Layout-Optionen

### 📁 Media Management
- Upload verschiedener Dateitypen (Bilder, PDFs)
- Alt-Text und Titel-Verwaltung
- Kategorisierung und Suchfunktion
- Thumbnail-Generierung

### 🧭 Menüverwaltung
- Dynamische Menüerstellung
- Drag & Drop Menü-Reihenfolge
- Mehrere Menüs pro Website

### 👥 Benutzerverwaltung
- Rollenbasierte Zugriffskontrolle
- Admin und Author-Rollen
- Profilbilder und Benutzerinformationen
- Sicheres Login-System

### ⚙️ Einstellungen
- Website-Konfiguration
- Logo-Upload
- Debug-Modi
- Systemeinstellungen

## 🔧 Systemanforderungen

### Server-Anforderungen
- **PHP**: 8.0 oder höher
- **MySQL**: 5.7 oder höher / MariaDB 10.2 oder höher
- **Webserver**: Apache 2.4+ oder Nginx
- **PHP-Erweiterungen**:
  - PDO
  - PDO_MySQL
  - GD (für Bildverarbeitung)
  - JSON
  - Session
  - Fileinfo

### Empfohlene Umgebung
- **XAMPP**: Für lokale Entwicklung
- **Composer**: Für Dependency Management (optional)
- **Git**: Für Versionskontrolle

## 🚀 Installation

### 1. Repository klonen
```bash
git clone [repository-url]
cd cms
```

### 2. Datenbank einrichten
```sql
-- Datenbank erstellen
CREATE DATABASE cms CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Benutzer erstellen (optional)
CREATE USER 'cms_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON cms.* TO 'cms_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Datenbank-Schema importieren
```bash
mysql -u root -p cms < sql/schema.sql
mysql -u root -p cms < sql/seed.sql
```

### 4. Konfiguration anpassen
Bearbeiten Sie die Datei `config/config.php`:

```php
// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Basis-URL anpassen
define('BASE_URL', 'http://localhost/cms');
```

### 5. Berechtigungen setzen
```bash
# Upload-Verzeichnisse beschreibbar machen
chmod 755 public/uploads/
chmod 755 public/uploads/settings/
chmod 755 public/uploads/users/
```

### 6. Webserver konfigurieren
Stellen Sie sicher, dass der Webserver auf das `public/` Verzeichnis zeigt oder konfigurieren Sie URL-Rewriting.

## ⚙️ Konfiguration

### Umgebungsvariablen
Die wichtigsten Konfigurationsoptionen in `config/config.php`:

| Variable | Beschreibung | Standard |
|----------|--------------|----------|
| `DB_HOST` | Datenbank-Host | localhost |
| `DB_NAME` | Datenbank-Name | cms |
| `DB_USER` | Datenbank-Benutzer | root |
| `DB_PASS` | Datenbank-Passwort | (leer) |
| `BASE_URL` | Basis-URL der Anwendung | http://localhost/cms |

### Sicherheitseinstellungen
```php
// Session-Konfiguration
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => true, // Für HTTPS
    'samesite' => 'Strict'
]);
```

## 📖 Verwendung

### Standard-Benutzer
Das System wird mit zwei vorkonfigurierten Benutzern ausgeliefert:

| Benutzername | Passwort | Rolle | Beschreibung |
|--------------|----------|-------|--------------|
| `admin` | `admin123` | Admin | Vollzugriff auf alle Funktionen |
| `test` | `test123` | Author | Eingeschränkter Zugriff für Tests |

### Admin-Bereich aufrufen
1. Navigieren Sie zu `http://localhost/cms/admin`
2. Melden Sie sich mit einem der Standard-Benutzer an:
   - **Admin**: `admin` / `admin123`
   - **Test**: `test` / `test123`
3. Sie werden zum Dashboard weitergeleitet

### Erste Schritte
1. **Benutzer erstellen**: Gehen Sie zu Admin → Users → New User
2. **Seite erstellen**: Admin → Pages → New Page
3. **Media hochladen**: Admin → Media → Upload
4. **Menü erstellen**: Admin → Menus → Create Menu

### Page Builder verwenden
1. Erstellen Sie eine neue Seite
2. Klicken Sie auf "Page Builder"
3. Ziehen Sie Blöcke per Drag & Drop
4. Konfigurieren Sie die Block-Einstellungen
5. Speichern Sie die Seite

### Benutzerrollen

#### Admin-Rolle
- Vollzugriff auf alle Funktionen
- Benutzerverwaltung
- Systemeinstellungen
- Alle Seiten und Medien verwalten

#### Author-Rolle
- Seiten erstellen und bearbeiten
- Medien hochladen
- Eingeschränkter Zugriff auf Einstellungen

## 📁 Projektstruktur

```
cms/
├── admin/                 # Admin-Bereich
│   ├── index.php         # Admin-Dashboard
│   ├── login.php         # Login-Seite
│   ├── media/            # Media-Management
│   ├── menus/            # Menüverwaltung
│   ├── pages/            # Seitenverwaltung
│   ├── settings/         # Systemeinstellungen
│   └── users/            # Benutzerverwaltung
├── config/
│   └── config.php        # Hauptkonfiguration
├── core/                 # Kern-System
│   ├── Controller/       # MVC-Controller
│   ├── Model/           # Datenmodelle
│   ├── Controller.php   # Basis-Controller
│   ├── Model.php        # Basis-Model
│   ├── Router.php       # URL-Routing
│   └── View.php         # Template-System
├── inc/                 # Hilfsfunktionen
│   ├── auth.php         # Authentifizierung
│   ├── functions.php    # Allgemeine Funktionen
│   └── renderer.php     # Template-Renderer
├── public/              # Öffentliche Dateien
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript
│   ├── uploads/        # Hochgeladene Dateien
│   └── index.php       # Frontend-Entry-Point
├── sql/                # Datenbank-Skripte
│   ├── schema.sql      # Datenbankschema
│   └── seed.sql        # Beispieldaten
├── templates/          # Templates
│   ├── admin/          # Admin-Templates
│   └── default/        # Frontend-Templates
└── index.php           # Haupt-Router
```

## 🔌 API-Dokumentation

### Media API
```php
// Media hochladen
POST /admin/media/upload

// Media abrufen
GET /admin/media/get?id={id}

// Media löschen
DELETE /admin/media/delete?id={id}
```

### Pages API
```php
// Seite erstellen
POST /admin/pages/new

// Seite bearbeiten
PUT /admin/pages/edit?id={id}

// Seite löschen
DELETE /admin/pages/delete?id={id}
```

### Menus API
```php
// Menü erstellen
POST /admin/menus/create

// Menü-Items hinzufügen
POST /admin/menus/add-item

// Menü-Reihenfolge aktualisieren
POST /admin/menus/update-order
```

## 🛠️ Entwicklung

### Entwicklungsumgebung einrichten
```bash
# Repository klonen
git clone [repository-url]
cd cms

# Datenbank einrichten
mysql -u root -p < sql/schema.sql

# Konfiguration anpassen
cp config/config.php config/config.local.php
# Bearbeiten Sie config.local.php
```

### Coding Standards
- **PHP**: PSR-12 Coding Standards
- **JavaScript**: ES6+ mit JSDoc-Kommentaren
- **CSS**: BEM-Methodologie
- **HTML**: Semantic HTML5

### Debugging
```php
// Debug-Modus aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Logging aktivieren
define('DEBUG_MODE', true);
```

### Tests
```bash
# Unit Tests ausführen (falls implementiert)
php vendor/bin/phpunit

# Manuelle Tests
# 1. Admin-Login testen (admin/admin123)
# 2. Test-Login testen (test/test123)
# 3. Page Builder testen
# 4. Media-Upload testen
# 5. Menüverwaltung testen
```

## 🔍 Troubleshooting

### Häufige Probleme

#### 1. Datenbankverbindung fehlgeschlagen
**Problem**: "Connection failed" Fehler
**Lösung**: 
- Überprüfen Sie die Datenbank-Konfiguration in `config/config.php`
- Stellen Sie sicher, dass MySQL läuft
- Überprüfen Sie Benutzername und Passwort

#### 2. Upload-Fehler
**Problem**: Dateien können nicht hochgeladen werden
**Lösung**:
```bash
# Berechtigungen setzen
chmod 755 public/uploads/
chmod 755 public/uploads/settings/
chmod 755 public/uploads/users/
```

#### 3. 404-Fehler
**Problem**: Seiten werden nicht gefunden
**Lösung**:
- Überprüfen Sie die URL-Rewriting-Konfiguration
- Stellen Sie sicher, dass `.htaccess` vorhanden ist
- Überprüfen Sie die `BASE_URL` in der Konfiguration

#### 4. Session-Probleme
**Problem**: Login funktioniert nicht
**Lösung**:
```php
// Session-Konfiguration überprüfen
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => false, // Für HTTP in Entwicklung
    'samesite' => 'Lax'
]);
```

#### 5. Login-Probleme
**Problem**: Standard-Benutzer können sich nicht anmelden
**Lösung**:
- Überprüfen Sie, ob die Datenbank korrekt importiert wurde
- Stellen Sie sicher, dass `sql/seed.sql` ausgeführt wurde
- Versuchen Sie die Standard-Anmeldedaten:
  - Admin: `admin` / `admin123`
  - Test: `test` / `test123`

### Logs überprüfen
```bash
# PHP-Fehler-Logs
tail -f /var/log/apache2/error.log

# MySQL-Logs
tail -f /var/log/mysql/error.log
```

## 📄 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE) Datei für Details.

*Letzte Aktualisierung: Dezember 2024*
