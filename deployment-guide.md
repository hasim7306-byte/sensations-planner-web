# 🚀 Sensations To Go Planner - Deployment Guide

## Web Server Installatie

### Stap 1: Server Vereisten

**Minimale Vereisten:**
- PHP 7.4 of hoger
- MySQL 5.7 of hoger  
- Apache/Nginx webserver
- 100MB vrije schijfruimte
- SSL certificaat (aanbevolen)

**PHP Extensies:**
- pdo, pdo_mysql
- curl, json, mbstring
- openssl, zip
- gd (voor afbeeldingen)

### Stap 2: Bestanden Uploaden

1. **Download het systeem** of kopieer alle bestanden naar uw webserver
2. **Upload naar web directory** (public_html, www, htdocs, etc.)
3. **Zorg voor juiste permissies**:
   ```bash
   chmod 755 /var/www/html/
   chmod -R 755 assets/
   chmod -R 777 config/ uploads/ logs/
   ```

### Stap 3: Database Voorbereiden

**MySQL Database aanmaken:**
```sql
CREATE DATABASE sensations_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'planner_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON sensations_planner.* TO 'planner_user'@'localhost';
FLUSH PRIVILEGES;
```

### Stap 4: Web Installatie

1. **Ga naar uw website** in de browser
2. **Open install.php**: `https://uwdomain.nl/install.php`
3. **Volg de installatie wizard**:
   - Systeem controle
   - Database configuratie  
   - Admin account setup
   - Automatische installatie
   - Voltooiing

### Stap 5: Na Installatie

**Veiligheid:**
```bash
# Verwijder installer (belangrijk!)
rm install.php

# Controleer permissies
chmod 644 config/database.php
chmod 644 config/installed.lock
```

**Backup Setup:**
```bash
# Maak backup script
#!/bin/bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
tar -czf files_backup_$(date +%Y%m%d).tar.gz .
```

## 🔧 Server Configuratie

### Apache .htaccess
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Beveiliging
<Files "config/*">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>
```

### Nginx Configuratie
```nginx
server {
    listen 80;
    server_name uwdomain.nl;
    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ ^/(config|logs)/ {
        deny all;
        return 404;
    }
}
```

## 📱 Features na Installatie

### Voor Administrators:
- **Gebruikersbeheer** - Medewerkers en managers toevoegen
- **Systeem configuratie** - Bedrijfsinstellingen aanpassen
- **Rapportages** - Overzichten van tijd en prestaties
- **Backup beheer** - Database en bestanden backup

### Voor Managers:
- **Roostering** - Diensten plannen en beheren
- **Verlof goedkeuring** - Verlofaanvragen behandelen  
- **Tijd goedkeuring** - Gewerkte uren controleren
- **Team overzicht** - Status van medewerkers

### Voor Medewerkers:
- **Tijd registratie** - In/uitklokken
- **Rooster inzien** - Eigen diensten bekijken
- **Verlof aanvragen** - Vakantie en verlof regelen
- **Team communicatie** - Chat met collega's

## 🔒 Beveiliging

### SSL/HTTPS Setup
```bash
# Let's Encrypt (gratis SSL)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d uwdomain.nl
```

### Database Beveiliging
```sql
-- Sterke wachtwoorden
ALTER USER 'planner_user'@'localhost' IDENTIFIED BY 'zeer_sterk_wachtwoord_123!';

-- Toegang beperken
REVOKE ALL PRIVILEGES ON *.* FROM 'planner_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON sensations_planner.* TO 'planner_user'@'localhost';
```

### File Permissions
```bash
# Optimale permissies
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 777 uploads/ logs/
chmod 600 config/database.php
```

## 🚀 Performance Optimalisatie

### PHP Configuratie (php.ini)
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
session.gc_maxlifetime = 86400
```

### MySQL Optimalisatie
```sql
-- Index optimalisatie
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_shifts_date ON shifts(date);
CREATE INDEX idx_time_entries_date ON time_entries(date, employee_id);
```

### Caching
```php
// PHP OPcache inschakelen
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

## 📊 Monitoring & Maintenance

### Log Monitoring
```bash
# Error logs bekijken
tail -f /var/log/apache2/error.log
tail -f logs/application.log

# Database queries monitoren
mysql -e "SHOW PROCESSLIST;"
```

### Automatische Updates
```bash
#!/bin/bash
# update.sh - Automatische backup en update script
cd /var/www/html
mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M).sql
# Download nieuwe versie en installeer
```

### Health Check
```bash
#!/bin/bash
# Status check script
curl -f https://uwdomain.nl/ > /dev/null
if [ $? -ne 0 ]; then
    echo "Website down!" | mail -s "Alert" admin@uwdomain.nl
fi
```

## 🆘 Troubleshooting

### Veelvoorkomende Problemen

**1. Database Connection Error**
```php
// Check config/database.php
// Controleer MySQL service: sudo service mysql status
```

**2. Permission Denied**
```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

**3. White Screen of Death**
```php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Support Contact

Voor technische ondersteuning:
- **Email**: support@sensationstogo.nl  
- **Website**: https://sensationstogo.nl
- **Documentatie**: Beschikbaar in de app onder Help

---

*Sensations To Go Planner - Complete Workforce Management Solution* 🎯