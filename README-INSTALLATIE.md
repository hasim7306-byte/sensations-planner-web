# 🎯 Sensations To Go Planner - Installatie Handleiding

## 🚀 Snelle Start

### 1-Klik Web Installatie

1. **Upload alle bestanden** naar uw webserver
2. **Ga naar** `https://uwdomain.nl/install.php`
3. **Volg de wizard** (5 minuten setup)
4. **Klaar!** Start met het beheren van uw workforce

---

## 📋 Voor de Installatie

### Server Vereisten ✅

| Component | Minimaal | Aanbevolen |
|-----------|----------|------------|
| **PHP** | 7.4+ | 8.0+ |
| **MySQL** | 5.7+ | 8.0+ |
| **Schijfruimte** | 100MB | 500MB |
| **RAM** | 512MB | 1GB+ |

### PHP Extensies Checklist
```
✅ PDO & PDO_MySQL (database)
✅ CURL (API communicatie)  
✅ JSON (data verwerking)
✅ MBString (tekst verwerking)
✅ OpenSSL (beveiliging)
✅ ZIP (bestand verwerking)
```

---

## 🎬 Installatie Stappen

### Stap 1: Bestanden Voorbereiden
```bash
# Download of kopieer alle bestanden
# Zorg voor juiste permissies
chmod -R 755 .
chmod 777 config/ uploads/ logs/
```

### Stap 2: Database Aanmaken
```sql
CREATE DATABASE sensations_planner;
-- Of gebruik de installer wizard
```

### Stap 3: Web Installer Uitvoeren

**Open in browser:** `https://uwdomain.nl/install.php`

De installer guide je door:

1. **🎉 Welkomst** - Overzicht van wat wordt geïnstalleerd
2. **🔧 Systeem Check** - Controle van server vereisten  
3. **🗄️ Database** - Configuratie van database verbinding
4. **👤 Admin Account** - Hoofdbeheerder aanmaken
5. **⚙️ Installatie** - Automatische setup van het systeem
6. **✅ Voltooid** - Klaar voor gebruik!

### Stap 4: Beveiliging
```bash
# BELANGRIJK: Verwijder installer na voltooiing
rm install.php
```

---

## 🎯 Wat wordt Geïnstalleerd?

### 📱 Complete Web Applicatie
- **Dashboard** - Overzicht en statistieken
- **Roostering** - Sleep & drop planning interface  
- **Tijd Registratie** - In/uitklok systeem
- **Verlofbeheer** - Aanvraag en goedkeuring workflow
- **Team Chat** - Interne communicatie
- **Gebruikersbeheer** - Admin, managers, medewerkers

### 🗄️ Database Structuur
- **Users** - Gebruikers en rechten
- **Shifts** - Roosters en diensten
- **Time Entries** - Tijd registraties  
- **Leave Requests** - Verlofaanvragen
- **Chat Messages** - Team communicatie
- **System Settings** - Configuratie

### 🎨 Nederlandse Interface
- Volledig Nederlandse teksten
- Nederlandse datum/tijd formatting
- Local business workflows
- GDPR compliant setup

---

## 🔐 Standaard Inloggegevens

**Na installatie krijgt u:**
- **Email**: admin@sensationstogo.nl
- **Rol**: Administrator  
- **Wachtwoord**: Zelf in te stellen tijdens installatie

**Of uw eigen gegevens zoals ingevoerd**

---

## 📱 Direct na Installatie

### Voor Administrators:
1. **Login** met uw admin account
2. **Voeg medewerkers toe** via Gebruikersbeheer
3. **Stel afdelingen in** en rollen toe
4. **Maak eerste roosters** aan
5. **Train uw team** in het gebruik

### Voor Medewerkers:
1. **Ontvang** inloggegevens van admin
2. **Login** via uwdomain.nl  
3. **Bekijk rooster** en klok in/uit
4. **Vraag verlof aan** via het systeem
5. **Communiceer** via team chat

---

## 🛠️ Troubleshooting

### ❌ Installatie Problemen

**Database Verbinding Mislukt:**
```
✅ Controleer database gegevens
✅ Test MySQL verbinding
✅ Controleer gebruikers rechten
```

**Permissie Fouten:**
```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod 777 config/ uploads/ logs/
```

**White Screen:**
```php
// Voeg toe aan begin van install.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 🔍 Na Installatie Problemen

**Kan niet inloggen:**
- Controleer email/wachtwoord
- Reset via database indien nodig
- Check config/installed.lock bestaat

**Pagina's laden niet:**
- Controleer .htaccess bestand
- Test server URL rewrite
- Check Apache mod_rewrite

---

## 🚀 Performance Tips

### Server Optimalisatie
```php
// php.ini aanpassingen
memory_limit = 256M
upload_max_filesize = 20M
max_execution_time = 300
```

### Database Optimalisatie  
```sql
-- Voeg indexes toe na installatie
CREATE INDEX idx_shifts_date ON shifts(date);
CREATE INDEX idx_time_date ON time_entries(date);
```

### Beveiliging Verhogen
```apache
# .htaccess toevoegen
<Files "config/*">
    Require all denied
</Files>
```

---

## 📞 Support & Updates

### 🆘 Hulp Nodig?

**Technische Support:**
- 📧 **Email**: support@sensationstogo.nl
- 🌐 **Website**: https://sensationstogo.nl  
- 📖 **Documentatie**: In-app help sectie

**Pre-Sales Vragen:**
- ☎️ **Telefoon**: +31 (0)20 123 4567
- 💬 **Chat**: Via website

### 🔄 Updates

**Automatische Notificaties:**
- Update alerts in admin dashboard
- Email notificaties voor belangrijke updates
- Changelog beschikbaar in systeem

**Handmatige Updates:**
- Download nieuwste versie
- Backup database voor update
- Vervang bestanden (behoud config/)
- Run update wizard indien beschikbaar

---

## 🎉 Success!

**🎯 Gefeliciteerd!** 

U heeft nu een professioneel workforce management systeem dat volledig ready is voor:

- ✅ **Employee Management** - Gebruikers en rollen
- ✅ **Smart Scheduling** - Intelligente roostering  
- ✅ **Time Tracking** - Nauwkeurige tijdregistratie
- ✅ **Leave Management** - Gestroomlijnd verlofbeheer
- ✅ **Team Communication** - Interne chat systeem
- ✅ **Mobile Ready** - Werkt op alle apparaten
- ✅ **GDPR Compliant** - Veilig en privacy-proof

**Start nu met het beheren van uw workforce als een pro! 🚀**

---

*Sensations To Go Planner - Your Complete Workforce Management Solution*

**Made with ❤️ in the Netherlands**