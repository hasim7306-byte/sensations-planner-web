<?php
/**
 * Sensations To Go Planner - Web Installer
 * Automatic installation script for web servers
 * 
 * @version 1.0.0
 * @author Sensations To Go
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

class SensationsInstaller {
    private $requirements = [
        'php' => '7.4.0',
        'extensions' => ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'openssl', 'zip'],
        'functions' => ['exec', 'shell_exec', 'file_get_contents', 'file_put_contents'],
        'permissions' => ['write']
    ];
    
    private $config = [];
    private $step = 1;
    private $maxSteps = 6;
    
    public function __construct() {
        $this->step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
        $this->config = isset($_SESSION['install_config']) ? $_SESSION['install_config'] : [];
    }
    
    public function run() {
        if (file_exists('config/installed.lock')) {
            $this->showAlreadyInstalled();
            return;
        }
        
        switch ($this->step) {
            case 1:
                $this->showWelcome();
                break;
            case 2:
                $this->checkRequirements();
                break;
            case 3:
                $this->configurDatabase();
                break;
            case 4:
                $this->setupAdmin();
                break;
            case 5:
                $this->installSystem();
                break;
            case 6:
                $this->completeInstallation();
                break;
            default:
                $this->showWelcome();
        }
    }
    
    private function showHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="nl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sensations To Go Planner - Installatie</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .installer-container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
                    width: 100%;
                    max-width: 800px;
                    overflow: hidden;
                }
                .installer-header {
                    background: #dc2626;
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .logo {
                    width: 60px;
                    height: 60px;
                    background: white;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    font-weight: bold;
                    color: #dc2626;
                }
                .installer-content {
                    padding: 40px;
                }
                .progress-bar {
                    height: 6px;
                    background: #f3f4f6;
                    margin-bottom: 30px;
                    border-radius: 3px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    background: #dc2626;
                    transition: width 0.3s ease;
                }
                .step-indicator {
                    text-align: center;
                    margin-bottom: 30px;
                    color: #6b7280;
                    font-size: 14px;
                }
                h1 { font-size: 28px; margin-bottom: 10px; }
                h2 { color: #1f2937; font-size: 24px; margin-bottom: 20px; }
                .form-group {
                    margin-bottom: 20px;
                }
                label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #374151;
                }
                input, select, textarea {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.2s;
                }
                input:focus, select:focus, textarea:focus {
                    outline: none;
                    border-color: #dc2626;
                }
                .btn {
                    background: #dc2626;
                    color: white;
                    padding: 14px 28px;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.2s;
                    text-decoration: none;
                    display: inline-block;
                    text-align: center;
                }
                .btn:hover { background: #b91c1c; }
                .btn-secondary {
                    background: #6b7280;
                    margin-right: 10px;
                }
                .btn-secondary:hover { background: #4b5563; }
                .alert {
                    padding: 16px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .alert-success {
                    background: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                }
                .alert-error {
                    background: #fee2e2;
                    color: #991b1b;
                    border: 1px solid #fca5a5;
                }
                .alert-warning {
                    background: #fef3c7;
                    color: #92400e;
                    border: 1px solid #fde68a;
                }
                .requirements-list {
                    list-style: none;
                    padding: 0;
                }
                .requirements-list li {
                    padding: 10px 0;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .status-ok { color: #059669; font-weight: bold; }
                .status-error { color: #dc2626; font-weight: bold; }
                .loading {
                    text-align: center;
                    padding: 40px;
                }
                .spinner {
                    width: 40px;
                    height: 40px;
                    border: 4px solid #f3f4f6;
                    border-top: 4px solid #dc2626;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 20px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .two-column {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                }
                @media (max-width: 768px) {
                    .two-column { grid-template-columns: 1fr; }
                    .installer-content { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="installer-container">
                <div class="installer-header">
                    <div class="logo">S</div>
                    <h1>Sensations To Go Planner</h1>
                    <p>Workforce Management System</p>
                </div>
                <div class="installer-content">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($this->step / $this->maxSteps * 100); ?>%"></div>
                    </div>
                    <div class="step-indicator">
                        Stap <?php echo $this->step; ?> van <?php echo $this->maxSteps; ?>
                    </div>
        <?php
    }
    
    private function showFooter() {
        ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    private function showWelcome() {
        $this->showHeader();
        ?>
        <h2>🚀 Welkom bij de Installatie</h2>
        <p style="margin-bottom: 30px; font-size: 16px; line-height: 1.6; color: #6b7280;">
            Deze installer zal automatisch het Sensations To Go Planner systeem installeren op uw webserver. 
            Het complete workforce management systeem wordt opgezet inclusief database, gebruikersbeheer en alle functionaliteiten.
        </p>
        
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="color: #1f2937; margin-bottom: 15px;">📋 Wat wordt geïnstalleerd:</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 5px 0;"><strong>✅ Complete Web Applicatie</strong> - Dashboard, roostering, tijd registratie</li>
                <li style="padding: 5px 0;"><strong>✅ Database Setup</strong> - MySQL database met alle tabellen</li>
                <li style="padding: 5px 0;"><strong>✅ Admin Account</strong> - Gebruikersbeheer en systeem configuratie</li>
                <li style="padding: 5px 0;"><strong>✅ Mobile Ready</strong> - Volledig responsive voor alle apparaten</li>
                <li style="padding: 5px 0;"><strong>✅ Nederlandse Interface</strong> - Volledig in het Nederlands</li>
            </ul>
        </div>
        
        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="color: #991b1b; margin-bottom: 15px;">⚠️ Belangrijke Vereisten:</h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>PHP 7.4 of hoger</li>
                <li>MySQL 5.7 of hoger</li>
                <li>Minimaal 100MB vrije schijfruimte</li>
                <li>Schrijfrechten in de web directory</li>
            </ul>
        </div>
        
        <div style="text-align: center;">
            <a href="?step=2" class="btn">🏁 Start Installatie</a>
        </div>
        <?php
        $this->showFooter();
    }
    
    private function checkRequirements() {
        $this->showHeader();
        ?>
        <h2>🔧 Systeem Controle</h2>
        <p style="margin-bottom: 30px; color: #6b7280;">
            Controleren van server vereisten en configuratie...
        </p>
        
        <ul class="requirements-list">
        <?php
        $allOk = true;
        
        // PHP Version Check
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, $this->requirements['php'], '>=');
        $allOk = $allOk && $phpOk;
        ?>
            <li>
                <span>PHP Versie (minimaal <?php echo $this->requirements['php']; ?>)</span>
                <span class="<?php echo $phpOk ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $phpOk ? "✅ $phpVersion" : "❌ $phpVersion"; ?>
                </span>
            </li>
        <?php
        
        // Extension Checks
        foreach ($this->requirements['extensions'] as $ext) {
            $extOk = extension_loaded($ext);
            $allOk = $allOk && $extOk;
            ?>
            <li>
                <span>PHP Extensie: <?php echo $ext; ?></span>
                <span class="<?php echo $extOk ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $extOk ? '✅ Beschikbaar' : '❌ Niet gevonden'; ?>
                </span>
            </li>
            <?php
        }
        
        // Function Checks
        foreach ($this->requirements['functions'] as $func) {
            $funcOk = function_exists($func);
            $allOk = $allOk && $funcOk;
            ?>
            <li>
                <span>PHP Functie: <?php echo $func; ?></span>
                <span class="<?php echo $funcOk ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $funcOk ? '✅ Beschikbaar' : '❌ Uitgeschakeld'; ?>
                </span>
            </li>
            <?php
        }
        
        // Directory Permissions
        $dirs = ['.', 'config', 'uploads', 'logs'];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $writable = is_writable($dir);
            $allOk = $allOk && $writable;
            ?>
            <li>
                <span>Schrijfrechten: <?php echo $dir; ?>/</span>
                <span class="<?php echo $writable ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $writable ? '✅ Schrijfbaar' : '❌ Geen rechten'; ?>
                </span>
            </li>
            <?php
        }
        ?>
        </ul>
        
        <?php if ($allOk): ?>
            <div class="alert alert-success">
                <strong>🎉 Uitstekend!</strong> Alle vereisten zijn voldaan. U kunt doorgaan met de installatie.
            </div>
            <div style="text-align: center;">
                <a href="?step=3" class="btn">📊 Database Configureren</a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>⚠️ Vereisten niet voldaan</strong><br>
                Los de bovenstaande problemen op voordat u doorgaat met de installatie. 
                Neem contact op met uw hosting provider als u hulp nodig heeft.
            </div>
            <div style="text-align: center;">
                <a href="?step=2" class="btn-secondary btn">🔄 Opnieuw Controleren</a>
            </div>
        <?php endif; ?>
        
        <?php
        $this->showFooter();
    }
    
    private function configurDatabase() {
        if ($_POST) {
            $this->handleDatabaseConfig();
            return;
        }
        
        $this->showHeader();
        ?>
        <h2>🗄️ Database Configuratie</h2>
        <p style="margin-bottom: 30px; color: #6b7280;">
            Voer uw database gegevens in. Een nieuwe database wordt automatisch aangemaakt als deze niet bestaat.
        </p>
        
        <form method="POST">
            <div class="two-column">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label for="db_port">Database Poort</label>
                    <input type="number" id="db_port" name="db_port" value="3306" required>
                </div>
            </div>
            
            <div class="two-column">
                <div class="form-group">
                    <label for="db_username">Database Gebruiker</label>
                    <input type="text" id="db_username" name="db_username" required>
                </div>
                <div class="form-group">
                    <label for="db_password">Database Wachtwoord</label>
                    <input type="password" id="db_password" name="db_password">
                </div>
            </div>
            
            <div class="form-group">
                <label for="db_name">Database Naam</label>
                <input type="text" id="db_name" name="db_name" value="sensations_planner" required>
                <small style="color: #6b7280;">Database wordt automatisch aangemaakt als deze niet bestaat</small>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="?step=2" class="btn-secondary btn">← Terug</a>
                <button type="submit" class="btn">🔍 Verbinding Testen</button>
            </div>
        </form>
        <?php
        $this->showFooter();
    }
    
    private function handleDatabaseConfig() {
        $config = [
            'host' => $_POST['db_host'],
            'port' => $_POST['db_port'],
            'username' => $_POST['db_username'],
            'password' => $_POST['db_password'],
            'database' => $_POST['db_name']
        ];
        
        try {
            // Test database connection
            $dsn = "mysql:host={$config['host']};port={$config['port']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Test connection to the specific database
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            
            $_SESSION['install_config']['database'] = $config;
            header('Location: ?step=4');
            exit;
            
        } catch (PDOException $e) {
            $this->showHeader();
            ?>
            <h2>❌ Database Verbinding Mislukt</h2>
            <div class="alert alert-error">
                <strong>Fout:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <p style="margin-bottom: 20px;">Controleer uw database gegevens en probeer opnieuw.</p>
            <div style="text-align: center;">
                <a href="?step=3" class="btn">← Probeer Opnieuw</a>
            </div>
            <?php
            $this->showFooter();
        }
    }
    
    private function setupAdmin() {
        if ($_POST) {
            $this->handleAdminSetup();
            return;
        }
        
        $this->showHeader();
        ?>
        <h2>👤 Administrator Account</h2>
        <p style="margin-bottom: 30px; color: #6b7280;">
            Maak uw hoofdbeheerder account aan. Dit account heeft volledige toegang tot het systeem.
        </p>
        
        <form method="POST">
            <div class="form-group">
                <label for="admin_name">Volledige Naam</label>
                <input type="text" id="admin_name" name="admin_name" value="System Administrator" required>
            </div>
            
            <div class="form-group">
                <label for="admin_email">E-mailadres</label>
                <input type="email" id="admin_email" name="admin_email" value="admin@sensationstogo.nl" required>
            </div>
            
            <div class="two-column">
                <div class="form-group">
                    <label for="admin_password">Wachtwoord</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="admin_password_confirm">Bevestig Wachtwoord</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="6">
                </div>
            </div>
            
            <div class="form-group">
                <label for="company_name">Bedrijfsnaam</label>
                <input type="text" id="company_name" name="company_name" value="Sensations To Go" required>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="?step=3" class="btn-secondary btn">← Terug</a>
                <button type="submit" class="btn">👑 Account Aanmaken</button>
            </div>
        </form>
        <?php
        $this->showFooter();
    }
    
    private function handleAdminSetup() {
        if ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
            $this->showHeader();
            ?>
            <div class="alert alert-error">
                Wachtwoorden komen niet overeen. Probeer opnieuw.
            </div>
            <div style="text-align: center;">
                <a href="?step=4" class="btn">← Probeer Opnieuw</a>
            </div>
            <?php
            $this->showFooter();
            return;
        }
        
        $_SESSION['install_config']['admin'] = [
            'name' => $_POST['admin_name'],
            'email' => $_POST['admin_email'],
            'password' => password_hash($_POST['admin_password'], PASSWORD_DEFAULT),
            'company' => $_POST['company_name']
        ];
        
        header('Location: ?step=5');
        exit;
    }
    
    private function installSystem() {
        $this->showHeader();
        ?>
        <h2>⚙️ Installatie in Uitvoering</h2>
        <div class="loading">
            <div class="spinner"></div>
            <p>Het systeem wordt geïnstalleerd... Dit kan enkele minuten duren.</p>
        </div>
        
        <script>
        setTimeout(function() {
            window.location.href = '?step=5&action=install';
        }, 2000);
        </script>
        <?php
        
        if (isset($_GET['action']) && $_GET['action'] === 'install') {
            $this->performInstallation();
        }
        
        $this->showFooter();
    }
    
    private function performInstallation() {
        try {
            $this->createDatabase();
            $this->createConfigFiles();
            $this->createDirectories();
            $this->installFiles();
            $this->createAdminUser();
            $this->createLockFile();
            
            header('Location: ?step=6');
            exit;
            
        } catch (Exception $e) {
            ?>
            <div class="alert alert-error">
                <strong>Installatie Mislukt:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <div style="text-align: center;">
                <a href="?step=4" class="btn">← Probeer Opnieuw</a>
            </div>
            <?php
        }
    }
    
    private function createDatabase() {
        $db = $_SESSION['install_config']['database'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
            department VARCHAR(100),
            phone VARCHAR(20),
            hourly_rate DECIMAL(10,2),
            skills JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS shifts (
            id VARCHAR(36) PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            break_duration INT DEFAULT 30,
            department VARCHAR(100),
            notes TEXT,
            status ENUM('scheduled', 'confirmed', 'completed', 'cancelled') DEFAULT 'scheduled',
            created_by VARCHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS time_entries (
            id VARCHAR(36) PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            date DATE NOT NULL,
            clock_in DATETIME,
            clock_out DATETIME,
            break_start DATETIME,
            break_end DATETIME,
            total_hours DECIMAL(5,2),
            is_approved BOOLEAN DEFAULT FALSE,
            approved_by VARCHAR(36),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id),
            FOREIGN KEY (approved_by) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS leave_requests (
            id VARCHAR(36) PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            leave_type ENUM('vacation', 'sick', 'personal', 'maternity', 'other') NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            approved_by VARCHAR(36),
            approved_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id),
            FOREIGN KEY (approved_by) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS chat_messages (
            id VARCHAR(36) PRIMARY KEY,
            sender_id VARCHAR(36) NOT NULL,
            sender_name VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_system BOOLEAN DEFAULT FALSE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id)
        );
        
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        
        $pdo->exec($sql);
    }
    
    private function createConfigFiles() {
        $db = $_SESSION['install_config']['database'];
        $admin = $_SESSION['install_config']['admin'];
        
        // Create PHP config
        $config = "<?php\n";
        $config .= "define('DB_HOST', '{$db['host']}');\n";
        $config .= "define('DB_PORT', '{$db['port']}');\n";
        $config .= "define('DB_NAME', '{$db['database']}');\n";
        $config .= "define('DB_USER', '{$db['username']}');\n";
        $config .= "define('DB_PASS', '{$db['password']}');\n";
        $config .= "define('COMPANY_NAME', '{$admin['company']}');\n";
        $config .= "define('APP_URL', 'http" . (isset($_SERVER['HTTPS']) ? 's' : '') . "://{$_SERVER['HTTP_HOST']}');\n";
        $config .= "define('SECRET_KEY', '" . bin2hex(random_bytes(32)) . "');\n";
        
        file_put_contents('config/database.php', $config);
        
        // Create .htaccess for pretty URLs
        $htaccess = "RewriteEngine On\n";
        $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
        $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
        $htaccess .= "RewriteRule ^(.*)$ index.php [QSA,L]\n";
        
        file_put_contents('.htaccess', $htaccess);
    }
    
    private function createDirectories() {
        $dirs = ['config', 'uploads', 'logs', 'assets', 'assets/css', 'assets/js', 'assets/images'];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    private function installFiles() {
        // Create main application files
        $this->createIndexFile();
        $this->createAuthFile();
        $this->createDashboardFile();
        $this->createAPIFile();
        $this->createStylesFile();
        $this->createJavaScriptFile();
    }
    
    private function createIndexFile() {
        $content = '<?php
session_start();
require_once "config/database.php";

// Simple routing
$page = $_GET["page"] ?? "dashboard";

if (!isset($_SESSION["user_id"]) && $page !== "login") {
    $page = "login";
}

include "auth.php";

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensations To Go Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🔴</text></svg>">
</head>
<body>
    <div id="app">
        <?php if (isset($_SESSION["user_id"])): ?>
            <nav class="navbar">
                <div class="nav-brand">
                    <span class="logo">S</span>
                    <span class="brand-text">Sensations To Go</span>
                </div>
                <div class="nav-menu">
                    <a href="?page=dashboard" class="nav-link">Dashboard</a>
                    <a href="?page=schedule" class="nav-link">Roosters</a>
                    <a href="?page=time" class="nav-link">Tijd</a>
                    <a href="?page=leave" class="nav-link">Verlof</a>
                    <a href="?page=chat" class="nav-link">Chat</a>
                    <?php if ($_SESSION["user_role"] === "admin"): ?>
                        <a href="?page=users" class="nav-link">Gebruikers</a>
                    <?php endif; ?>
                    <a href="?action=logout" class="nav-link logout">Uitloggen</a>
                </div>
            </nav>
        <?php endif; ?>
        
        <main class="main-content">
            <?php
            switch ($page) {
                case "login":
                    include "login.php";
                    break;
                case "dashboard":
                    include "dashboard.php";
                    break;
                case "schedule":
                    include "schedule.php";
                    break;
                case "time":
                    include "time.php";
                    break;
                case "leave":
                    include "leave.php";
                    break;
                case "chat":
                    include "chat.php";
                    break;
                case "users":
                    if ($_SESSION["user_role"] === "admin") {
                        include "users.php";
                    } else {
                        echo "<h1>Geen toegang</h1>";
                    }
                    break;
                default:
                    include "dashboard.php";
            }
            ?>
        </main>
    </div>
    
    <script src="assets/js/app.js"></script>
</body>
</html>';
        
        file_put_contents('index.php', $content);
    }
    
    private function createAuthFile() {
        $content = '<?php
require_once "config/database.php";

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

// Handle login
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "login") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_email"] = $user["email"];
        $_SESSION["user_role"] = $user["role"];
        header("Location: ?page=dashboard");
        exit;
    } else {
        $login_error = "Onjuiste inloggegevens";
    }
}

// Handle logout
if (isset($_GET["action"]) && $_GET["action"] === "logout") {
    session_destroy();
    header("Location: ?page=login");
    exit;
}
?>';
        
        file_put_contents('auth.php', $content);
    }
    
    private function createDashboardFile() {
        $content = '<?php if (!isset($_SESSION["user_id"])) { header("Location: ?page=login"); exit; } ?>

<div class="dashboard">
    <h1>🏠 Dashboard</h1>
    <div class="welcome-card">
        <h2>Welkom, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</h2>
        <p>Rol: <span class="role-badge role-<?php echo $_SESSION["user_role"]; ?>"><?php echo ucfirst($_SESSION["user_role"]); ?></span></p>
    </div>
    
    <?php if ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "manager"): ?>
    <div class="stats-grid">
        <?php
        $pdo = getDB();
        
        // Get stats
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $todayShifts = $pdo->query("SELECT COUNT(*) FROM shifts WHERE date = CURDATE()")->fetchColumn();
        $pendingLeave = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = \"pending\"")->fetchColumn();
        $activeEntries = $pdo->query("SELECT COUNT(*) FROM time_entries WHERE date = CURDATE() AND clock_in IS NOT NULL AND clock_out IS NULL")->fetchColumn();
        ?>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Totaal Medewerkers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3><?php echo $todayShifts; ?></h3>
                <p>Diensten Vandaag</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3><?php echo $activeEntries; ?></h3>
                <p>Actief Ingeklokt</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $pendingLeave; ?></h3>
                <p>Verlof Aanvragen</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="actions-grid">
        <div class="action-card">
            <h3>⚡ Snelle Acties</h3>
            <div class="action-buttons">
                <a href="?page=time" class="action-btn primary">⏰ In/Uitklokken</a>
                <a href="?page=leave" class="action-btn success">🏖️ Verlof Aanvragen</a>
                <a href="?page=schedule" class="action-btn info">📅 Rooster Bekijken</a>
                <a href="?page=chat" class="action-btn warning">💬 Team Chat</a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>📈 Recente Activiteit</h3>
            <div class="activity-list">
                <div class="activity-item">
                    <span class="activity-dot success"></span>
                    <span>Systeem succesvol geïnstalleerd</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot info"></span>
                    <span>Account aangemaakt: <?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot warning"></span>
                    <span>Ready voor gebruik!</span>
                </div>
            </div>
        </div>
    </div>
</div>';
        
        file_put_contents('dashboard.php', $content);
    }
    
    private function createAPIFile() {
        // Create basic API files for time tracking, etc.
        $apiContent = '<?php
// API endpoints will be added here for AJAX calls
?>';
        file_put_contents('api.php', $apiContent);
    }
    
    private function createStylesFile() {
        $css = '/* Sensations To Go Planner Styles */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #f8fafc;
    color: #1a202c;
    line-height: 1.6;
}

.navbar {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-brand {
    display: flex;
    align-items: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.logo {
    width: 40px;
    height: 40px;
    background: white;
    color: #dc2626;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-weight: bold;
}

.nav-menu {
    display: flex;
    gap: 1rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: background 0.2s;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
}

.nav-link.logout {
    background: #991b1b;
}

.main-content {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 2rem;
}

.dashboard h1 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: #1a202c;
}

.welcome-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.role-badge {
    background: #dc2626;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #dc2626;
}

.stat-content p {
    color: #64748b;
    font-size: 0.875rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.action-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.action-card h3 {
    margin-bottom: 1.5rem;
    color: #1a202c;
}

.action-buttons {
    display: grid;
    gap: 1rem;
}

.action-btn {
    padding: 1rem;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    transition: transform 0.2s, box-shadow 0.2s;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.action-btn.primary { background: #3b82f6; color: white; }
.action-btn.success { background: #10b981; color: white; }
.action-btn.info { background: #8b5cf6; color: white; }
.action-btn.warning { background: #f59e0b; color: white; }

.activity-list {
    space-y: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.activity-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 1rem;
}

.activity-dot.success { background: #10b981; }
.activity-dot.info { background: #3b82f6; }
.activity-dot.warning { background: #f59e0b; }

/* Login Styles */
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
}

.login-card {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-logo {
    width: 80px;
    height: 80px;
    background: #dc2626;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    font-weight: bold;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #dc2626;
}

.login-btn {
    width: 100%;
    background: #dc2626;
    color: white;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.login-btn:hover {
    background: #b91c1c;
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border: 1px solid #fca5a5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        padding: 1rem;
    }
    
    .nav-menu {
        margin-top: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .main-content {
        padding: 0 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
}';
        
        file_put_contents('assets/css/style.css', $css);
    }
    
    private function createJavaScriptFile() {
        $js = '// Sensations To Go Planner JavaScript
document.addEventListener("DOMContentLoaded", function() {
    console.log("Sensations To Go Planner loaded successfully!");
    
    // Add smooth transitions
    document.body.style.opacity = "0";
    setTimeout(() => {
        document.body.style.transition = "opacity 0.3s ease";
        document.body.style.opacity = "1";
    }, 100);
    
    // Mobile menu toggle (if needed)
    const mobileToggle = document.querySelector(".mobile-toggle");
    const navMenu = document.querySelector(".nav-menu");
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener("click", () => {
            navMenu.classList.toggle("active");
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            const requiredFields = form.querySelectorAll("[required]");
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = "#dc2626";
                } else {
                    field.style.borderColor = "#e5e7eb";
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert("Vul alle verplichte velden in.");
            }
        });
    });
});';
        
        file_put_contents('assets/js/app.js', $js);
    }
    
    private function createAdminUser() {
        $admin = $_SESSION['install_config']['admin'];
        $pdo = getDB();
        
        $userId = $this->generateUUID();
        $stmt = $pdo->prepare("INSERT INTO users (id, email, name, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, 'admin', 1, NOW())");
        $stmt->execute([
            $userId,
            $admin['email'],
            $admin['name'],
            $admin['password']
        ]);
        
        // Add welcome message
        $messageId = $this->generateUUID();
        $stmt = $pdo->prepare("INSERT INTO chat_messages (id, sender_id, sender_name, message, is_system, timestamp) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->execute([
            $messageId,
            $userId,
            'Systeem',
            'Welkom bij Sensations To Go Planner! Het systeem is succesvol geïnstalleerd. 🎉'
        ]);
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    private function createLockFile() {
        $lockContent = "Sensations To Go Planner installed on " . date('Y-m-d H:i:s');
        file_put_contents('config/installed.lock', $lockContent);
    }
    
    private function completeInstallation() {
        $this->showHeader();
        $admin = $_SESSION['install_config']['admin'];
        ?>
        <h2>🎉 Installatie Voltooid!</h2>
        
        <div class="alert alert-success">
            <strong>Gefeliciteerd!</strong> Sensations To Go Planner is succesvol geïnstalleerd en klaar voor gebruik.
        </div>
        
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #1f2937; margin-bottom: 15px;">🔐 Inloggegevens:</h3>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
            <p><strong>Rol:</strong> Administrator</p>
            <p><strong>Bedrijf:</strong> <?php echo htmlspecialchars($admin['company']); ?></p>
        </div>
        
        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #991b1b; margin-bottom: 15px;">⚠️ Belangrijk:</h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li>Verwijder het install.php bestand voor de beveiliging</li>
                <li>Bewaar uw inloggegevens op een veilige plaats</li>
                <li>Maak regelmatig back-ups van uw database</li>
                <li>Update regelmatig naar nieuwe versies</li>
            </ul>
        </div>
        
        <div style="background: #f0f9ff; border: 1px solid #7dd3fc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #0c4a6e; margin-bottom: 15px;">🚀 Volgende Stappen:</h3>
            <ol style="list-style: decimal; margin-left: 20px;">
                <li>Log in als administrator</li>
                <li>Voeg medewerkers toe via het gebruikersbeheer</li>
                <li>Stel roosters en werkschema\'s in</li>
                <li>Train uw team in het gebruik van het systeem</li>
                <li>Begin met het beheren van uw workforce!</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn" style="font-size: 18px; padding: 16px 32px;">
                🏠 Naar Dashboard
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px;">
            <p>Bedankt voor het kiezen van Sensations To Go Planner!</p>
            <p>Voor support en updates, bezoek: <strong>sensationstogo.nl</strong></p>
        </div>
        <?php
        
        // Clear session
        unset($_SESSION['install_config']);
        
        $this->showFooter();
    }
    
    private function showAlreadyInstalled() {
        $this->showHeader();
        ?>
        <h2>✅ Reeds Geïnstalleerd</h2>
        
        <div class="alert alert-warning">
            <strong>Systeem al geïnstalleerd!</strong><br>
            Sensations To Go Planner is al geïnstalleerd op deze server. 
            Verwijder het config/installed.lock bestand als u opnieuw wilt installeren.
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">🏠 Naar Applicatie</a>
        </div>
        <?php
        $this->showFooter();
    }
}

// Helper function for database connection in other files
function getDB() {
    if (!file_exists('config/database.php')) {
        return null;
    }
    
    require_once 'config/database.php';
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

// Create additional required files
if (!file_exists('login.php')) {
    $loginContent = '<?php if (isset($login_error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
<?php endif; ?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">S</div>
            <h2>Sensations To Go Planner</h2>
            <p>Inloggen op uw account</p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Inloggen</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280;">
            Contact uw administrator voor toegang
        </div>
    </div>
</div>';
    
    file_put_contents('login.php', $loginContent);
}

// Run installer
$installer = new SensationsInstaller();
$installer->run();
?>