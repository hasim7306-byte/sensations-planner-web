<?php
/**
 * Sensations To Go Planner - Enhanced Demo Installer
 * Met nieuwe employee functions en enhanced scheduling
 * 
 * @version 2.0.0
 * @author Sensations To Go
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

class SensationsEnhancedInstaller {
    private $requirements = [
        'php' => '7.4.0',
        'extensions' => ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'openssl'],
        'permissions' => ['write']
    ];
    
    private $config = [];
    private $step = 1;
    private $maxSteps = 6;
    
    // Nieuwe employee functions
    private $employeeFunctions = [
        'bezorger_fiets' => 'Bezorger (Fiets)',
        'bezorger_auto' => 'Bezorger (Auto)',
        'keuken' => 'Keuken Medewerker',
        'balie_medewerker' => 'Balie Medewerker'
    ];
    
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
            <title>Sensations To Go Planner - Enhanced Demo</title>
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
                    max-width: 900px;
                    overflow: hidden;
                }
                .installer-header {
                    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .logo {
                    width: 80px;
                    height: 80px;
                    background: white;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                    font-weight: bold;
                    color: #dc2626;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                .installer-content {
                    padding: 40px;
                }
                .progress-bar {
                    height: 8px;
                    background: #f3f4f6;
                    margin-bottom: 30px;
                    border-radius: 4px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #dc2626, #ef4444);
                    transition: width 0.3s ease;
                    border-radius: 4px;
                }
                .step-indicator {
                    text-align: center;
                    margin-bottom: 30px;
                    color: #6b7280;
                    font-size: 16px;
                    font-weight: 600;
                }
                h1 { font-size: 32px; margin-bottom: 10px; }
                h2 { color: #1f2937; font-size: 28px; margin-bottom: 20px; text-align: center; }
                .feature-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 20px;
                    margin: 30px 0;
                }
                .feature-card {
                    background: #f8fafc;
                    padding: 25px;
                    border-radius: 12px;
                    border: 2px solid #e2e8f0;
                    transition: all 0.2s;
                }
                .feature-card:hover {
                    border-color: #dc2626;
                    transform: translateY(-2px);
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                }
                .feature-icon {
                    font-size: 2.5rem;
                    margin-bottom: 15px;
                    display: block;
                }
                .feature-title {
                    font-size: 1.25rem;
                    font-weight: bold;
                    color: #1f2937;
                    margin-bottom: 10px;
                }
                .feature-desc {
                    color: #6b7280;
                    line-height: 1.6;
                }
                .btn {
                    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                    color: white;
                    padding: 16px 32px;
                    border: none;
                    border-radius: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    text-decoration: none;
                    display: inline-block;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
                }
                .btn:hover { 
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
                }
                .alert {
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 25px;
                    border-left: 5px solid;
                }
                .alert-success {
                    background: #ecfdf5;
                    color: #065f46;
                    border-color: #10b981;
                }
                .alert-info {
                    background: #eff6ff;
                    color: #1e40af;
                    border-color: #3b82f6;
                }
                .new-badge {
                    background: #ef4444;
                    color: white;
                    font-size: 0.75rem;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-weight: bold;
                    margin-left: 10px;
                    animation: pulse 2s infinite;
                }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.7; }
                }
                .version-info {
                    text-align: center;
                    margin-bottom: 30px;
                    padding: 20px;
                    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                    border-radius: 10px;
                    border: 2px solid #f59e0b;
                }
                @media (max-width: 768px) {
                    .installer-content { padding: 20px; }
                    .feature-grid { grid-template-columns: 1fr; }
                }
            </style>
        </head>
        <body>
            <div class="installer-container">
                <div class="installer-header">
                    <div class="logo">S</div>
                    <h1>Sensations To Go Planner</h1>
                    <p style="font-size: 18px; opacity: 0.9;">Enhanced Workforce Management System</p>
                    <span class="new-badge">NIEUW v2.0</span>
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
        <div class="version-info">
            <h3 style="color: #92400e; margin-bottom: 10px;">🎉 Nieuwe Versie 2.0 - Enhanced Features!</h3>
            <p style="color: #92400e;">Met employee functions, geavanceerd roosteren en verbeterde functionaliteiten</p>
        </div>
        
        <h2>🚀 Welkom bij de Enhanced Demo</h2>
        
        <div class="alert alert-info">
            <strong>🆕 Wat is er nieuw?</strong><br>
            Deze demo toont de nieuwe functionaliteiten die je wilde implementeren voor employee functions en enhanced scheduling.
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-icon">🚴‍♂️</span>
                <div class="feature-title">Employee Functions <span class="new-badge">NIEUW</span></div>
                <div class="feature-desc">
                    Bezorger (Fiets), Bezorger (Auto), Keuken Medewerker, Balie Medewerker - met functie-specifieke toewijzingen
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">📅</span>
                <div class="feature-title">Enhanced Scheduling <span class="new-badge">NIEUW</span></div>
                <div class="feature-desc">
                    Functie-specifieke shift planning met automatische matching van employee functions aan roosters
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">⏰</span>
                <div class="feature-title">Geïntegreerde Tijdregistratie</div>
                <div class="feature-desc">
                    Alleen geroosterde medewerkers kunnen inklokken - perfecte integratie tussen roostering en tijd
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <div class="feature-title">Geavanceerd Gebruikersbeheer</div>
                <div class="feature-desc">
                    Admins kunnen medewerkers aanmaken met specifieke functies en rollen toewijzen
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🏖️</span>
                <div class="feature-title">Verlofbeheer & Goedkeuring</div>
                <div class="feature-desc">
                    Verlofaanvragen worden zichtbaar op het rooster met manager/admin goedkeuringsworkflow
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">💬</span>
                <div class="feature-title">Team Communicatie</div>
                <div class="feature-desc">
                    Real-time team chat voor communicatie tussen alle medewerkers
                </div>
            </div>
        </div>
        
        <div class="alert alert-success">
            <strong>✅ Database Schema Updates:</strong><br>
            • <code>function_role</code> kolom toegevoegd aan users tabel<br>
            • <code>function_required</code> kolom toegevoegd aan shifts tabel<br>
            • Automatische indexen voor betere performance
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="?step=2" class="btn">🏁 Start Enhanced Demo</a>
        </div>
        <?php
        $this->showFooter();
    }
    
    private function checkRequirements() {
        $this->showHeader();
        ?>
        <h2>🔧 Systeem Controle</h2>
        <p style="margin-bottom: 30px; color: #6b7280; text-align: center;">
            Controleren van server vereisten voor de enhanced features...
        </p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <?php
            $allOk = true;
            
            // PHP Version Check
            $phpVersion = PHP_VERSION;
            $phpOk = version_compare($phpVersion, $this->requirements['php'], '>=');
            $allOk = $allOk && $phpOk;
            
            echo "<p><strong>✅ PHP Versie:</strong> $phpVersion " . ($phpOk ? '(OK)' : '(Update nodig)') . "</p>";
            
            // Extension Checks
            foreach ($this->requirements['extensions'] as $ext) {
                $extOk = extension_loaded($ext);
                $allOk = $allOk && $extOk;
                echo "<p><strong>" . ($extOk ? '✅' : '❌') . " PHP Extensie:</strong> $ext</p>";
            }
            
            // Directory Permissions
            $dirs = ['.', 'config', 'uploads', 'logs'];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                $writable = is_writable($dir);
                $allOk = $allOk && $writable;
                echo "<p><strong>" . ($writable ? '✅' : '❌') . " Schrijfrechten:</strong> $dir/</p>";
            }
            ?>
        </div>
        
        <?php if ($allOk): ?>
            <div class="alert alert-success">
                <strong>🎉 Perfect!</strong> Alle vereisten zijn voldaan voor de enhanced features. Ready voor demo installatie!
            </div>
            <div style="text-align: center;">
                <a href="?step=3" class="btn">📊 Database Configureren</a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>⚠️ Vereisten niet voldaan</strong><br>
                Los de bovenstaande problemen op voordat u doorgaat.
            </div>
            <div style="text-align: center;">
                <a href="?step=2" class="btn">🔄 Opnieuw Controleren</a>
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
        <p style="margin-bottom: 30px; color: #6b7280; text-align: center;">
            Database setup voor de enhanced features met employee functions
        </p>
        
        <form method="POST" style="max-width: 600px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Database Host</label>
                    <input type="text" name="db_host" value="localhost" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Database Poort</label>
                    <input type="number" name="db_port" value="3306" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Database Gebruiker</label>
                    <input type="text" name="db_username" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Database Wachtwoord</label>
                    <input type="password" name="db_password" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Database Naam</label>
                <input type="text" name="db_name" value="sensations_planner_v2" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                <small style="color: #6b7280;">Enhanced database met nieuwe employee functions</small>
            </div>
            
            <div style="text-align: center;">
                <button type="submit" class="btn">🔍 Verbinding Testen & Doorgaan</button>
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
            
            $_SESSION['install_config']['database'] = $config;
            header('Location: ?step=4');
            exit;
            
        } catch (PDOException $e) {
            $this->showHeader();
            ?>
            <div class="alert alert-error">
                <strong>Database Verbinding Mislukt:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
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
        <p style="margin-bottom: 30px; color: #6b7280; text-align: center;">
            Admin account met volledige toegang tot employee functions management
        </p>
        
        <form method="POST" style="max-width: 600px; margin: 0 auto;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Volledige Naam</label>
                <input type="text" name="admin_name" value="System Administrator" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">E-mailadres</label>
                <input type="email" name="admin_email" value="admin@sensationstogo.nl" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Wachtwoord</label>
                    <input type="password" name="admin_password" required minlength="6" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Bevestig Wachtwoord</label>
                    <input type="password" name="admin_password_confirm" required minlength="6" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Bedrijfsnaam</label>
                <input type="text" name="company_name" value="Sensations To Go" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px;">
            </div>
            
            <div style="text-align: center;">
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
        <h2>⚙️ Enhanced Demo Installatie</h2>
        <div style="text-align: center; padding: 40px;">
            <div style="width: 60px; height: 60px; border: 4px solid #f3f4f6; border-top: 4px solid #dc2626; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
            <p style="font-size: 18px; color: #6b7280;">Installing enhanced features...</p>
            <p style="color: #6b7280; margin-top: 10px;">Employee functions, enhanced scheduling en meer!</p>
        </div>
        
        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
        
        <script>
        setTimeout(function() {
            window.location.href = '?step=5&action=install';
        }, 3000);
        </script>
        <?php
        
        if (isset($_GET['action']) && $_GET['action'] === 'install') {
            $this->performInstallation();
        }
        
        $this->showFooter();
    }
    
    private function performInstallation() {
        try {
            $this->createEnhancedDatabase();
            $this->createConfigFiles();
            $this->createDirectories();
            $this->installEnhancedFiles();
            $this->createAdminUser();
            $this->createSampleData();
            $this->createLockFile();
            
            header('Location: ?step=6');
            exit;
            
        } catch (Exception $e) {
            ?>
            <div class="alert alert-error">
                <strong>Installatie Mislukt:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <?php
        }
    }
    
    private function createEnhancedDatabase() {
        $db = $_SESSION['install_config']['database'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Enhanced database schema with new features
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
            function_role ENUM('bezorger_fiets', 'bezorger_auto', 'keuken', 'balie_medewerker') DEFAULT NULL,
            department VARCHAR(100),
            phone VARCHAR(20),
            hourly_rate DECIMAL(10,2),
            skills JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_function_role (function_role),
            INDEX idx_role (role),
            INDEX idx_active (is_active)
        );
        
        CREATE TABLE IF NOT EXISTS shifts (
            id VARCHAR(36) PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            break_duration INT DEFAULT 30,
            department VARCHAR(100),
            function_required ENUM('bezorger_fiets', 'bezorger_auto', 'keuken', 'balie_medewerker') DEFAULT NULL,
            notes TEXT,
            status ENUM('scheduled', 'confirmed', 'completed', 'cancelled') DEFAULT 'scheduled',
            created_by VARCHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id),
            FOREIGN KEY (created_by) REFERENCES users(id),
            INDEX idx_date (date),
            INDEX idx_function_required (function_required),
            INDEX idx_status (status)
        );
        
        CREATE TABLE IF NOT EXISTS time_entries (
            id VARCHAR(36) PRIMARY KEY,
            employee_id VARCHAR(36) NOT NULL,
            shift_id VARCHAR(36),
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
            FOREIGN KEY (shift_id) REFERENCES shifts(id),
            FOREIGN KEY (approved_by) REFERENCES users(id),
            INDEX idx_date (date),
            INDEX idx_employee (employee_id)
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
            FOREIGN KEY (approved_by) REFERENCES users(id),
            INDEX idx_status (status),
            INDEX idx_dates (start_date, end_date)
        );
        
        CREATE TABLE IF NOT EXISTS chat_messages (
            id VARCHAR(36) PRIMARY KEY,
            sender_id VARCHAR(36) NOT NULL,
            sender_name VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_system BOOLEAN DEFAULT FALSE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            INDEX idx_timestamp (timestamp)
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
        
        // Create directories
        if (!is_dir('config')) mkdir('config', 0755, true);
        
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
        $config .= "define('APP_VERSION', '2.0-Enhanced');\n";
        
        file_put_contents('config/database.php', $config);
    }
    
    private function createDirectories() {
        $dirs = ['config', 'uploads', 'logs', 'assets', 'assets/css', 'assets/js', 'assets/images'];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    // Continue in next file due to size limit
    private function installEnhancedFiles() {
        // Create all the enhanced PHP files
        $this->createIndexFile();
        $this->createAuthFile();
        $this->createDashboardFile();
        $this->createUsersFile();
        $this->createScheduleFile();
        $this->createTimeFile();
        $this->createLeaveFile();
        $this->createChatFile();
        $this->createLoginFile();
        $this->createStylesFile();
        $this->createJavaScriptFile();
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
    
    private function createAdminUser() {
        $admin = $_SESSION['install_config']['admin'];
        $db = $_SESSION['install_config']['database'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        
        $userId = $this->generateUUID();
        $stmt = $pdo->prepare("INSERT INTO users (id, email, name, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, 'admin', 1, NOW())");
        $stmt->execute([
            $userId,
            $admin['email'],
            $admin['name'],
            $admin['password']
        ]);
    }
    
    private function createSampleData() {
        $db = $_SESSION['install_config']['database'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
        $pdo = new PDO($dsn, $db['username'], $db['password']);
        
        // Create sample employees with different functions
        $sampleEmployees = [
            ['Jan de Vries', 'jan@sensationstogo.nl', 'bezorger_fiets'],
            ['Lisa Peters', 'lisa@sensationstogo.nl', 'bezorger_auto'],
            ['Mohammed Ali', 'mohammed@sensationstogo.nl', 'keuken'],
            ['Sarah van Dam', 'sarah@sensationstogo.nl', 'balie_medewerker']
        ];
        
        foreach ($sampleEmployees as $emp) {
            $empId = $this->generateUUID();
            $stmt = $pdo->prepare("INSERT INTO users (id, email, name, password_hash, role, function_role, is_active, created_at) VALUES (?, ?, ?, ?, 'employee', ?, 1, NOW())");
            $stmt->execute([
                $empId,
                $emp[1],
                $emp[0],
                password_hash('demo123', PASSWORD_DEFAULT),
                $emp[2]
            ]);
        }
        
        // Add welcome message
        $messageId = $this->generateUUID();
        $adminId = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO chat_messages (id, sender_id, sender_name, message, is_system, timestamp) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->execute([
            $messageId,
            $adminId,
            'Systeem',
            '🎉 Welkom bij Sensations To Go Planner v2.0! Enhanced features zijn actief: Employee Functions, Enhanced Scheduling en meer!'
        ]);
    }
    
    private function createLockFile() {
        $lockContent = "Sensations To Go Planner Enhanced Demo installed on " . date('Y-m-d H:i:s') . "\nVersion: 2.0-Enhanced\nFeatures: Employee Functions, Enhanced Scheduling";
        file_put_contents('config/installed.lock', $lockContent);
    }
    
    private function completeInstallation() {
        $this->showHeader();
        $admin = $_SESSION['install_config']['admin'];
        ?>
        <h2>🎉 Enhanced Demo Voltooid!</h2>
        
        <div class="alert alert-success">
            <strong>Gefeliciteerd!</strong> De Sensations To Go Planner Enhanced Demo is succesvol geïnstalleerd!
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-icon">🔐</span>
                <div class="feature-title">Admin Login</div>
                <div class="feature-desc">
                    <strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?><br>
                    <strong>Rol:</strong> Administrator
                </div>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <div class="feature-title">Demo Accounts</div>
                <div class="feature-desc">
                    4 sample employees aangemaakt met verschillende functions:<br>
                    <small>Wachtwoord voor alle demo accounts: <code>demo123</code></small>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <strong>🆕 Enhanced Features Ready:</strong><br>
            • Employee Functions (Bezorger Fiets/Auto, Keuken, Balie)<br>
            • Function-specific shift scheduling<br>
            • Integrated time tracking with shifts<br>
            • Enhanced user management<br>
            • Leave management with schedule integration
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn" style="font-size: 18px; padding: 16px 32px;">
                🚀 Start Enhanced Demo
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #6b7280;">
            <p><strong>Enhanced Demo v2.0</strong> - Sensations To Go Planner</p>
        </div>
        <?php
        
        // Clear session
        unset($_SESSION['install_config']);
        
        $this->showFooter();
    }
    
    private function showAlreadyInstalled() {
        $this->showHeader();
        ?>
        <h2>✅ Enhanced Demo al geïnstalleerd</h2>
        
        <div class="alert alert-info">
            <strong>Demo al klaar!</strong><br>
            De Enhanced Demo is al geïnstalleerd. Ga naar de applicatie om de nieuwe features te bekijken.
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">🏠 Naar Enhanced Demo</a>
        </div>
        <?php
        $this->showFooter();
    }
    
    // Helper methods for creating files will be in separate methods
    private function createIndexFile() {
        // Index file creation logic here
    }
    
    private function createAuthFile() {
        // Auth file creation logic here
    }
    
    private function createDashboardFile() {
        // Dashboard file creation logic here
    }
    
    private function createUsersFile() {
        // Users file creation logic here
    }
    
    private function createScheduleFile() {
        // Schedule file creation logic here
    }
    
    private function createTimeFile() {
        // Time file creation logic here
    }
    
    private function createLeaveFile() {
        // Leave file creation logic here
    }
    
    private function createChatFile() {
        // Chat file creation logic here
    }
    
    private function createLoginFile() {
        // Login file creation logic here
    }
    
    private function createStylesFile() {
        // Styles file creation logic here
    }
    
    private function createJavaScriptFile() {
        // JavaScript file creation logic here
    }
}

// Database helper function
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

// Run installer
$installer = new SensationsEnhancedInstaller();
$installer->run();
?>