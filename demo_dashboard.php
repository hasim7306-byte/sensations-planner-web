<?php if (!isset($_SESSION["user_id"])) { header("Location: ?page=login"); exit; } 

$db = getDemoDB();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>🏠 Dashboard</h1>
        <div class="version-banner">
            <span class="version-badge-large">LIVE DEMO v2.0</span>
            <span class="feature-highlight">Admin Wachtwoord Beheer & Employee Functions</span>
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
        // Get enhanced stats from demo database
        $totalUsers = count($db->getActiveUsers());
        $todayShifts = count($db->getTodayShifts());
        $pendingLeave = count($db->getPendingLeaveRequests());
        $functionStats = $db->getFunctionStats();
        ?>
        
        <div class="stat-card primary">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Totaal Medewerkers</p>
                <div class="stat-detail">
                    <?php foreach ($functionStats as $func => $count): ?>
                        <span class="function-stat">
                            <?php 
                            $functionNames = [
                                'bezorger_fiets' => '🚴‍♂️',
                                'bezorger_auto' => '🚗',
                                'keuken' => '👨‍🍳',
                                'balie_medewerker' => '👥'
                            ];
                            echo $functionNames[$func] ?? $func; 
                            ?>
                            <?php echo $count; ?>
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
                    <span class="trend up">↗ +1</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3>3</h3>
                <p>Actief Ingeklokt</p>
                <div class="stat-detail">
                    <small>Live demo data</small>
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
            <h3>⚡ Demo Acties</h3>
            <div class="action-buttons">
                <?php if ($_SESSION["user_role"] === "admin"): ?>
                    <a href="?page=users" class="action-btn danger">
                        🔑 Wachtwoord Beheer
                        <small>TEST DE NIEUWE FUNCTIE!</small>
                    </a>
                <?php endif; ?>
                <a href="?page=time" class="action-btn primary">
                    ⏰ In/Uitklokken
                    <small>Tijd registreren (demo)</small>
                </a>
                <a href="?page=leave" class="action-btn success">
                    🏖️ Verlof Aanvragen
                    <small>Vrij dag aanvragen (demo)</small>
                </a>
                <a href="?page=schedule" class="action-btn info">
                    📅 Rooster Bekijken
                    <small>Enhanced scheduling (demo)</small>
                </a>
                <a href="?page=chat" class="action-btn warning">
                    💬 Team Chat
                    <small>Collega's bereiken (demo)</small>
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>📈 Live Demo Activiteit</h3>
            <div class="activity-list">
                <div class="activity-item">
                    <span class="activity-dot success"></span>
                    <span>Enhanced v2.0 demo geladen</span>
                    <small>Nu actief</small>
                </div>
                
                <div class="activity-item">
                    <span class="activity-dot info"></span>
                    <span>Admin wachtwoord beheer beschikbaar</span>
                    <small>Test functie</small>
                </div>
                
                <div class="activity-item">
                    <span class="activity-dot warning"></span>
                    <span>Employee functions geactiveerd</span>
                    <small>4 functies beschikbaar</small>
                </div>
                
                <div class="activity-item">
                    <span class="activity-dot success"></span>
                    <span>Demo database geïnitialiseerd</span>
                    <small>5 gebruikers geladen</small>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Features Showcase -->
        <div class="action-card enhanced">
            <h3>🆕 Live Demo Features</h3>
            <div class="enhanced-features-grid">
                <div class="feature-highlight">
                    <span class="feature-icon">🔑</span>
                    <strong>Wachtwoord Beheer</strong>
                    <p>Admin kan alle wachtwoorden wijzigen - LIVE werkend!</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">🚴‍♂️</span>
                    <strong>Employee Functions</strong>
                    <p>Bezorger, Keuken, Balie rollen</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">📊</span>
                    <strong>Live Statistics</strong>
                    <p>Real-time dashboard updates</p>
                </div>
                <div class="feature-highlight">
                    <span class="feature-icon">⚡</span>
                    <strong>No Database Setup</strong>
                    <p>File-based demo zonder configuratie</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($_SESSION["user_role"] === "admin"): ?>
    <div class="demo-highlight">
        <h3>🎯 Admin Demo Instructies</h3>
        <div class="demo-steps">
            <div class="demo-step">
                <span class="step-number">1</span>
                <div class="step-content">
                    <strong>Ga naar Gebruikersbeheer</strong>
                    <p>Klik op "👥 Gebruikers" in het menu</p>
                </div>
            </div>
            <div class="demo-step">
                <span class="step-number">2</span>
                <div class="step-content">
                    <strong>Test Wachtwoord Wijziging</strong>
                    <p>Klik op "🔑 Wachtwoord" bij elke gebruiker</p>
                </div>
            </div>
            <div class="demo-step">
                <span class="step-number">3</span>
                <div class="step-content">
                    <strong>Probeer Suggesties</strong>
                    <p>Gebruik de voorgestelde wachtwoorden</p>
                </div>
            </div>
            <div class="demo-step">
                <span class="step-number">4</span>
                <div class="step-content">
                    <strong>Zie Live Feedback</strong>
                    <p>Success berichten en validatie</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.demo-highlight {
    background: linear-gradient(135deg, #fef7ff 0%, #fae8ff 100%);
    border: 2px solid #e879f9;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 2rem;
}

.demo-highlight h3 {
    text-align: center;
    margin-bottom: 2rem;
    color: #1f2937;
}

.demo-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.demo-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.step-number {
    background: #e879f9;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.step-content p {
    color: #6b7280;
    font-size: 0.875rem;
}
</style>

<script>
// Update time display
function updateTime() {
    const now = new Date();
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    const timeEl = document.getElementById('currentTime');
    const dateEl = document.getElementById('currentDate');
    
    if (timeEl && dateEl) {
        timeEl.textContent = now.toLocaleTimeString('nl-NL', timeOptions);
        dateEl.textContent = now.toLocaleDateString('nl-NL', dateOptions);
    }
}

// Update time every second
setInterval(updateTime, 1000);
updateTime(); // Initial call
</script>