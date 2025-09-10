<?php if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") { 
    header("Location: ?page=login"); 
    exit; 
} 

$db = getDemoDB();

// Get all users
$users = $db->getActiveUsers();

// Employee functions
$functions = [
    'bezorger_fiets' => 'Bezorger (Fiets)',
    'bezorger_auto' => 'Bezorger (Auto)',
    'keuken' => 'Keuken Medewerker',
    'balie_medewerker' => 'Balie Medewerker'
];

// Show messages
if (isset($_SESSION["success_message"])) {
    $success_message = $_SESSION["success_message"];
    unset($_SESSION["success_message"]);
}
if (isset($_SESSION["error_message"])) {
    $error_message = $_SESSION["error_message"];
    unset($_SESSION["error_message"]);
}
?>

<div class="users-management">
    <div class="page-header">
        <h1>👥 Gebruikersbeheer - LIVE DEMO</h1>
        <p class="page-subtitle">🔑 Test het admin wachtwoord beheer - WERKENDE FUNCTIE!</p>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <strong>✅ Success!</strong> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <strong>❌ Fout!</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Live Demo Instructions -->
    <div class="demo-instructions-banner">
        <h3>🎯 LIVE DEMO - Test het Wachtwoord Beheer!</h3>
        <p>Klik op de "🔑 Wachtwoord" knoppen hieronder om wachtwoorden te wijzigen. De functie werkt echt!</p>
    </div>

    <div class="management-grid">
        <!-- Create User Form -->
        <div class="management-card">
            <h3>➕ Nieuwe Gebruiker</h3>
            <form method="POST" class="user-form">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-group">
                    <label>Volledige Naam</label>
                    <input type="text" name="name" required placeholder="Bijv. Peter Janssen">
                </div>
                
                <div class="form-group">
                    <label>E-mailadres</label>
                    <input type="email" name="email" required placeholder="peter@sensationstogo.nl">
                </div>
                
                <div class="form-group">
                    <label>Wachtwoord</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimaal 6 karakters">
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role" required>
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Functie</label>
                    <select name="function_role">
                        <option value="">Geen specifieke functie</option>
                        <?php foreach ($functions as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">👤 Demo Gebruiker Aanmaken</button>
            </form>
        </div>

        <!-- Users List -->
        <div class="management-card users-list">
            <h3>📋 Alle Gebruikers (<?php echo count($users); ?>) - LIVE DATA</h3>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>E-mail</th>
                            <th>Rol</th>
                            <th>Functie</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                            <span class="badge badge-you">Jij</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['function_role']): ?>
                                        <span class="function-badge">
                                            <?php echo $functions[$user['function_role']] ?? $user['function_role']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Geen functie</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <button class="btn-small btn-warning demo-password-btn" onclick="showPasswordModal('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['name']); ?>')">
                                        🔑 Wachtwoord
                                    </button>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <a href="?page=users&delete_user=<?php echo $user['id']; ?>" 
                                           class="btn-small btn-danger" 
                                           onclick="return confirm('Weet je zeker dat je deze gebruiker wilt deactiveren?')">
                                            🗑️ Deactiveren
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enhanced Features Info -->
    <div class="info-card">
        <h3>🆕 LIVE DEMO Features v2.0</h3>
        <div class="features-grid">
            <div class="feature-item">
                <span class="feature-icon">🔑</span>
                <strong>Wachtwoord Beheer - WERKEND!</strong>
                <p>Admins kunnen wachtwoorden van alle gebruikers wijzigen - test het nu!</p>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🚴‍♂️</span>
                <strong>Employee Functions</strong>
                <p>Bezorger (Fiets/Auto), Keuken, Balie specifieke rollen</p>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📅</span>
                <strong>Function-based Scheduling</strong>
                <p>Shifts gekoppeld aan specifieke employee functions</p>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👥</span>
                <strong>Live User Management</strong>
                <p>Admin, Manager, Employee hierarchie met permissions</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Password Change Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔑 Wachtwoord Wijzigen - LIVE DEMO</h3>
            <span class="close" onclick="closePasswordModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="user_id" id="modal_user_id">
            
            <div class="modal-body">
                <div class="demo-notice">
                    <strong>🎉 Dit werkt echt!</strong> Je wijzigt nu het wachtwoord van een gebruiker.
                </div>
                
                <p>Wachtwoord wijzigen voor: <strong id="modal_user_name"></strong></p>
                
                <div class="form-group">
                    <label>Nieuw Wachtwoord</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Minimaal 6 karakters">
                </div>
                
                <div class="password-suggestions">
                    <h4>💡 Wachtwoord Suggesties - Klik om te gebruiken:</h4>
                    <button type="button" class="btn-suggestion" onclick="setPassword('Sensations2024!')">Sensations2024!</button>
                    <button type="button" class="btn-suggestion" onclick="setPassword('StG' + Math.floor(Math.random() * 1000))">StG + nummer</button>
                    <button type="button" class="btn-suggestion" onclick="setPassword('Werkrooster' + new Date().getFullYear())">Werkrooster + jaar</button>
                    <button type="button" class="btn-suggestion" onclick="setPassword('Demo123!')">Demo123!</button>
                </div>
                
                <div class="demo-tip">
                    <strong>💡 Demo Tip:</strong> Probeer de suggestie knoppen! Ze vullen automatisch het wachtwoord veld in.
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Annuleren</button>
                <button type="submit" class="btn btn-primary">🔄 Wachtwoord Wijzigen</button>
            </div>
        </form>
    </div>
</div>

<style>
.demo-instructions-banner {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.demo-instructions-banner h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.demo-instructions-banner p {
    font-size: 1.125rem;
    opacity: 0.9;
}

.demo-password-btn {
    animation: pulse 2s infinite;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3) !important;
}

.demo-notice {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: white;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 600;
}

.demo-tip {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    color: #92400e;
    padding: 1rem;
    border-radius: 10px;
    margin-top: 1rem;
    font-size: 0.875rem;
}

.btn-suggestion {
    background: white;
    border: 2px solid #e5e7eb;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
    font-weight: 600;
}

.btn-suggestion:hover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

.btn-suggestion:active {
    background: rgba(16, 185, 129, 0.2);
    transform: translateY(0);
}

@keyframes pulse {
    0%, 100% { 
        opacity: 1; 
        transform: scale(1);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }
    50% { 
        opacity: 0.8; 
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
    }
}

/* Enhanced mobile responsiveness */
@media (max-width: 768px) {
    .management-grid {
        grid-template-columns: 1fr;
    }
    
    .demo-instructions-banner {
        padding: 1.5rem;
    }
    
    .demo-instructions-banner h3 {
        font-size: 1.25rem;
    }
    
    .demo-instructions-banner p {
        font-size: 1rem;
    }
    
    .actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-small {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
function showPasswordModal(userId, userName) {
    document.getElementById('modal_user_id').value = userId;
    document.getElementById('modal_user_name').textContent = userName;
    document.getElementById('passwordModal').style.display = 'block';
    
    // Focus on password field
    setTimeout(() => {
        const passwordField = document.querySelector('input[name="new_password"]');
        if (passwordField) {
            passwordField.focus();
        }
    }, 100);
    
    // Add modal animation
    const modalContent = document.querySelector('.modal-content');
    modalContent.style.transform = 'translateY(-50px)';
    modalContent.style.opacity = '0';
    setTimeout(() => {
        modalContent.style.transition = 'all 0.3s ease';
        modalContent.style.transform = 'translateY(0)';
        modalContent.style.opacity = '1';
    }, 10);
    
    // Show toast notification
    showToast('💡 Test de wachtwoord suggesties hieronder!', 'info');
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    const modalContent = modal.querySelector('.modal-content');
    modalContent.style.transform = 'translateY(-50px)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function setPassword(password) {
    const passwordField = document.querySelector('input[name="new_password"]');
    if (passwordField) {
        passwordField.value = password;
        passwordField.focus();
        
        // Add highlight animation
        passwordField.style.background = 'rgba(16, 185, 129, 0.1)';
        passwordField.style.borderColor = '#10b981';
        passwordField.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
        
        setTimeout(() => {
            passwordField.style.background = '';
            passwordField.style.borderColor = '#e5e7eb';
            passwordField.style.boxShadow = '';
        }, 2000);
        
        // Show success toast
        showToast('✅ Wachtwoord ingevuld! Klik "Wachtwoord Wijzigen" om op te slaan.', 'success');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('passwordModal');
    if (event.target === modal) {
        closePasswordModal();
    }
}

// Enhanced toast for demo
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Enhanced toast styles for demo
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 350px;
        max-width: 500px;
        border-left: 5px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        animation: slideInRight 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Show welcome toast on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        showToast('🎉 Welkom bij de LIVE DEMO! Klik op 🔑 Wachtwoord knoppen om te testen.', 'info');
    }, 1000);
});
</script>