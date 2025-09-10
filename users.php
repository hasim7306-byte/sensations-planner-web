<?php if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") { 
    header("Location: ?page=login"); 
    exit; 
} 

$pdo = getDB();

// Handle user creation
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "create_user") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $function_role = $_POST["function_role"] ?? null;
    
    if (strlen($password) >= 6) {
        $user_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, function_role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
        
        try {
            $stmt->execute([$user_id, $name, $email, $hashed_password, $role, $function_role]);
            $success_message = "Gebruiker succesvol aangemaakt";
        } catch (PDOException $e) {
            $error_message = "Fout bij aanmaken gebruiker: E-mailadres bestaat mogelijk al";
        }
    } else {
        $error_message = "Wachtwoord moet minimaal 6 karakters zijn";
    }
}

// Handle user deletion
if (isset($_GET["delete_user"])) {
    $user_id = $_GET["delete_user"];
    if ($user_id !== $_SESSION["user_id"]) { // Can't delete yourself
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Gebruiker gedeactiveerd";
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users WHERE is_active = 1 ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <h1>👥 Gebruikersbeheer</h1>
        <p class="page-subtitle">Beheer alle medewerkers en hun wachtwoorden</p>
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

    <div class="management-grid">
        <!-- Create User Form -->
        <div class="management-card">
            <h3>➕ Nieuwe Gebruiker</h3>
            <form method="POST" class="user-form">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-group">
                    <label>Volledige Naam</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>E-mailadres</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Wachtwoord</label>
                    <input type="password" name="password" required minlength="6">
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
                
                <button type="submit" class="btn btn-primary">👤 Gebruiker Aanmaken</button>
            </form>
        </div>

        <!-- Users List -->
        <div class="management-card users-list">
            <h3>📋 Alle Gebruikers (<?php echo count($users); ?>)</h3>
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
                                    <button class="btn-small btn-warning" onclick="showPasswordModal('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['name']); ?>')">
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
        <h3>🆕 Enhanced Features v2.0</h3>
        <div class="features-grid">
            <div class="feature-item">
                <span class="feature-icon">🔑</span>
                <strong>Wachtwoord Beheer</strong>
                <p>Admins kunnen wachtwoorden van alle gebruikers wijzigen</p>
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
                <strong>Role Management</strong>
                <p>Admin, Manager, Employee hierarchie met permissions</p>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔑 Wachtwoord Wijzigen</h3>
            <span class="close" onclick="closePasswordModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="user_id" id="modal_user_id">
            
            <div class="modal-body">
                <p>Wachtwoord wijzigen voor: <strong id="modal_user_name"></strong></p>
                
                <div class="form-group">
                    <label>Nieuw Wachtwoord</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Minimaal 6 karakters">
                </div>
                
                <div class="password-suggestions">
                    <h4>💡 Wachtwoord Suggesties:</h4>
                    <button type="button" class="btn-suggestion" onclick="setPassword('Sensations2024!')">Sensations2024!</button>
                    <button type="button" class="btn-suggestion" onclick="setPassword('StG' + Math.floor(Math.random() * 1000))">StG + nummer</button>
                    <button type="button" class="btn-suggestion" onclick="setPassword('Werkrooster' + new Date().getFullYear())">Werkrooster + jaar</button>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Annuleren</button>
                <button type="submit" class="btn btn-primary">🔄 Wachtwoord Wijzigen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showPasswordModal(userId, userName) {
    document.getElementById('modal_user_id').value = userId;
    document.getElementById('modal_user_name').textContent = userName;
    document.getElementById('passwordModal').style.display = 'block';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

function setPassword(password) {
    document.querySelector('input[name="new_password"]').value = password;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('passwordModal');
    if (event.target === modal) {
        closePasswordModal();
    }
}
</script>