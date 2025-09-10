<?php if (isset($login_error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
<?php endif; ?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">S</div>
            <h2>Sensations To Go Planner</h2>
            <p>Enhanced v2.0 - LIVE DEMO</p>
            <div class="version-info">
                <span class="new-badge">WERKENDE DEMO</span>
            </div>
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
            
            <button type="submit" class="login-btn">🔐 Inloggen</button>
        </form>
        
        <div class="demo-accounts">
            <h4>🧪 Test Accounts - WERKENDE DEMO</h4>
            <div class="demo-grid">
                <div class="demo-account">
                    <strong>👑 Admin (Wachtwoord Beheer):</strong><br>
                    <code>admin@sensationstogo.nl</code><br>
                    <code>admin123</code><br>
                    <small>✅ Kan alle wachtwoorden wijzigen!</small>
                </div>
                <div class="demo-account">
                    <strong>👥 Employees:</strong><br>
                    <code>jan@sensationstogo.nl</code> (🚴‍♂️ Fiets)<br>
                    <code>lisa@sensationstogo.nl</code> (🚗 Auto)<br>
                    <code>mohammed@sensationstogo.nl</code> (👨‍🍳 Keuken)<br>
                    <code>sarah@sensationstogo.nl</code> (👥 Balie)<br>
                    <small>Wachtwoord: <code>demo123</code></small>
                </div>
            </div>
        </div>
        
        <div class="enhanced-features">
            <h4>✨ Live Demo Features</h4>
            <ul>
                <li>🔑 <strong>Admin wachtwoord beheer</strong> - Test het!</li>
                <li>🚴‍♂️ Employee functions (Bezorger, Keuken, Balie)</li>
                <li>📊 Enhanced dashboard met real-time stats</li>
                <li>👥 Geavanceerd gebruikersbeheer</li>
                <li>📱 Volledig responsive design</li>
                <li>⚡ Live werking zonder database setup!</li>
            </ul>
        </div>
        
        <div class="demo-instructions">
            <h4>🎯 Test het Wachtwoord Beheer:</h4>
            <ol style="text-align: left; font-size: 0.875rem; color: #6b7280;">
                <li>Log in als admin (admin@sensationstogo.nl / admin123)</li>
                <li>Ga naar "👥 Gebruikers"</li>
                <li>Klik op "🔑 Wachtwoord" bij elke gebruiker</li>
                <li>Test de wachtwoord suggesties</li>
                <li>Wijzig wachtwoorden en zie de feedback!</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280;">
            🎉 Volledig werkende demo - geen database setup nodig!
        </div>
    </div>
</div>

<style>
.demo-instructions {
    margin-top: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 12px;
    border: 2px solid #f59e0b;
}

.demo-instructions h4 {
    color: #92400e;
    margin-bottom: 1rem;
    text-align: center;
}

.demo-instructions ol {
    margin-left: 1rem;
}

.demo-instructions li {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}
</style>