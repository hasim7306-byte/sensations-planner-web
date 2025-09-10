<?php if (isset($login_error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
<?php endif; ?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">S</div>
            <h2>Sensations To Go Planner</h2>
            <p>Enhanced v2.0 - Employee Functions</p>
            <div class="version-info">
                <span class="new-badge">ENHANCED</span>
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
            <h4>🧪 Demo Accounts</h4>
            <div class="demo-grid">
                <div class="demo-account">
                    <strong>Admin:</strong><br>
                    admin@sensationstogo.nl<br>
                    <small>Alle rechten + wachtwoord beheer</small>
                </div>
                <div class="demo-account">
                    <strong>Employees:</strong><br>
                    jan@sensationstogo.nl (Bezorger Fiets)<br>
                    lisa@sensationstogo.nl (Bezorger Auto)<br>
                    <small>Wachtwoord: demo123</small>
                </div>
            </div>
        </div>
        
        <div class="enhanced-features">
            <h4>✨ Enhanced Features v2.0</h4>
            <ul>
                <li>🔑 Admin wachtwoord beheer</li>
                <li>🚴‍♂️ Employee functions (Bezorger, Keuken, Balie)</li>
                <li>📅 Function-based scheduling</li>
                <li>⏰ Geïntegreerde tijdregistratie</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280;">
            Contact uw administrator voor toegang
        </div>
    </div>
</div>