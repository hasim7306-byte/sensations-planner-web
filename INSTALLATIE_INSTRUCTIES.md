# 🚀 Sensations To Go Planner Enhanced v2.0 - Installatie

## 📋 BENODIGDE BESTANDEN

### Hoofd Applicatie Bestanden:
- `demo_index.php` - Hoofd applicatie bestand
- `demo_db.php` - File-based database simulator
- `demo_auth.php` - Authenticatie en wachtwoord beheer
- `demo_users.php` - Admin gebruikersbeheer interface
- `demo_login.php` - Enhanced login pagina
- `demo_dashboard.php` - Live dashboard

### Assets:
- `assets/css/style.css` - Complete enhanced styling
- `assets/js/app.js` - Interactive JavaScript

### Extra:
- `live_demo.html` - Demo preview pagina
- `demo_install.php` - Enhanced installer (alternatief)

## 🛠️ INSTALLATIE STAPPEN

### Stap 1: Bestanden Uploaden
1. Upload alle bestanden naar je webserver
2. Zorg dat de `assets/` folder structuur behouden blijft:
   ```
   assets/
   ├── css/
   │   └── style.css
   └── js/
       └── app.js
   ```

### Stap 2: Permissies Instellen
```bash
chmod 755 *.php
chmod -R 755 assets/
mkdir demo_data
chmod 777 demo_data
```

### Stap 3: Test de Installatie
1. Ga naar: `demo_index.php`
2. Login met: `admin@sensationstogo.nl` / `admin123`

## 🧪 TEST ACCOUNTS

### Admin (Volledige rechten):
- Email: `admin@sensationstogo.nl`
- Wachtwoord: `admin123`
- Functie: Kan alle wachtwoorden wijzigen

### Employees (Demo accounts):
- `jan@sensationstogo.nl` / `demo123` (🚴‍♂️ Bezorger Fiets)
- `lisa@sensationstogo.nl` / `demo123` (🚗 Bezorger Auto)
- `mohammed@sensationstogo.nl` / `demo123` (👨‍🍳 Keuken)
- `sarah@sensationstogo.nl` / `demo123` (👥 Balie)

## 🔧 SERVER VEREISTEN

### Minimale Vereisten:
- PHP 7.4 of hoger
- Schrijfrechten in web directory
- Geen database nodig! (File-based storage)

### Aanbevolen:
- PHP 8.0+
- HTTPS enabled
- Moderne browser ondersteuning

## 🎯 HOOFDFUNCTIONALITEIT TESTEN

### Admin Wachtwoord Beheer:
1. Login als admin
2. Ga naar "👥 Gebruikers"
3. Klik "🔑 Wachtwoord" bij elke gebruiker
4. Test wachtwoord suggesties
5. Wijzig wachtwoorden en zie feedback

## 📱 FEATURES OVERZICHT

### ✅ Nieuwe Enhanced Features v2.0:
- 🔑 Admin wachtwoord beheer
- 🚴‍♂️ Employee functions (Bezorger, Keuken, Balie)
- 📊 Live dashboard met statistieken
- 📱 Mobile responsive design
- 🎨 Modern UI/UX met animaties
- ⚡ File-based database (geen MySQL setup)

## 🚨 TROUBLESHOOTING

### Probleem: Lege pagina
- Controleer PHP error logs
- Zorg dat alle bestanden geüpload zijn
- Check file permissies

### Probleem: Login werkt niet
- Controleer dat `demo_data/` folder schrijfbaar is
- Test met admin account eerst

### Probleem: Styling ontbreekt
- Controleer dat `assets/css/style.css` bestaat
- Zorg dat de folder structuur correct is

## 📞 SUPPORT

Voor vragen of problemen:
1. Controleer eerst de troubleshooting sectie
2. Test met admin account: admin@sensationstogo.nl / admin123
3. Controleer browser console voor JavaScript errors

## 🎉 SUCCESS!

Als alles werkt zie je:
- ✅ Login pagina met demo accounts
- ✅ Dashboard met live statistieken
- ✅ Gebruikersbeheer met wachtwoord wijzig functie
- ✅ Success berichten bij wachtwoord wijzigingen

**De Enhanced v2.0 is klaar voor gebruik!**