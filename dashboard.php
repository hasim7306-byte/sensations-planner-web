<?php if (!isset($_SESSION["user_id"])) { header("Location: ?page=login"); exit; } 

$pdo = getDB();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>🏠 Dashboard</h1>
        <div class="version-banner">
            <span class="version-badge-large">Enhanced v2.0</span>
            <span class="feature-highlight">Employee Functions & Password Management</span>
        </div>
    </div>
    
    <div class="welcome-card">
        <div class="welcome-content">
            <h2>Welkom, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</h2>
            <div class="user-details">
                <span class="role-badge role-<?php echo $_SESSION["user_role"]; ?>">
                    <?php echo ucfirst($_SESSION["user_role"]); ?>
                </span>
                <?php if ($_SESSION["user_function"]): ?>
                    <span class="function-badge">
                        <?php 
                        $functions = [
                            'bezorger_fiets' => '🚴‍♂️ Bezorger (Fiets)',
                            'bezorger_auto' => '🚗 Bezorger (Auto)',
                            'keuken' => '👨‍🍳 Keuken',
                            'balie_medewerker' => '👥 Balie'
                        ];
                        echo $functions[$_SESSION["user_function"]] ?? $_SESSION["user_function"];
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="welcome-time">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </div>
    </div>
    
    <?php if ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "manager"): ?>
    <div class="stats-grid">
        <?php
        // Get enhanced stats
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $todayShifts = $pdo->query("SELECT COUNT(*) FROM shifts WHERE date = CURDATE()")->fetchColumn();
        $pendingLeave = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
        $activeEntries = $pdo->query("SELECT COUNT(*) FROM time_entries WHERE date = CURDATE() AND clock_in IS NOT NULL AND clock_out IS NULL")->fetchColumn();
        
        // Get function breakdown
        $functionStats = $pdo->query("SELECT function_role, COUNT(*) as count FROM users WHERE is_active = 1 AND function_role IS NOT NULL GROUP BY function_role")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="stat-card primary">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Totaal Medewerkers</p>
                <div class="stat-detail">
                    <?php foreach ($functionStats as $func): ?>
                        <span class="function-stat">
                            <?php 
                            $functionNames = [
                                'bezorger_fiets' => '🚴‍♂️',
                                'bezorger_auto' => '🚗',
                                'keuken' => '👨‍🍳',
                                'balie_medewerker' => '👥'
                            ];
                            echo $functionNames[$func['function_role']] ?? $func['function_role']; 
                            ?>
                            <?php echo $func['count']; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3><?php echo $todayShifts; ?></h3>
                <p>Diensten Vandaag</p>
                <div class="stat-trend">
                    <?php 
                    $yesterdayShifts = $pdo->query("SELECT COUNT(*) FROM shifts WHERE date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();
                    $trend = $todayShifts - $yesterdayShifts;
                    ?>
                    <span class="trend <?php echo $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'same'); ?>">
                        <?php echo $trend > 0 ? "↗ +" . $trend : ($trend < 0 ? "↘ " . $trend : "→ 0"); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3><?php echo $activeEntries; ?></h3>
                <p>Actief Ingeklokt</p>
                <div class="stat-detail">
                    <small>Live medewerkers aan het werk</small>
                </div>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $pendingLeave; ?></h3>
                <p>Verlof Aanvragen</p>
                <div class="stat-detail">
                    <?php if ($pendingLeave > 0): ?>
                        <small class="urgent">Wacht op goedkeuring</small>
                    <?php else: ?>
                        <small>Alles afgehandeld</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="actions-grid">
        <div class="action-card primary">
            <h3>⚡ Snelle Acties</h3>
            <div class="action-buttons">
                <a href="?page=time" class="action-btn primary">
                    ⏰ In/Uitklokken
                    <small>Tijd registreren</small>
                </a>
                <a href="?page=leave" class="action-btn success">
                    🏖️ Verlof Aanvragen
                    <small>Vrij dag aanvragen</small>
                </a>
                <a href="?page=schedule" class="action-btn info">
                    📅 Rooster Bekijken
                    <small>Mijn planning</small>
                </a>
                <a href="?page=chat" class="action-btn warning">
                    💬 Team Chat
                    <small>Collega's bereiken</small>
                </a>
                <?php if ($_SESSION["user_role"] === "admin"): ?>
                    <a href="?page=users" class="action-btn danger">
                        👥 Gebruikers Beheer
                        <small>Wachtwoorden & functies</small>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="action-card">
            <h3>📈 Recente Activiteit</h3>
            <div class="activity-list">
                <?php
                // Get recent activities
                $recentActivities = [];
                
                // Recent shifts
                $stmt = $pdo->prepare("SELECT s.*, u.name FROM shifts s JOIN users u ON s.employee_id = u.id WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY s.created_at DESC LIMIT 5");
                $stmt->execute();
                $recentShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Recent leave requests  
                $stmt = $pdo->prepare("SELECT l.*, u.name FROM leave_requests l JOIN users u ON l.employee_id = u.id WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY l.created_at DESC LIMIT 3");
                $stmt->execute();
                $recentLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="activity-item">
                    <span class="activity-dot success"></span>
                    <span>Enhanced v2.0 features geactiveerd</span>
                    <small>Employee functions & wachtwoord beheer</small>
                </div>
                
                <?php foreach ($recentShifts as $shift): ?>
                    <div class="activity-item">
                        <span class="activity-dot info"></span>
                        <span>Nieuw rooster: <?php echo htmlspecialchars($shift['name']); ?></span>
                        <small><?php echo date('d-m H:i', strtotime($shift['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($recentLeave as $leave): ?>
                    <div class="activity-item">
                        <span class="activity-dot warning"></span>
                        <span>Verlof aanvraag: <?php echo htmlspecialchars($leave['name']); ?></span>
                        <small><?php echo date('d-m H:i', strtotime($leave['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($recentShifts) && empty($recentLeave)): ?>
                    <div class="activity-item">
                        <span class="activity-dot success"></span>
                        <span>Systeem klaar voor gebruik!</span>
                        <small>Start met roostering en tijdregistratie</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enhanced Features Showcase -->
        <div class="action-card enhanced">
            <h3>🆕 Enhanced Features v2.0</h3>
            <div class="enhanced-features-grid">
                <div class="feature-highlight">
                    <span class="feature-icon">🔑</span>
                    <strong>Wachtwoord Beheer</strong>
                    <p>Admins kunnen alle wachtwoorden wijzigen</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">🚴‍♂️</span>
                    <strong>Employee Functions</strong>
                    <p>Bezorger, Keuken, Balie specialisaties</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">📅</span>
                    <strong>Smart Scheduling</strong>
                    <p>Functie-specifieke dienst toewijzing</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">📊</span>
                    <strong>Enhanced Analytics</strong>
                    <p>Uitgebreide statistieken en trends</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update time display
function updateTime() {
    const now = new Date();
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('nl-NL', timeOptions);
    document.getElementById('currentDate').textContent = now.toLocaleDateString('nl-NL', dateOptions);
}

// Update time every second
setInterval(updateTime, 1000);
updateTime(); // Initial call
</script>